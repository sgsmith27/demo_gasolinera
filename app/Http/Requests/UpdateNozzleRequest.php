<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateNozzleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $nozzleId = $this->route('nozzle')->id;

        return [
            'fuel_id' => ['required', 'integer', 'exists:fuels,id'],
            'code' => ['required', 'string', 'max:30', Rule::unique('nozzles', 'code')->ignore($nozzleId)],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
