<?php

namespace App\Http\Controllers\Api;

use App\Support\Audit;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFuelDeliveryRequest;
use App\Http\Requests\VoidFuelDeliveryRequest;
use App\Models\FuelDelivery;
use App\Models\InventoryMovement;
use App\Models\Tank;
use App\Services\FuelDeliveryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FuelDeliveryController extends Controller
{
    public function store(StoreFuelDeliveryRequest $request, FuelDeliveryService $service): JsonResponse
    {
        $userId = (int) Auth::id();

        $delivery = $service->createDelivery($userId, $request->validated());

        return response()->json([
            'message' => 'Entrada registrada',
            'data' => $delivery,
        ], 201);
    }

    public function void(VoidFuelDeliveryRequest $request, FuelDelivery $fuelDelivery): JsonResponse
{
    $userId = (int) Auth::id();

    if ($fuelDelivery->status === 'voided') {
        throw ValidationException::withMessages([
            'fuel_delivery' => 'El abastecimiento ya fue anulado.',
        ]);
    }

    DB::transaction(function () use ($fuelDelivery, $request, $userId) {
        $tank = Tank::query()
            ->whereKey($fuelDelivery->tank_id)
            ->lockForUpdate()
            ->first();

        if (! $tank) {
            throw ValidationException::withMessages([
                'tank' => 'No existe tanque para revertir el abastecimiento.',
            ]);
        }

        $current = (float) $tank->current_gallons;
        $gallons = (float) $fuelDelivery->gallons;

        if ($current + 1e-9 < $gallons) {
            throw ValidationException::withMessages([
                'stock' => 'No se puede anular porque el tanque ya no tiene suficientes galones para revertir este abastecimiento.',
            ]);
        }

        $tank->current_gallons = round($current - $gallons, 3);
        $tank->save();

        InventoryMovement::create([
            'moved_at' => now(),
            'tank_id' => $tank->id,
            'fuel_id' => $fuelDelivery->fuel_id,
            'movement_type' => 'OUT',
            'gallons_delta' => -1 * $gallons,
            'reference_type' => 'PIPA_VOID',
            'reference_id' => $fuelDelivery->id,
            'created_by' => $userId,
            'notes' => 'Anulación de abastecimiento: '.$request->validated('reason'),
        ]);

        $fuelDelivery->update([
            'status' => 'voided',
            'voided_at' => now(),
            'voided_by' => $userId,
            'void_reason' => $request->validated('reason'),
        ]);

        Audit::log(
            module: 'fuel_deliveries',
            action: 'void',
            entityType: 'FuelDelivery',
            entityId: $fuelDelivery->id,
            description: 'Abastecimiento anulado',
            meta: [
                'reason' => $request->validated('reason'),
                'tank_id' => $fuelDelivery->tank_id,
                'fuel_id' => $fuelDelivery->fuel_id,
                'gallons' => $fuelDelivery->gallons,
            ]
        );
    });

    return response()->json([
        'message' => 'Abastecimiento anulado correctamente.',
    ]);
}

public function list(\Illuminate\Http\Request $request): JsonResponse
{
    $from = $request->query('from');
    $to = $request->query('to');

    $deliveries = DB::table('fuel_deliveries as d')
        ->join('fuels as f', 'f.id', '=', 'd.fuel_id')
        ->join('tanks as t', 't.id', '=', 'd.tank_id')
        ->leftJoin('users as u', 'u.id', '=', 'd.created_by')
        ->when($from, fn ($q) => $q->whereDate('d.delivered_at', '>=', $from))
        ->when($to, fn ($q) => $q->whereDate('d.delivered_at', '<=', $to))
        ->orderByDesc('d.delivered_at')
        ->selectRaw('d.id, d.delivered_at, d.gallons, d.total_cost_q, d.status, f.name as fuel_name, t.id as tank_id, COALESCE(u.name, \'-\') as user_name')
        ->get();

    return response()->json([
        'deliveries' => $deliveries,
    ]);
}
}