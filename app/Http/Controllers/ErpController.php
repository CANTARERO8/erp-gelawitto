<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ErpController extends Controller
{
    public function dashboard(): Response
    {
        $ventasHoy = (float) DB::table('ventas')
            ->whereDate('fecha_venta', now()->toDateString())
            ->where('estado', 'Completada')
            ->sum('total');

        $ventasTotales = (float) DB::table('ventas')
            ->where('estado', 'Completada')
            ->sum('total');

        $valorInventarioInsumos = (float) DB::table('insumos')
            ->where('activo', true)
            ->selectRaw('COALESCE(SUM(stock_actual * costo_promedio), 0) as total')
            ->value('total');

        $valorInventarioProductos = (float) DB::table('productos')
            ->where('activo', true)
            ->selectRaw('COALESCE(SUM(stock_actual * costo_unitario), 0) as total')
            ->value('total');

        $insumosCriticos = DB::table('insumos')
            ->join('unidades_medida', 'insumos.unidad_id', '=', 'unidades_medida.id')
            ->whereColumn('insumos.stock_actual', '<=', 'insumos.stock_minimo')
            ->select(
                'insumos.id',
                'insumos.codigo',
                'insumos.nombre',
                'insumos.stock_actual',
                'insumos.stock_minimo',
                'unidades_medida.abreviatura as unidad'
            )
            ->orderBy('insumos.stock_actual')
            ->get();

        $ultimosMovimientos = DB::table('movimientos_inventario')
            ->leftJoin('users', 'movimientos_inventario.user_id', '=', 'users.id')
            ->select(
                'movimientos_inventario.id',
                'movimientos_inventario.fecha',
                'movimientos_inventario.tipo_item',
                'movimientos_inventario.nombre_item',
                'movimientos_inventario.tipo_movimiento',
                'movimientos_inventario.referencia',
                'movimientos_inventario.cantidad',
                'movimientos_inventario.stock_resultante',
                'users.name as responsable'
            )
            ->orderByDesc('movimientos_inventario.fecha')
            ->orderByDesc('movimientos_inventario.id')
            ->limit(6)
            ->get();

        $productosRentabilidad = DB::table('productos')
            ->select(
                'id',
                'sku',
                'nombre',
                'sabor',
                'presentacion',
                'precio_venta',
                'costo_unitario',
                'stock_actual',
                DB::raw('ROUND(((precio_venta - costo_unitario) / NULLIF(precio_venta, 0)) * 100, 1) as margen_porcentaje')
            )
            ->orderByDesc('stock_actual')
            ->get();

        $cuentasPorPagar = (float) DB::table('finanzas_movimientos')
            ->where('estado', 'Pendiente')
            ->sum('monto');

        return Inertia::render('Erp/Dashboard', [
            'kpis' => [
                'ventas_hoy' => round($ventasHoy, 2),
                'ventas_acumuladas' => round($ventasTotales, 2),
                'valor_inventario_insumos' => round($valorInventarioInsumos, 2),
                'valor_inventario_productos' => round($valorInventarioProductos, 2),
                'alertas_stock' => $insumosCriticos->count(),
                'cuentas_por_pagar' => round($cuentasPorPagar, 2),
            ],
            'insumosCriticos' => $insumosCriticos,
            'ultimosMovimientos' => $ultimosMovimientos,
            'productosRentabilidad' => $productosRentabilidad,
        ]);
    }

    public function catalogos(): Response
    {
        return Inertia::render('Erp/Catalogos', [
            'productos' => DB::table('productos')->orderBy('sku')->get(),
            'insumos' => DB::table('insumos')
                ->join('unidades_medida', 'insumos.unidad_id', '=', 'unidades_medida.id')
                ->select('insumos.*', 'unidades_medida.abreviatura as unidad')
                ->orderBy('insumos.codigo')
                ->get(),
            'proveedores' => DB::table('proveedores')->orderBy('nombre')->get(),
            'clientes' => DB::table('clientes')->orderBy('nombre')->get(),
            'unidades' => DB::table('unidades_medida')->orderBy('nombre')->get(),
        ]);
    }

    public function storeProducto(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'sku' => ['required', 'string', 'max:30', 'unique:productos,sku'],
            'nombre' => ['required', 'string', 'max:120'],
            'sabor' => ['required', 'string', 'max:60'],
            'presentacion' => ['required', 'string', 'max:60'],
            'precio_venta' => ['required', 'numeric', 'min:0.01'],
            'costo_unitario' => ['required', 'numeric', 'min:0'],
            'stock_minimo' => ['required', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($validated, $request) {
            DB::table('productos')->insert([
                ...$validated,
                'stock_actual' => 0,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->registrarAuditoria(
                $request,
                'Catálogos',
                'Alta de Producto',
                "Se registró el producto {$validated['sku']} ({$validated['nombre']})."
            );
        });

        return back()->with('success', 'Producto registrado correctamente en el catálogo.');
    }

    public function storeInsumo(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'codigo' => ['required', 'string', 'max:30', 'unique:insumos,codigo'],
            'nombre' => ['required', 'string', 'max:120'],
            'categoria' => ['required', 'string', 'max:60'],
            'unidad_id' => ['required', 'exists:unidades_medida,id'],
            'stock_minimo' => ['required', 'numeric', 'min:0'],
            'costo_promedio' => ['required', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($validated, $request) {
            DB::table('insumos')->insert([
                ...$validated,
                'stock_actual' => 0,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->registrarAuditoria(
                $request,
                'Catálogos',
                'Alta de Insumo',
                "Se registró el insumo {$validated['codigo']} ({$validated['nombre']})."
            );
        });

        return back()->with('success', 'Insumo registrado correctamente en el catálogo.');
    }

    public function compras(): Response
    {
        $ordenes = DB::table('ordenes_compra')
            ->join('proveedores', 'ordenes_compra.proveedor_id', '=', 'proveedores.id')
            ->join('users', 'ordenes_compra.user_id', '=', 'users.id')
            ->select(
                'ordenes_compra.*',
                'proveedores.nombre as proveedor_nombre',
                'users.name as comprador_nombre'
            )
            ->orderByDesc('ordenes_compra.id')
            ->get();

        return Inertia::render('Erp/Compras', [
            'ordenes' => $ordenes,
            'proveedores' => DB::table('proveedores')->where('activo', true)->orderBy('nombre')->get(),
            'insumos' => DB::table('insumos')
                ->join('unidades_medida', 'insumos.unidad_id', '=', 'unidades_medida.id')
                ->select('insumos.id', 'insumos.codigo', 'insumos.nombre', 'insumos.costo_promedio', 'unidades_medida.abreviatura as unidad')
                ->where('insumos.activo', true)
                ->orderBy('insumos.nombre')
                ->get(),
        ]);
    }

    public function storeCompra(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'proveedor_id' => ['required', 'exists:proveedores,id'],
            'insumo_id' => ['required', 'exists:insumos,id'],
            'cantidad' => ['required', 'numeric', 'min:0.001'],
            'costo_unitario' => ['required', 'numeric', 'min:0.01'],
            'modalidad_pago' => ['required', 'in:Pagado,Pendiente'],
            'notas' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($validated, $request) {
            $insumo = DB::table('insumos')->where('id', $validated['insumo_id'])->lockForUpdate()->first();
            $consecutivo = DB::table('ordenes_compra')->count() + 1;
            $folio = sprintf('OC-2026-%04d', $consecutivo);
            $subtotal = round((float) $validated['cantidad'] * (float) $validated['costo_unitario'], 2);

            $ordenId = DB::table('ordenes_compra')->insertGetId([
                'folio' => $folio,
                'proveedor_id' => $validated['proveedor_id'],
                'user_id' => $request->user()->id,
                'fecha_emision' => now()->toDateString(),
                'fecha_recepcion' => now()->toDateString(),
                'estado' => 'Recibida',
                'subtotal' => $subtotal,
                'impuestos' => 0,
                'total' => $subtotal,
                'notas' => $validated['notas'] ?? 'Recepción directa a almacén.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('orden_compra_lineas')->insert([
                'orden_compra_id' => $ordenId,
                'insumo_id' => $insumo->id,
                'cantidad' => $validated['cantidad'],
                'costo_unitario' => $validated['costo_unitario'],
                'subtotal' => $subtotal,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $nuevoStock = round((float) $insumo->stock_actual + (float) $validated['cantidad'], 3);
            $valorActual = (float) $insumo->stock_actual * (float) $insumo->costo_promedio;
            $nuevoCostoPromedio = $nuevoStock > 0
                ? round(($valorActual + $subtotal) / $nuevoStock, 2)
                : (float) $validated['costo_unitario'];

            DB::table('insumos')->where('id', $insumo->id)->update([
                'stock_actual' => $nuevoStock,
                'costo_promedio' => $nuevoCostoPromedio,
                'updated_at' => now(),
            ]);

            DB::table('movimientos_inventario')->insert([
                'fecha' => now(),
                'tipo_item' => 'insumo',
                'item_id' => $insumo->id,
                'nombre_item' => $insumo->nombre,
                'tipo_movimiento' => 'Entrada por Compra',
                'referencia' => $folio,
                'cantidad' => $validated['cantidad'],
                'stock_resultante' => $nuevoStock,
                'costo_unitario' => $validated['costo_unitario'],
                'user_id' => $request->user()->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $finConsecutivo = DB::table('finanzas_movimientos')->count() + 1;
            DB::table('finanzas_movimientos')->insert([
                'folio' => sprintf('FIN-2026-%03d', $finConsecutivo),
                'fecha' => now()->toDateString(),
                'tipo' => $validated['modalidad_pago'] === 'Pagado' ? 'Egreso' : 'Cuenta por Pagar',
                'categoria' => 'Compra de Insumos',
                'concepto' => "Recepción de {$insumo->nombre} ({$folio})",
                'referencia' => $folio,
                'monto' => $subtotal,
                'estado' => $validated['modalidad_pago'],
                'fecha_vencimiento' => $validated['modalidad_pago'] === 'Pendiente' ? now()->addDays(15)->toDateString() : null,
                'user_id' => $request->user()->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->registrarAuditoria(
                $request,
                'Compras',
                'Recepción de Orden',
                "Orden {$folio} ingresada por \${$subtotal} de {$insumo->nombre}."
            );
        });

        return back()->with('success', 'Orden de compra recibida e integrada al Kardex e historial financiero.');
    }

    public function inventario(): Response
    {
        return Inertia::render('Erp/Inventario', [
            'insumos' => DB::table('insumos')
                ->join('unidades_medida', 'insumos.unidad_id', '=', 'unidades_medida.id')
                ->select('insumos.*', 'unidades_medida.abreviatura as unidad')
                ->orderBy('insumos.codigo')
                ->get(),
            'productos' => DB::table('productos')->orderBy('sku')->get(),
            'kardex' => DB::table('movimientos_inventario')
                ->leftJoin('users', 'movimientos_inventario.user_id', '=', 'users.id')
                ->select('movimientos_inventario.*', 'users.name as responsable')
                ->orderByDesc('movimientos_inventario.fecha')
                ->orderByDesc('movimientos_inventario.id')
                ->limit(40)
                ->get(),
        ]);
    }

    public function storeAjuste(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tipo_item' => ['required', 'in:insumo,producto'],
            'item_id' => ['required', 'integer'],
            'tipo_movimiento' => ['required', 'in:Merma,Ajuste Positivo,Ajuste Negativo'],
            'cantidad' => ['required', 'numeric', 'min:0.001'],
            'motivo' => ['required', 'string', 'max:180'],
        ]);

        DB::transaction(function () use ($validated, $request) {
            $tabla = $validated['tipo_item'] === 'insumo' ? 'insumos' : 'productos';
            $item = DB::table($tabla)->where('id', $validated['item_id'])->lockForUpdate()->first();

            if (! $item) {
                throw ValidationException::withMessages(['item_id' => 'El artículo seleccionado no existe.']);
            }

            $esSalida = in_array($validated['tipo_movimiento'], ['Merma', 'Ajuste Negativo'], true);
            $delta = $esSalida ? -1 * (float) $validated['cantidad'] : (float) $validated['cantidad'];
            $nuevoStock = round((float) $item->stock_actual + $delta, 3);

            if ($nuevoStock < 0) {
                throw ValidationException::withMessages([
                    'cantidad' => "Stock insuficiente en {$item->nombre}. Disponible: {$item->stock_actual}.",
                ]);
            }

            DB::table($tabla)->where('id', $item->id)->update([
                'stock_actual' => $nuevoStock,
                'updated_at' => now(),
            ]);

            $costo = $validated['tipo_item'] === 'insumo' ? $item->costo_promedio : $item->costo_unitario;
            $ref = 'AJU-'.now()->format('His');

            DB::table('movimientos_inventario')->insert([
                'fecha' => now(),
                'tipo_item' => $validated['tipo_item'],
                'item_id' => $item->id,
                'nombre_item' => $item->nombre,
                'tipo_movimiento' => $validated['tipo_movimiento'],
                'referencia' => $ref,
                'cantidad' => $delta,
                'stock_resultante' => $nuevoStock,
                'costo_unitario' => $costo,
                'user_id' => $request->user()->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->registrarAuditoria(
                $request,
                'Inventario',
                $validated['tipo_movimiento'],
                "{$validated['tipo_movimiento']} sobre {$item->nombre} ({$delta}): {$validated['motivo']}."
            );
        });

        return back()->with('success', 'Movimiento asentado en el Kardex inmutable.');
    }

    public function produccion(): Response
    {
        $recetas = DB::table('recetas')
            ->join('productos', 'recetas.producto_id', '=', 'productos.id')
            ->select(
                'recetas.*',
                'productos.nombre as producto_nombre',
                'productos.sku as producto_sku',
                'productos.sabor'
            )
            ->orderBy('recetas.id')
            ->get()
            ->map(function ($receta) {
                $receta->ingredientes = DB::table('receta_insumos')
                    ->join('insumos', 'receta_insumos.insumo_id', '=', 'insumos.id')
                    ->join('unidades_medida', 'insumos.unidad_id', '=', 'unidades_medida.id')
                    ->where('receta_insumos.receta_id', $receta->id)
                    ->select(
                        'receta_insumos.cantidad_requerida',
                        'receta_insumos.merma_estimada_porcentaje',
                        'insumos.nombre as insumo_nombre',
                        'insumos.stock_actual',
                        'insumos.costo_promedio',
                        'unidades_medida.abreviatura as unidad'
                    )
                    ->get();

                return $receta;
            });

        $lotes = DB::table('lotes_produccion')
            ->join('productos', 'lotes_produccion.producto_id', '=', 'productos.id')
            ->join('users', 'lotes_produccion.user_id', '=', 'users.id')
            ->select(
                'lotes_produccion.*',
                'productos.nombre as producto_nombre',
                'productos.sku as producto_sku',
                'users.name as operador_nombre'
            )
            ->orderByDesc('lotes_produccion.id')
            ->get();

        return Inertia::render('Erp/Produccion', [
            'recetas' => $recetas,
            'lotes' => $lotes,
        ]);
    }

    public function storeLote(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'receta_id' => ['required', 'exists:recetas,id'],
            'cantidad_programada' => ['required', 'numeric', 'min:1'],
            'cantidad_obtenida' => ['required', 'numeric', 'min:0.5'],
            'notas' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($validated, $request) {
            $receta = DB::table('recetas')->where('id', $validated['receta_id'])->first();
            $producto = DB::table('productos')->where('id', $receta->producto_id)->lockForUpdate()->first();
            $ingredientes = DB::table('receta_insumos')->where('receta_id', $receta->id)->get();

            $factorEscala = (float) $validated['cantidad_programada'] / (float) $receta->rendimiento_unidades;
            $consecutivo = DB::table('lotes_produccion')->count() + 1;
            $codigoLote = sprintf('LOT-2026-%03d', $consecutivo);
            $costoTotalLote = 0.0;

            foreach ($ingredientes as $ing) {
                $insumo = DB::table('insumos')->where('id', $ing->insumo_id)->lockForUpdate()->first();
                $requerido = round((float) $ing->cantidad_requerida * $factorEscala, 3);

                if ((float) $insumo->stock_actual < $requerido) {
                    throw ValidationException::withMessages([
                        'receta_id' => "Insumo insuficiente: {$insumo->nombre} requiere {$requerido} pero hay {$insumo->stock_actual}.",
                    ]);
                }

                $nuevoStockInsumo = round((float) $insumo->stock_actual - $requerido, 3);
                $costoTotalLote += $requerido * (float) $insumo->costo_promedio;

                DB::table('insumos')->where('id', $insumo->id)->update([
                    'stock_actual' => $nuevoStockInsumo,
                    'updated_at' => now(),
                ]);

                DB::table('movimientos_inventario')->insert([
                    'fecha' => now(),
                    'tipo_item' => 'insumo',
                    'item_id' => $insumo->id,
                    'nombre_item' => $insumo->nombre,
                    'tipo_movimiento' => 'Consumo de Producción',
                    'referencia' => $codigoLote,
                    'cantidad' => -1 * $requerido,
                    'stock_resultante' => $nuevoStockInsumo,
                    'costo_unitario' => $insumo->costo_promedio,
                    'user_id' => $request->user()->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $costoTotalLote = round($costoTotalLote, 2);
            $costoUnitarioResultante = round($costoTotalLote / (float) $validated['cantidad_obtenida'], 2);
            $merma = max(0, round((float) $validated['cantidad_programada'] - (float) $validated['cantidad_obtenida'], 3));

            DB::table('lotes_produccion')->insert([
                'codigo_lote' => $codigoLote,
                'receta_id' => $receta->id,
                'producto_id' => $producto->id,
                'user_id' => $request->user()->id,
                'fecha_produccion' => now()->toDateString(),
                'cantidad_programada' => $validated['cantidad_programada'],
                'cantidad_obtenida' => $validated['cantidad_obtenida'],
                'merma' => $merma,
                'costo_total_lote' => $costoTotalLote,
                'costo_unitario_resultante' => $costoUnitarioResultante,
                'estado' => 'Finalizado',
                'notas' => $validated['notas'] ?? 'Producción estándar verificada.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $nuevoStockProducto = round((float) $producto->stock_actual + (float) $validated['cantidad_obtenida'], 3);
            DB::table('productos')->where('id', $producto->id)->update([
                'stock_actual' => $nuevoStockProducto,
                'costo_unitario' => $costoUnitarioResultante,
                'updated_at' => now(),
            ]);

            DB::table('movimientos_inventario')->insert([
                'fecha' => now(),
                'tipo_item' => 'producto',
                'item_id' => $producto->id,
                'nombre_item' => $producto->nombre,
                'tipo_movimiento' => 'Ingreso de Producción',
                'referencia' => $codigoLote,
                'cantidad' => $validated['cantidad_obtenida'],
                'stock_resultante' => $nuevoStockProducto,
                'costo_unitario' => $costoUnitarioResultante,
                'user_id' => $request->user()->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->registrarAuditoria(
                $request,
                'Producción',
                'Registro de Lote',
                "Lote {$codigoLote} completado: {$validated['cantidad_obtenida']} uds de {$producto->nombre} (Costo unitario: \${$costoUnitarioResultante})."
            );
        });

        return back()->with('success', 'Lote de producción procesado: insumos descontados y producto terminado ingresado al Kardex.');
    }

    public function ventas(): Response
    {
        $turnoActivo = DB::table('cajas_turnos')
            ->join('users', 'cajas_turnos.user_id', '=', 'users.id')
            ->select('cajas_turnos.*', 'users.name as cajero_nombre')
            ->where('cajas_turnos.estado', 'Abierta')
            ->orderByDesc('cajas_turnos.id')
            ->first();

        $ventas = DB::table('ventas')
            ->leftJoin('clientes', 'ventas.cliente_id', '=', 'clientes.id')
            ->join('users', 'ventas.user_id', '=', 'users.id')
            ->select(
                'ventas.*',
                'clientes.nombre as cliente_nombre',
                'users.name as vendedor_nombre'
            )
            ->orderByDesc('ventas.id')
            ->limit(25)
            ->get();

        return Inertia::render('Erp/Ventas', [
            'turnoActivo' => $turnoActivo,
            'ventas' => $ventas,
            'productos' => DB::table('productos')->where('activo', true)->orderBy('nombre')->get(),
            'clientes' => DB::table('clientes')->where('activo', true)->orderBy('nombre')->get(),
            'historialTurnos' => DB::table('cajas_turnos')
                ->join('users', 'cajas_turnos.user_id', '=', 'users.id')
                ->select('cajas_turnos.*', 'users.name as cajero_nombre')
                ->orderByDesc('cajas_turnos.id')
                ->limit(10)
                ->get(),
        ]);
    }

    public function storeVenta(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'producto_id' => ['required', 'exists:productos,id'],
            'cliente_id' => ['required', 'exists:clientes,id'],
            'cantidad' => ['required', 'numeric', 'min:1'],
            'metodo_pago' => ['required', 'in:Efectivo,Tarjeta,Transferencia'],
        ]);

        DB::transaction(function () use ($validated, $request) {
            $turno = DB::table('cajas_turnos')->where('estado', 'Abierta')->lockForUpdate()->first();
            if (! $turno) {
                throw ValidationException::withMessages([
                    'producto_id' => 'Debe abrir un turno de caja antes de registrar ventas.',
                ]);
            }

            $producto = DB::table('productos')->where('id', $validated['producto_id'])->lockForUpdate()->first();
            if ((float) $producto->stock_actual < (float) $validated['cantidad']) {
                throw ValidationException::withMessages([
                    'cantidad' => "Stock insuficiente de {$producto->nombre}. Disponible: {$producto->stock_actual}.",
                ]);
            }

            $consecutivo = DB::table('ventas')->count() + 1;
            $folio = sprintf('VTA-2026-%04d', $consecutivo);
            $subtotal = round((float) $validated['cantidad'] * (float) $producto->precio_venta, 2);

            $ventaId = DB::table('ventas')->insertGetId([
                'folio' => $folio,
                'caja_turno_id' => $turno->id,
                'cliente_id' => $validated['cliente_id'],
                'user_id' => $request->user()->id,
                'fecha_venta' => now(),
                'metodo_pago' => $validated['metodo_pago'],
                'subtotal' => $subtotal,
                'impuestos' => 0,
                'total' => $subtotal,
                'estado' => 'Completada',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('venta_lineas')->insert([
                'venta_id' => $ventaId,
                'producto_id' => $producto->id,
                'cantidad' => $validated['cantidad'],
                'precio_unitario' => $producto->precio_venta,
                'costo_unitario' => $producto->costo_unitario,
                'subtotal' => $subtotal,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $nuevoStock = round((float) $producto->stock_actual - (float) $validated['cantidad'], 3);
            DB::table('productos')->where('id', $producto->id)->update([
                'stock_actual' => $nuevoStock,
                'updated_at' => now(),
            ]);

            DB::table('movimientos_inventario')->insert([
                'fecha' => now(),
                'tipo_item' => 'producto',
                'item_id' => $producto->id,
                'nombre_item' => $producto->nombre,
                'tipo_movimiento' => 'Salida por Venta',
                'referencia' => $folio,
                'cantidad' => -1 * (float) $validated['cantidad'],
                'stock_resultante' => $nuevoStock,
                'costo_unitario' => $producto->costo_unitario,
                'user_id' => $request->user()->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $efectivo = (float) $turno->ventas_efectivo + ($validated['metodo_pago'] === 'Efectivo' ? $subtotal : 0);
            $tarjeta = (float) $turno->ventas_tarjeta + ($validated['metodo_pago'] === 'Tarjeta' ? $subtotal : 0);
            $transferencia = (float) $turno->ventas_transferencia + ($validated['metodo_pago'] === 'Transferencia' ? $subtotal : 0);
            $esperado = round((float) $turno->monto_inicial + $efectivo, 2);

            DB::table('cajas_turnos')->where('id', $turno->id)->update([
                'ventas_efectivo' => round($efectivo, 2),
                'ventas_tarjeta' => round($tarjeta, 2),
                'ventas_transferencia' => round($transferencia, 2),
                'monto_esperado' => $esperado,
                'updated_at' => now(),
            ]);

            $finConsecutivo = DB::table('finanzas_movimientos')->count() + 1;
            DB::table('finanzas_movimientos')->insert([
                'folio' => sprintf('FIN-2026-%03d', $finConsecutivo),
                'fecha' => now()->toDateString(),
                'tipo' => 'Ingreso',
                'categoria' => 'Ventas Mostrador',
                'concepto' => "Venta {$folio} ({$producto->nombre} x {$validated['cantidad']})",
                'referencia' => $folio,
                'monto' => $subtotal,
                'estado' => 'Pagado',
                'fecha_vencimiento' => null,
                'user_id' => $request->user()->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->registrarAuditoria(
                $request,
                'Ventas y Caja',
                'Registro de Venta',
                "Venta {$folio} por \${$subtotal} en {$validated['metodo_pago']}."
            );
        });

        return back()->with('success', 'Venta registrada, stock descontado y caja actualizada.');
    }

    public function gestionarCaja(Request $request): RedirectResponse
    {
        $accion = $request->input('accion');

        if ($accion === 'abrir') {
            $validated = $request->validate([
                'monto_inicial' => ['required', 'numeric', 'min:0'],
                'observaciones' => ['nullable', 'string', 'max:200'],
            ]);

            DB::transaction(function () use ($validated, $request) {
                $consecutivo = DB::table('cajas_turnos')->count() + 1;
                $codigo = sprintf('TUR-2026-%03d', $consecutivo);

                DB::table('cajas_turnos')->insert([
                    'codigo_turno' => $codigo,
                    'user_id' => $request->user()->id,
                    'fecha_apertura' => now(),
                    'monto_inicial' => $validated['monto_inicial'],
                    'monto_esperado' => $validated['monto_inicial'],
                    'estado' => 'Abierta',
                    'observaciones' => $validated['observaciones'] ?? 'Apertura regular de turno.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $this->registrarAuditoria($request, 'Ventas y Caja', 'Apertura de Caja', "Turno {$codigo} abierto con \${$validated['monto_inicial']}.");
            });

            return back()->with('success', 'Turno de caja abierto correctamente.');
        }

        $validated = $request->validate([
            'monto_declarado' => ['required', 'numeric', 'min:0'],
            'observaciones' => ['nullable', 'string', 'max:200'],
        ]);

        DB::transaction(function () use ($validated, $request) {
            $turno = DB::table('cajas_turnos')->where('estado', 'Abierta')->lockForUpdate()->first();
            if (! $turno) {
                throw ValidationException::withMessages(['monto_declarado' => 'No hay ningún turno abierto actualmente.']);
            }

            $diferencia = round((float) $validated['monto_declarado'] - (float) $turno->monto_esperado, 2);

            DB::table('cajas_turnos')->where('id', $turno->id)->update([
                'fecha_cierre' => now(),
                'monto_declarado' => $validated['monto_declarado'],
                'diferencia' => $diferencia,
                'estado' => 'Cerrada',
                'observaciones' => $validated['observaciones'] ?? $turno->observaciones,
                'updated_at' => now(),
            ]);

            $this->registrarAuditoria(
                $request,
                'Ventas y Caja',
                'Corte de Caja',
                "Cierre de turno {$turno->codigo_turno}. Esperado: \${$turno->monto_esperado}, Declarado: \${$validated['monto_declarado']}, Diferencia: \${$diferencia}."
            );
        });

        return back()->with('success', 'Corte de caja ejecutado y registrado en auditoría.');
    }

    public function finanzas(): Response
    {
        $movimientos = DB::table('finanzas_movimientos')
            ->join('users', 'finanzas_movimientos.user_id', '=', 'users.id')
            ->select('finanzas_movimientos.*', 'users.name as responsable')
            ->orderByDesc('finanzas_movimientos.id')
            ->get();

        $ingresos = (float) DB::table('finanzas_movimientos')->where('tipo', 'Ingreso')->where('estado', 'Pagado')->sum('monto');
        $egresos = (float) DB::table('finanzas_movimientos')->where('tipo', 'Egreso')->where('estado', 'Pagado')->sum('monto');
        $porPagar = (float) DB::table('finanzas_movimientos')->where('estado', 'Pendiente')->sum('monto');

        return Inertia::render('Erp/Finanzas', [
            'movimientos' => $movimientos,
            'resumen' => [
                'ingresos' => round($ingresos, 2),
                'egresos' => round($egresos, 2),
                'flujo_neto' => round($ingresos - $egresos, 2),
                'cuentas_por_pagar' => round($porPagar, 2),
            ],
        ]);
    }

    public function storeGasto(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'categoria' => ['required', 'string', 'max:60'],
            'concepto' => ['required', 'string', 'max:180'],
            'monto' => ['required', 'numeric', 'min:0.01'],
            'estado' => ['required', 'in:Pagado,Pendiente'],
        ]);

        DB::transaction(function () use ($validated, $request) {
            $consecutivo = DB::table('finanzas_movimientos')->count() + 1;
            $folio = sprintf('FIN-2026-%03d', $consecutivo);

            DB::table('finanzas_movimientos')->insert([
                'folio' => $folio,
                'fecha' => now()->toDateString(),
                'tipo' => $validated['estado'] === 'Pagado' ? 'Egreso' : 'Cuenta por Pagar',
                'categoria' => $validated['categoria'],
                'concepto' => $validated['concepto'],
                'referencia' => 'GASTO-OP',
                'monto' => $validated['monto'],
                'estado' => $validated['estado'],
                'fecha_vencimiento' => $validated['estado'] === 'Pendiente' ? now()->addDays(15)->toDateString() : null,
                'user_id' => $request->user()->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->registrarAuditoria($request, 'Finanzas', 'Registro de Egreso', "Folio {$folio}: {$validated['concepto']} por \${$validated['monto']}.");
        });

        return back()->with('success', 'Movimiento financiero registrado correctamente.');
    }

    public function liquidarCuenta(Request $request, int $id): RedirectResponse
    {
        DB::transaction(function () use ($id, $request) {
            $mov = DB::table('finanzas_movimientos')->where('id', $id)->lockForUpdate()->first();
            if ($mov && $mov->estado === 'Pendiente') {
                DB::table('finanzas_movimientos')->where('id', $id)->update([
                    'tipo' => 'Egreso',
                    'estado' => 'Pagado',
                    'updated_at' => now(),
                ]);

                $this->registrarAuditoria($request, 'Finanzas', 'Liquidación de Cuenta', "Se liquidó la cuenta por pagar {$mov->folio} (\${$mov->monto}).");
            }
        });

        return back()->with('success', 'Cuenta por pagar liquidada y asentada como egreso pagado.');
    }

    public function seguridad(): Response
    {
        return Inertia::render('Erp/Seguridad', [
            'usuarios' => User::orderBy('id')->get(['id', 'name', 'email', 'rol', 'activo', 'ultimo_acceso', 'created_at']),
            'bitacora' => DB::table('bitacora_auditoria')
                ->leftJoin('users', 'bitacora_auditoria.user_id', '=', 'users.id')
                ->select('bitacora_auditoria.*', 'users.name as usuario_nombre', 'users.rol as usuario_rol')
                ->orderByDesc('bitacora_auditoria.id')
                ->limit(40)
                ->get(),
        ]);
    }

    public function storeUsuario(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:120', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'rol' => ['required', 'in:Administrador,Producción,Cajero,Contador'],
        ]);

        DB::transaction(function () use ($validated, $request) {
            User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'rol' => $validated['rol'],
                'activo' => true,
            ]);

            $this->registrarAuditoria($request, 'Seguridad', 'Alta de Usuario', "Se creó la cuenta para {$validated['name']} con rol {$validated['rol']}.");
        });

        return back()->with('success', 'Colaborador registrado con acceso al ERP.');
    }

    public function toggleUsuario(Request $request, int $id): RedirectResponse
    {
        $usuario = User::findOrFail($id);
        if ($usuario->id === $request->user()->id) {
            return back()->with('error', 'No puedes desactivar tu propia sesión activa.');
        }

        $usuario->update(['activo' => ! $usuario->activo]);
        $estadoTexto = $usuario->activo ? 'habilitado' : 'suspendido';

        $this->registrarAuditoria($request, 'Seguridad', 'Cambio de Estado', "Acceso {$estadoTexto} para {$usuario->name}.");

        return back()->with('success', "Estado del usuario {$usuario->name} actualizado.");
    }

    private function registrarAuditoria(Request $request, string $modulo, string $accion, string $descripcion): void
    {
        DB::table('bitacora_auditoria')->insert([
            'user_id' => $request->user()?->id,
            'modulo' => $modulo,
            'accion' => $accion,
            'descripcion' => $descripcion,
            'ip_address' => $request->ip(),
            'created_at' => now(),
        ]);
    }
}
