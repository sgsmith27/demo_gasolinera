<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFelConfigRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'environment' => ['required', 'in:test,production'],
            'taxid' => ['required', 'string', 'max:20'],
            'username' => ['required', 'string', 'max:100'],
            'password' => ['required', 'string', 'max:255'],
            'seller_name' => ['required', 'string', 'max:255'],
            'seller_address' => ['required', 'string', 'max:255'],
            'afiliacion_iva' => ['required', 'string', 'max:20'],
            'tipo_personeria' => ['required', 'string', 'max:20'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);
    }
}
