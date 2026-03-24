<?php

namespace App\Http\Controllers\Api;

use App\Support\Audit;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInventoryAdjustmentRequest;
use App\Models\InventoryAdjustment;
use App\Models\InventoryMovement;
use App\Models\Tank;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryAdjustmentController extends Controller
{
    public function store(StoreInventoryAdjustmentRequest $request): JsonResponse
    {
        $userId = (int) Auth::id();
        $adjustedAt = $request->validated('adjusted_at') ? now()->parse($request->validated('adjusted_at')) : now();
        $gallons = round((float) $request->validated('gallons'), 3);
        $type = $request->validated('adjustment_type');
        $reason = $request->validated('reason');

        $adjustment = DB::transaction(function () use ($request, $userId, $adjustedAt, $gallons, $type, $reason) {
            $tank = Tank::query()
                ->whereKey($request->validated('tank_id'))
                ->lockForUpdate()
                ->firstOrFail();

            $current = (float) $tank->current_gallons;

            if ($type === 'OUT' && $current + 1e-9 < $gallons) {
                throw ValidationException::withMessages([
                    'gallons' => 'No hay suficientes galones en el tanque para aplicar este ajuste.',
                ]);
            }

            $delta = $type === 'IN' ? $gallons : -1 * $gallons;

            $inventoryAdjustment = InventoryAdjustment::create([
                'adjusted_at' => $adjustedAt,
                'tank_id' => $tank->id,
                'fuel_id' => $tank->fuel_id,
                'adjustment_type' => $type,
                'gallons' => $gallons,
                'reason' => $reason,
                'created_by' => $userId,
            ]);

            InventoryMovement::create([
                'moved_at' => $adjustedAt,
                'tank_id' => $tank->id,
                'fuel_id' => $tank->fuel_id,
                'movement_type' => 'ADJUST',
                'gallons_delta' => $delta,
                'reference_type' => 'ADJUST',
                'reference_id' => $inventoryAdjustment->id,
                'created_by' => $userId,
                'notes' => $reason,
            ]);

            $tank->current_gallons = round($current + $delta, 3);
            $tank->save();

            return $inventoryAdjustment->fresh();
        });
        
        Audit::log(
            module: 'inventory',
            action: 'adjust',
            entityType: 'InventoryAdjustment',
            entityId: $adjustment->id,
            description: 'Ajuste manual de inventario',
            meta: [
                'tank_id' => $adjustment->tank_id,
                'fuel_id' => $adjustment->fuel_id,
                'adjustment_type' => $adjustment->adjustment_type,
                'gallons' => $adjustment->gallons,
                'reason' => $adjustment->reason,
            ]
        );

        return response()->json([
            'message' => 'Ajuste de inventario registrado correctamente.',
            'data' => $adjustment,
        ], 201);
    }
}