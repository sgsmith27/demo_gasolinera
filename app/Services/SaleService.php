<?php

namespace App\Services;

use App\Models\FuelPrice;
use App\Models\InventoryMovement;
use App\Models\Nozzle;
use App\Models\Sale;
use App\Models\Tank;
use App\Models\WorkShift;
use App\Models\AccountReceivable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleService
{
    public function createSale(int $userId, array $data): Sale
    {
        $soldAt = isset($data['sold_at']) ? now()->parse($data['sold_at']) : now();
        $saleMode = $data['sale_mode'];
        $paymentMethod = $data['payment_method'] ?? 'cash';
        $notes = $data['notes'] ?? null;

        return DB::transaction(function () use ($userId, $data, $soldAt, $saleMode, $paymentMethod, $notes) {
            /** @var Nozzle $nozzle */
            $nozzle = Nozzle::query()->whereKey($data['nozzle_id'])->firstOrFail();

            /** @var Tank $tank */
            $tank = Tank::query()
                ->where('fuel_id', $nozzle->fuel_id)
                ->where('is_active', true)
                ->lockForUpdate()
                ->first();

            if (!$tank) {
                throw ValidationException::withMessages([
                    'tank' => 'No existe tanque activo para el combustible seleccionado.',
                ]);
            }

            $price = FuelPrice::query()
                ->where('fuel_id', $nozzle->fuel_id)
                ->where('valid_from', '<=', $soldAt)
                ->orderByDesc('valid_from')
                ->value('price_per_gallon');

            if ($price === null) {
                throw ValidationException::withMessages([
                    'price' => 'No hay precio vigente configurado para este combustible.',
                ]);
            }

            if ($saleMode === 'amount') {
                $amountQ = (float) $data['amount_q'];
                $gallons = $amountQ / (float) $price;
                $totalQ = $amountQ;
            } else {
                $gallons = (float) $data['gallons'];
                $totalQ = $gallons * (float) $price;
            }

            $gallons = round($gallons, 3);
            $totalQ = round($totalQ, 2);

            /** =========================
             * CÁLCULO FISCAL (IDP + IVA)
             * ========================= */
            $fuel = $nozzle->fuel; // ya viene relacionado por fuel_id

            $idpPerGallon = (float) ($fuel->idp_amount_per_gallon ?? 0);

            // 1. IDP total
            $idpTotal = $gallons * $idpPerGallon;

            // 2. Subtotal sin IDP
            $subtotalSinIdp = $totalQ - $idpTotal;

            // 3. Base imponible (sin IVA)
            $taxableBase = $subtotalSinIdp / 1.12;

            // 4. IVA
            $vatAmount = $subtotalSinIdp - $taxableBase;

            // Redondeos finales
            $idpTotal = round($idpTotal, 2);
            $taxableBase = round($taxableBase, 2);
            $vatAmount = round($vatAmount, 2);

            if ($gallons <= 0) {
                throw ValidationException::withMessages([
                    'gallons' => 'Los galones calculados deben ser mayores a 0.',
                ]);
            }

            $current = (float) $tank->current_gallons;
            if ($current + 1e-9 < $gallons) {
                throw ValidationException::withMessages([
                    'stock' => "Stock insuficiente. Disponible: {$tank->current_gallons} gal",
                ]);
            }

            $shift = WorkShift::query()
                ->where('user_id', $userId)
                ->where('status', 'open')
                ->first();

            if (! $shift) {
                throw ValidationException::withMessages([
                    'shift' => 'No tienes un turno abierto. Debes abrir un turno antes de registrar ventas.',
                ]);
            }
            if ($shift->assignment_mode === 'fixed' && (int) $shift->pump_id !== (int) $nozzle->pump_id) {
                throw ValidationException::withMessages([
                    'nozzle_id' => 'Este turno está asignado a una bomba fija. No puedes vender desde otra bomba.',
                ]);
            }

            $sale = Sale::create([
                'sold_at' => $soldAt,
                'user_id' => $userId,
                'nozzle_id' => $nozzle->id,
                'fuel_id' => $nozzle->fuel_id,
                'price_per_gallon' => $price,
                'gallons' => $gallons,
                'total_amount_q' => $totalQ,
                'sale_mode' => $saleMode,
                'payment_method' => $paymentMethod,
                'notes' => $notes,
                'shift_id' => $shift->id,
                'customer_id' => $data['customer_id'] ?? null,
                'idp_amount_q' => $idpTotal,
                'vat_amount_q' => $vatAmount,
                'taxable_base_q' => $taxableBase,
            ]);

            if ($paymentMethod === 'credit') {
                AccountReceivable::create([
                    'customer_id' => $sale->customer_id,
                    'sale_id' => $sale->id,
                    'document_date' => $soldAt->toDateString(),
                    'original_amount_q' => $totalQ,
                    'paid_amount_q' => 0,
                    'balance_q' => $totalQ,
                    'status' => 'pending',
                    'notes' => 'Generada desde venta al crédito',
                ]);
            }

            InventoryMovement::create([
                'moved_at' => $soldAt,
                'tank_id' => $tank->id,
                'fuel_id' => $nozzle->fuel_id,
                'movement_type' => 'OUT',
                'gallons_delta' => -1 * $gallons,
                'reference_type' => 'SALE',
                'reference_id' => $sale->id,
                'created_by' => $userId,
            ]);

            $tank->current_gallons = round($current - $gallons, 3);
            $tank->save();

            return $sale->fresh();
        });
    }
}