<?php

namespace App\Http\Controllers\Api;

use App\Support\Audit;
use App\Http\Controllers\Controller;
use App\Services\SaleService;
use App\Http\Requests\VoidSaleRequest;
use App\Http\Requests\StoreSaleRequest;
use App\Models\InventoryMovement;
use App\Models\Sale;
use App\Models\Tank;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;


class SaleController extends Controller
{
    public function store(StoreSaleRequest $request, SaleService $service): JsonResponse
    {
        // Amarrado a login real.
        $userId = (int) Auth::id();

        $sale = $service->createSale($userId, $request->validated());

        return response()->json([
            'message' => 'Venta registrada',
            'data' => $sale,
        ], 201);
    }

 public function void(VoidSaleRequest $request, Sale $sale): JsonResponse
{
    $userId = (int) Auth::id();

    if ($sale->status === 'voided') {
        throw ValidationException::withMessages([
            'sale' => 'La venta ya fue anulada.',
        ]);
    }

    DB::transaction(function () use ($sale, $request, $userId) {
        $tank = Tank::query()
            ->where('fuel_id', $sale->fuel_id)
            ->where('is_active', true)
            ->lockForUpdate()
            ->first();

        if (! $tank) {
            throw ValidationException::withMessages([
                'tank' => 'No existe tanque activo para revertir la venta.',
            ]);
        }

        $tank->current_gallons = round((float) $tank->current_gallons + (float) $sale->gallons, 3);
        $tank->save();

        InventoryMovement::create([
            'moved_at' => now(),
            'tank_id' => $tank->id,
            'fuel_id' => $sale->fuel_id,
            'movement_type' => 'IN',
            'gallons_delta' => (float) $sale->gallons,
            'reference_type' => 'SALE_VOID',
            'reference_id' => $sale->id,
            'created_by' => $userId,
            'notes' => 'Anulación de venta: '.$request->validated('reason'),
        ]);

        $sale->update([
            'status' => 'voided',
            'voided_at' => now(),
            'voided_by' => $userId,
            'void_reason' => $request->validated('reason'),
        ]);

        Audit::log(
        module: 'sales',
        action: 'void',
        entityType: 'Sale',
        entityId: $sale->id,
        description: 'Venta anulada',
        meta: [
            'reason' => $request->validated('reason'),
            'fuel_id' => $sale->fuel_id,
            'gallons' => $sale->gallons,
            'total_amount_q' => $sale->total_amount_q,
        ]
    );
    });

    return response()->json([
        'message' => 'Venta anulada correctamente.',
    ]);
}

public function list(\Illuminate\Http\Request $request): JsonResponse
{
    $from = $request->query('from');
    $to = $request->query('to');

    $latestFelStatusSub = DB::table('fel_documents as fd')
        ->select('fd.fel_status')
        ->whereColumn('fd.sale_id', 's.id')
        ->orderByDesc('fd.id')
        ->limit(1);

    $latestFelIdSub = DB::table('fel_documents as fd')
        ->select('fd.id')
        ->whereColumn('fd.sale_id', 's.id')
        ->orderByDesc('fd.id')
        ->limit(1);

    $latestFelUuidSub = DB::table('fel_documents as fd')
        ->select('fd.uuid')
        ->whereColumn('fd.sale_id', 's.id')
        ->orderByDesc('fd.id')
        ->limit(1);

    $sales = DB::table('sales as s')
        ->join('users as u', 'u.id', '=', 's.user_id')
        ->join('fuels as f', 'f.id', '=', 's.fuel_id')
        ->when($from, fn ($q) => $q->whereDate('s.sold_at', '>=', $from))
        ->when($to, fn ($q) => $q->whereDate('s.sold_at', '<=', $to))
        ->orderByDesc('s.sold_at')
        ->select([
            's.id',
            's.sold_at',
            's.gallons',
            's.total_amount_q',
            's.status',
            'u.name as user_name',
            'f.name as fuel_name',
            DB::raw('(' . $latestFelStatusSub->toSql() . ') as fel_status'),
            DB::raw('(' . $latestFelIdSub->toSql() . ') as fel_id'),
            DB::raw('(' . $latestFelUuidSub->toSql() . ') as fel_uuid'),
        ])
        ->mergeBindings($latestFelStatusSub)
        ->mergeBindings($latestFelIdSub)
        ->mergeBindings($latestFelUuidSub)
        ->get();

    return response()->json([
        'sales' => $sales,
    ]);
}

public function ticket(Sale $sale)
{
    $sale->load([
        'fuel',
        'customer',
        'shift',
        'nozzle',
        'user',
        'latestFelDocument',
    ]);

    $pdf = Pdf::loadView('sales.ticket', [
        'sale' => $sale,
    ])->setPaper([0, 0, 136, 600], 'portrait');

    return $pdf->stream('tck' . $sale->id . '.pdf');
}


}