<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FuelPrice;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FuelController extends Controller
{
    public function currentPrice(Request $request, int $fuelId): JsonResponse
    {
        $at = $request->query('at')
            ? Carbon::parse($request->query('at'))
            : now();

        $pricePerGallon = FuelPrice::query()
            ->where('fuel_id', $fuelId)
            ->where('valid_from', '<=', $at)
            ->orderByDesc('valid_from')
            ->value('price_per_gallon');

        if ($pricePerGallon === null) {
            return response()->json([
                'message' => 'No hay precio vigente para este combustible.',
            ], 404);
        }

        return response()->json([
            'fuel_id' => $fuelId,
            'at' => $at->toIso8601String(),
            'price_per_gallon' => round((float) $pricePerGallon, 2),
        ]);
    }
    public function fuelsWithPrices(): JsonResponse
{
    $startOfMonth = now()->startOfMonth();

    $fuels = \App\Models\Fuel::query()
        ->where('is_active', true)
        ->orderBy('name')
        ->get()
        ->map(function ($fuel) use ($startOfMonth) {
            $current = \App\Models\FuelPrice::query()
                ->where('fuel_id', $fuel->id)
                ->orderByDesc('valid_from')
                ->first(['id', 'price_per_gallon', 'valid_from']);

            $history = \App\Models\FuelPrice::query()
                ->where('fuel_id', $fuel->id)
                ->where('valid_from', '>=', $startOfMonth)
                ->orderByDesc('valid_from')
                ->get(['id', 'price_per_gallon', 'valid_from']);

            return [
                'id' => $fuel->id,
                'name' => $fuel->name,
                'current_price_per_gallon' => $current?->price_per_gallon,
                'current_valid_from' => $current?->valid_from?->format('Y-m-d H:i:s'),
                'price_history' => $history->map(fn ($row) => [
                    'id' => $row->id,
                    'price_per_gallon' => $row->price_per_gallon,
                    'valid_from' => optional($row->valid_from)->format('Y-m-d H:i:s'),
                ])->values(),
            ];
        });

    return response()->json([
        'month' => now()->format('Y-m'),
        'fuels' => $fuels,
    ]);
}
}