<?php

namespace App\Http\Requests\Finanzas;

use Illuminate\Foundation\Http\FormRequest;

class StoreGastoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'categoria' => ['required', 'string', 'max:60'],
            'concepto' => ['required', 'string', 'max:180'],
            'monto' => ['required', 'numeric', 'min:0.01'],
            'estado' => ['required', 'in:Pagado,Pendiente'],
        ];
    }
}
