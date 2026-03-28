<?php

namespace App\Services\Fel;

use App\Models\FelConfig;
use App\Models\Sale;
use Illuminate\Validation\ValidationException;

class FelPayloadValidator
{
    public function validateSaleInvoice(Sale $sale, FelConfig $config): void
    {
        $errors = [];

        if ($sale->status !== 'active') {
            $errors['sale'][] = 'Solo se pueden certificar ventas activas.';
        }

        if (! $sale->fuel) {
            $errors['fuel'][] = 'La venta no tiene combustible asociado.';
        }

        if ((float) $sale->gallons <= 0) {
            $errors['gallons'][] = 'Los galones deben ser mayores a 0.';
        }

        if ((float) $sale->price_per_gallon <= 0) {
            $errors['price_per_gallon'][] = 'El precio por galón debe ser mayor a 0.';
        }

        if ((float) $sale->total_amount_q <= 0) {
            $errors['total_amount_q'][] = 'El total de la venta debe ser mayor a 0.';
        }

        if (! in_array($config->environment, ['test', 'production'], true)) {
            $errors['fel_config.environment'][] = 'El ambiente FEL debe ser test o production.';
        }

        foreach ([
            'taxid',
            'username',
            'password',
            'seller_name',
            'seller_address',
            'afiliacion_iva',
            'tipo_personeria',
        ] as $field) {
            if (blank($config->{$field})) {
                $errors["fel_config.{$field}"][] = "El campo {$field} de la configuración FEL es obligatorio.";
            }
        }

        if ($sale->customer && method_exists($sale->customer, 'getAttribute')) {
            if (isset($sale->customer->is_active) && ! $sale->customer->is_active) {
                $errors['customer'][] = 'No se puede emitir FEL para un cliente inactivo.';
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }
}