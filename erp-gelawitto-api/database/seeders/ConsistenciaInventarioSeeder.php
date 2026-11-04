<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Deja coherentes, a partir de los lotes ya sembrados, los consumos por lote,
 * los saldos por lote, el Kardex y el stock de insumos y productos.
 *
 * Reglas (las mismas que aplica ProduccionService al ejecutar un lote):
 *  - Cada lote finalizado consume su BOM completa: cantidad_requerida x programada / rendimiento.
 *  - Los insumos se toman de los lotes de proveedor en orden de vencimiento (FEFO).
 *  - El subensamble se toma del lote finalizado de ese subensamble.
 *  - El Kardex se reconstruye en orden cronológico con saldo corrido y no admite saldos negativos.
 */
class ConsistenciaInventarioSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $this->regenerarConsumos();
            $this->recalcularDisponibles();
            $this->reconstruirKardex();
        });
    }

    private function regenerarConsumos(): void
    {
        DB::table('consumos_lote')->delete();

        $disponible = DB::table('lotes_insumo')->pluck('cantidad_inicial', 'id')->map(fn ($v) => (float) $v)->all();
        $lotes = DB::table('lotes_produccion')->where('estado', 'Finalizado')->orderBy('id')->get();
        $filas = [];

        foreach ($lotes as $lote) {
            $receta = DB::table('recetas')->where('id', $lote->receta_id)->first();
            $factor = (float) $lote->cantidad_programada / max(0.001, (float) $receta->rendimiento_unidades);
            $momento = Carbon::parse($lote->inicio_real);

            foreach (DB::table('receta_insumos')->where('receta_id', $receta->id)->orderBy('id')->get() as $linea) {
                $cantidad = round((float) $linea->cantidad_requerida * $factor, 3);

                if ($linea->subproducto_id) {
                    $base = DB::table('lotes_produccion')
                        ->where('producto_id', $linea->subproducto_id)->where('estado', 'Finalizado')->orderBy('id')->first();
                    $filas[] = ['lote_produccion_id' => $lote->id, 'lote_insumo_id' => null, 'subproducto_lote_id' => $base->id,
                        'cantidad' => $cantidad, 'created_at' => $momento, 'updated_at' => $momento];

                    continue;
                }

                $pendiente = $cantidad;
                $proveedores = DB::table('lotes_insumo')->where('insumo_id', $linea->insumo_id)
                    ->orderByRaw('fecha_vencimiento asc nulls last')->orderBy('id')->get();
                foreach ($proveedores as $li) {
                    if ($pendiente <= 0.0005) {
                        break;
                    }
                    $toma = round(min($pendiente, $disponible[$li->id]), 3);
                    if ($toma <= 0) {
                        continue;
                    }
                    $disponible[$li->id] = round($disponible[$li->id] - $toma, 3);
                    $pendiente = round($pendiente - $toma, 3);
                    $filas[] = ['lote_produccion_id' => $lote->id, 'lote_insumo_id' => $li->id, 'subproducto_lote_id' => null,
                        'cantidad' => $toma, 'created_at' => $momento, 'updated_at' => $momento];
                }
                if ($pendiente > 0.0005) {
                    throw new RuntimeException("Lote de insumo insuficiente para {$lote->codigo_lote} (insumo {$linea->insumo_id}).");
                }
            }
        }

        DB::table('consumos_lote')->insert($filas);
    }

    private function recalcularDisponibles(): void
    {
        foreach (DB::table('lotes_insumo')->get() as $li) {
            $usado = (float) DB::table('consumos_lote')->where('lote_insumo_id', $li->id)->sum('cantidad');
            DB::table('lotes_insumo')->where('id', $li->id)->update(['cantidad_disponible' => round((float) $li->cantidad_inicial - $usado, 3)]);
        }

        foreach (DB::table('lotes_produccion')->where('estado', 'Finalizado')->get() as $lote) {
            $vendido = (float) DB::table('venta_lineas')->where('lote_produccion_id', $lote->id)->sum('cantidad');
            $scrap = (float) DB::table('controles_calidad')->where('lote_id', $lote->id)->where('disposicion', 'Scrap')->sum('unidades_no_conformes');
            $consumidoComoBase = (float) DB::table('consumos_lote')->where('subproducto_lote_id', $lote->id)->sum('cantidad');
            $saldo = round((float) $lote->cantidad_obtenida - $vendido - $scrap - $consumidoComoBase, 3);
            if ($saldo < 0) {
                throw new RuntimeException("Saldo negativo en el lote {$lote->codigo_lote}.");
            }
            DB::table('lotes_produccion')->where('id', $lote->id)->update(['cantidad_disponible' => $saldo]);
        }
    }

    private function reconstruirKardex(): void
    {
        $eventos = [];
        $insumos = DB::table('insumos')->pluck('nombre', 'id');
        $productos = DB::table('productos')->pluck('nombre', 'id');

        $compras = DB::table('orden_compra_lineas as l')
            ->join('ordenes_compra as o', 'o.id', '=', 'l.orden_compra_id')
            ->where('o.estado', 'Recibida')
            ->select('l.insumo_id', 'l.cantidad', 'l.costo_unitario', 'o.folio', 'o.fecha_recepcion', 'o.user_id')
            ->get();
        foreach ($compras as $c) {
            $eventos[] = ['ts' => Carbon::parse($c->fecha_recepcion)->startOfDay()->addHours(6), 'tipo_item' => 'insumo', 'item_id' => $c->insumo_id,
                'nombre' => $insumos[$c->insumo_id], 'mov' => 'Entrada por Compra', 'ref' => $c->folio, 'cant' => (float) $c->cantidad,
                'costo' => (float) $c->costo_unitario, 'user' => $c->user_id];
        }

        foreach (DB::table('lotes_produccion')->where('estado', 'Finalizado')->orderBy('id')->get() as $lote) {
            $inicio = Carbon::parse($lote->inicio_real);
            $fin = Carbon::parse($lote->fin_real);

            foreach (DB::table('consumos_lote')->where('lote_produccion_id', $lote->id)->orderBy('id')->get() as $c) {
                if ($c->lote_insumo_id) {
                    $li = DB::table('lotes_insumo')->where('id', $c->lote_insumo_id)->first();
                    $costo = (float) DB::table('orden_compra_lineas')->where('id', $li->orden_compra_linea_id)->value('costo_unitario');
                    $eventos[] = ['ts' => $inicio, 'tipo_item' => 'insumo', 'item_id' => $li->insumo_id, 'nombre' => $insumos[$li->insumo_id],
                        'mov' => 'Consumo de Producción', 'ref' => $lote->codigo_lote, 'cant' => -(float) $c->cantidad, 'costo' => $costo, 'user' => $lote->user_id];
                } else {
                    $base = DB::table('lotes_produccion')->where('id', $c->subproducto_lote_id)->first();
                    $eventos[] = ['ts' => $inicio, 'tipo_item' => 'producto', 'item_id' => $base->producto_id, 'nombre' => $productos[$base->producto_id],
                        'mov' => 'Consumo de Producción', 'ref' => $lote->codigo_lote, 'cant' => -(float) $c->cantidad,
                        'costo' => (float) $base->costo_unitario_resultante, 'user' => $lote->user_id];
                }
            }

            $eventos[] = ['ts' => $fin, 'tipo_item' => 'producto', 'item_id' => $lote->producto_id, 'nombre' => $productos[$lote->producto_id],
                'mov' => 'Ingreso de Producción', 'ref' => $lote->codigo_lote, 'cant' => (float) $lote->cantidad_obtenida,
                'costo' => (float) $lote->costo_unitario_resultante, 'user' => $lote->user_id];
        }

        foreach (DB::table('controles_calidad as c')->join('lotes_produccion as l', 'l.id', '=', 'c.lote_id')
            ->where('c.disposicion', 'Scrap')->select('c.*', 'l.codigo_lote', 'l.producto_id', 'l.costo_unitario_resultante')->get() as $c) {
            $eventos[] = ['ts' => Carbon::parse($c->fecha_inspeccion), 'tipo_item' => 'producto', 'item_id' => $c->producto_id, 'nombre' => $productos[$c->producto_id],
                'mov' => 'Scrap por Calidad', 'ref' => $c->codigo_lote, 'cant' => -(float) $c->unidades_no_conformes,
                'costo' => (float) $c->costo_unitario_resultante, 'user' => $c->user_id];
        }

        foreach (DB::table('venta_lineas as vl')->join('ventas as v', 'v.id', '=', 'vl.venta_id')
            ->select('vl.producto_id', 'vl.cantidad', 'vl.costo_unitario', 'v.folio', 'v.fecha_venta', 'v.user_id')->get() as $v) {
            $eventos[] = ['ts' => Carbon::parse($v->fecha_venta), 'tipo_item' => 'producto', 'item_id' => $v->producto_id, 'nombre' => $productos[$v->producto_id],
                'mov' => 'Salida por Venta', 'ref' => $v->folio, 'cant' => -(float) $v->cantidad, 'costo' => (float) $v->costo_unitario, 'user' => $v->user_id];
        }

        foreach ($eventos as $i => &$e) {
            $e['seq'] = $i;
        }
        unset($e);
        usort($eventos, fn ($a, $b) => [$a['ts']->timestamp, $a['seq']] <=> [$b['ts']->timestamp, $b['seq']]);

        DB::table('movimientos_inventario')->delete();
        $saldo = [];
        $filas = [];
        foreach ($eventos as $e) {
            $clave = $e['tipo_item'].':'.$e['item_id'];
            $saldo[$clave] = round(($saldo[$clave] ?? 0.0) + $e['cant'], 3);
            if ($saldo[$clave] < -0.0005) {
                throw new RuntimeException("Saldo negativo en Kardex: {$e['nombre']} tras {$e['ref']}.");
            }
            $filas[] = ['fecha' => $e['ts'], 'tipo_item' => $e['tipo_item'], 'item_id' => $e['item_id'], 'nombre_item' => $e['nombre'],
                'tipo_movimiento' => $e['mov'], 'referencia' => $e['ref'], 'cantidad' => $e['cant'], 'stock_resultante' => $saldo[$clave],
                'costo_unitario' => $e['costo'], 'user_id' => $e['user'], 'created_at' => $e['ts'], 'updated_at' => $e['ts']];
        }
        DB::table('movimientos_inventario')->insert($filas);

        foreach ($saldo as $clave => $valor) {
            [$tipo, $id] = explode(':', $clave);
            DB::table($tipo === 'insumo' ? 'insumos' : 'productos')->where('id', $id)->update(['stock_actual' => $valor]);
        }
    }
}
