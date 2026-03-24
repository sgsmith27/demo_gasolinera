<?php

namespace App\Http\Controllers\Api;

use App\Support\Audit;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFuelPriceRequest;
use App\Models\FuelPrice;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class FuelPriceController extends Controller
{
    public function store(StoreFuelPriceRequest $request): JsonResponse
    {
        $data = $request->validated();
        $userId = (int) Auth::id();

        $validFrom = isset($data['valid_from']) ? now()->parse($data['valid_from']) : now();

        $fuelPrice = FuelPrice::create([
            'fuel_id' => $data['fuel_id'],
            'price_per_gallon' => round((float) $data['price'], 4),
            'valid_from' => $validFrom,
            'created_by' => $userId,
        ]);

        Audit::log(
            module: 'fuel_prices',
            action: 'create',
            entityType: 'FuelPrice',
            entityId: $fuelPrice->id,
            description: 'Nuevo precio de combustible registrado',
            meta: [
                'fuel_id' => $fuelPrice->fuel_id,
                'price_per_gallon' => $fuelPrice->price_per_gallon,
                'valid_from' => $fuelPrice->valid_from,
            ]
        );

        return response()->json([
            'message' => 'Precio registrado',
            'data' => $fuelPrice->fresh(),
        ], 201);
    }
}