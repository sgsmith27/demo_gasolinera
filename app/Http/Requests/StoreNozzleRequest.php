<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNozzleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fuel_id' => ['required', 'integer', 'exists:fuels,id'],
            'code' => ['required', 'string', 'max:30', 'unique:nozzles,code'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}