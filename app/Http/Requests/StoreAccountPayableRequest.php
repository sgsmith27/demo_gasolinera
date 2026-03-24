<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAccountPayableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'supplier_id' => ['required', 'integer', 'exists:suppliers,id'],
            'document_date' => ['required', 'date'],
            'document_no' => ['nullable', 'string', 'max:60'],
            'category' => ['required', 'in:fuel,services,maintenance,general'],
            'description' => ['required', 'string', 'max:255'],
            'original_amount_q' => ['required', 'numeric', 'min:0.01'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
