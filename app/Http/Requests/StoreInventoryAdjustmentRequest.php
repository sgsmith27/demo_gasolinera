<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInventoryAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'adjusted_at' => ['nullable', 'date'],
            'tank_id' => ['required', 'integer', 'exists:tanks,id'],
            'adjustment_type' => ['required', 'in:IN,OUT'],
            'gallons' => ['required', 'numeric', 'min:0.001'],
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
        ];
    }
}
