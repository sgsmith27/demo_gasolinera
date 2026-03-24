<?php

namespace App\Services;

use App\Models\FuelDelivery;
use App\Models\InventoryMovement;
use App\Models\Tank;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FuelDeliveryService
{
    public function createDelivery(int $userId, array $data): FuelDelivery
    {
        $deliveredAt = isset($data['delivered_at']) ? now()->parse($data['delivered_at']) : now();
        $gallons = round((float) $data['gallons'], 3);
        $totalCostQ = array_key_exists('total_cost_q', $data) ? round((float) $data['total_cost_q'], 2) : null;
        $notes = $data['notes'] ?? null;

        if ($gallons <= 0) {
            throw ValidationException::withMessages(['gallons' => 'Los galones deben ser mayores a 0.']);
        }

        return DB::transaction(function () use ($userId, $data, $deliveredAt, $gallons, $totalCostQ, $notes) {
            /** @var Tank $tank */
            $tank = Tank::query()->whereKey($data['tank_id'])->lockForUpdate()->firstOrFail();

            if (!$tank->is_active) {
                throw ValidationException::withMessages(['tank' => 'El tanque no está activo.']);
            }

            if ($tank->capacity_gallons !== null) {
                $newValue = (float) $tank->current_gallons + $gallons;
                if ($newValue - (float) $tank->capacity_gallons > 1e-9) {
                    throw ValidationException::withMessages([
                        'capacity' => "Supera la capacidad del tanque. Capacidad: {$tank->capacity_gallons} gal",
                    ]);
                }
            }

            $delivery = FuelDelivery::create([
                'delivered_at' => $deliveredAt,
                'tank_id' => $tank->id,
                'fuel_id' => $tank->fuel_id,
                'gallons' => $gallons,
                'total_cost_q' => $totalCostQ,
                'created_by' => $userId,
                'notes' => $notes,
            ]);

            InventoryMovement::create([
                'moved_at' => $deliveredAt,
                'tank_id' => $tank->id,
                'fuel_id' => $tank->fuel_id,
                'movement_type' => 'IN',
                'gallons_delta' => $gallons,
                'reference_type' => 'PIPA',
                'reference_id' => $delivery->id,
                'created_by' => $userId,
                'notes' => $notes,
            ]);

            $tank->current_gallons = round((float) $tank->current_gallons + $gallons, 3);
            $tank->save();

            return $delivery->fresh();
        });
    }
}