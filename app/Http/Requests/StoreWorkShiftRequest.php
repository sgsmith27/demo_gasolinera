<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWorkShiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'started_at' => ['nullable', 'date'],
            'opening_cash_q' => ['required', 'numeric', 'min:0'],
            'opening_notes' => ['nullable', 'string', 'max:1000'],
            'assignment_mode' => ['required', 'in:fixed,free'],
            'pump_id' => ['nullable', 'integer', 'exists:pumps,id'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $mode = $this->input('assignment_mode');
            $pumpId = $this->input('pump_id');

            if ($mode === 'fixed' && empty($pumpId)) {
                $validator->errors()->add('pump_id', 'Debes seleccionar una bomba para un turno con asignación fija.');
            }

            if ($mode === 'free' && !empty($pumpId)) {
                $validator->errors()->add('pump_id', 'No debes seleccionar bomba cuando el turno es libre.');
            }
        });
    }
}
