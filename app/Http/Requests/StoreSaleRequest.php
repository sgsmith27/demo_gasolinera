<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sold_at' => ['nullable', 'date'],
            'nozzle_id' => ['required', 'integer', 'exists:nozzles,id'],
            'sale_mode' => ['required', 'in:amount,volume'],
            'amount_q' => ['required_if:sale_mode,amount', 'numeric', 'min:0.01'],
            'gallons' => ['required_if:sale_mode,volume', 'numeric', 'min:0.001'],
            'notes' => ['nullable', 'string', 'max:255'],
            'payment_method' => ['required', 'in:cash,card,transfer,credit'],
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->input('payment_method') === 'credit' && empty($this->input('customer_id'))) {
                $validator->errors()->add('customer_id', 'Debes seleccionar un cliente para una venta al crédito.');
            }
        });
    }
}