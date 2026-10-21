<?php

namespace App\Http\Requests\Iiot;

use Illuminate\Foundation\Http\FormRequest;

class RegistrarLecturaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'temperatura' => ['required', 'numeric', 'between:-40,60'],
            'dispositivo' => ['nullable', 'string', 'max:40'],
            'fecha_lectura' => ['nullable', 'date'],
        ];
    }
}
