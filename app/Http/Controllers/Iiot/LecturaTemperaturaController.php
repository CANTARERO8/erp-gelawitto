<?php

namespace App\Http\Controllers\Iiot;

use App\Http\Controllers\Controller;
use App\Http\Requests\Iiot\RegistrarLecturaRequest;
use App\Services\Iiot\IiotService;
use Illuminate\Http\JsonResponse;

class LecturaTemperaturaController extends Controller
{
    public function __construct(
        protected IiotService $iiotService
    ) {}

    /**
     * Recibe una lectura enviada por el sensor (ESP32 o simulador) con el token X-IIoT-Token.
     */
    public function store(RegistrarLecturaRequest $request): JsonResponse
    {
        $resultado = $this->iiotService->registrarLectura(
            $request->validated(),
            'Sensor',
            $request->ip()
        );

        return response()->json([
            'id' => $resultado['lectura']->id,
            'temperatura' => (string) $resultado['lectura']->temperatura,
            'fuera_de_rango' => $resultado['lectura']->fuera_de_rango,
            'lotes_retenidos' => $resultado['lotes_retenidos'],
        ], 201);
    }
}
