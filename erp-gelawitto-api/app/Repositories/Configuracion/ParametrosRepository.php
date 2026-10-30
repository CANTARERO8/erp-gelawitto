<?php

namespace App\Repositories\Configuracion;

use App\Models\Configuracion\Parametro;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ParametrosRepository
{
    public function obtenerTodos(): array
    {
        if (! Schema::hasTable('parametros')) {
            return [];
        }

        return Parametro::query()->pluck('valor', 'clave')->toArray();
    }

    public function valor(string $clave, string|float|int $porDefecto = ''): string
    {
        if (! Schema::hasTable('parametros')) {
            return (string) $porDefecto;
        }

        $registro = Parametro::find($clave);

        return $registro ? (string) $registro->valor : (string) $porDefecto;
    }

    public function valorDecimal(string $clave, float $porDefecto = 0.0): float
    {
        return (float) $this->valor($clave, $porDefecto);
    }

    public function tasaIva(): float
    {
        $porcentaje = $this->valorDecimal('iva_porcentaje', 15.0);

        return round($porcentaje / 100, 4);
    }

    public static function siguienteFolio(
        string $secuencia,
        string $formato,
        string $tabla,
        string $columna
    ): string {
        do {
            $fila = DB::selectOne("SELECT nextval('{$secuencia}') AS n");
            $consecutivo = (int) ($fila->n ?? 1);
            $folio = sprintf($formato, $consecutivo);
        } while (DB::table($tabla)->where($columna, $folio)->exists());

        return $folio;
    }
}
