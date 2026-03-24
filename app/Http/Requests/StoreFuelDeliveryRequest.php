<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFuelDeliveryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'delivered_at' => ['nullable', 'date'],
            'tank_id' => ['required', 'integer', 'exists:tanks,id'],
            'gallons' => ['required', 'numeric', 'min:0.001'],
            'total_cost_q' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:255'],
        ];
    }
}