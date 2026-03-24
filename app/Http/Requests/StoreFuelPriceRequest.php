<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFuelPriceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fuel_id' => ['required', 'integer', 'exists:fuels,id'],
            'price' => ['required', 'numeric', 'min:0.0001'],
            'valid_from' => ['nullable', 'date'],
        ];
    }
}