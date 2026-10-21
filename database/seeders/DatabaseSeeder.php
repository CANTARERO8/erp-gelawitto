<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Eduardo Cantarero',
            'email' => 'admin@gelawitto.com',
            'password' => Hash::make('password123'),
            'rol' => 'Administrador',
            'activo' => true,
            'ultimo_acceso' => now()->subMinutes(12),
        ]);

        $produccion = User::create([
            'name' => 'Moises Zelaya',
            'email' => 'produccion@gelawitto.com',
            'password' => Hash::make('password123'),
            'rol' => 'Producción',
            'activo' => true,
            'ultimo_acceso' => now()->subHours(2),
        ]);

        $cajero = User::create([
            'name' => 'Valeria Mendoza',
            'email' => 'caja@gelawitto.com',
            'password' => Hash::make('password123'),
            'rol' => 'Cajero',
            'activo' => true,
            'ultimo_acceso' => now()->subMinutes(35),
        ]);

        User::create([
            'name' => 'Carlos Navarrete',
            'email' => 'finanzas@gelawitto.com',
            'password' => Hash::make('password123'),
            'rol' => 'Contador',
            'activo' => true,
            'ultimo_acceso' => now()->subDay(),
        ]);

        $kgId = DB::table('unidades_medida')->insertGetId(['nombre' => 'Kilogramo', 'abreviatura' => 'kg', 'created_at' => now(), 'updated_at' => now()]);
        $litroId = DB::table('unidades_medida')->insertGetId(['nombre' => 'Litro', 'abreviatura' => 'L', 'created_at' => now(), 'updated_at' => now()]);
        $unidadId = DB::table('unidades_medida')->insertGetId(['nombre' => 'Unidad', 'abreviatura' => 'ud', 'created_at' => now(), 'updated_at' => now()]);

        $provLacteos = DB::table('proveedores')->insertGetId([
            'nombre' => 'Lácteos del Valle S.A.',
            'ruc_nit' => 'J031000045129',
            'contacto' => 'Ing. Roberto Solís',
            'telefono' => '+505 8842 1920',
            'correo' => 'pedidos@lacteosdelvalle.com',
            'direccion' => 'Km 14 Carretera Norte, Parque Industrial',
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $provFrutas = DB::table('proveedores')->insertGetId([
            'nombre' => 'Frutas Selectas de la Montaña',
            'ruc_nit' => 'J031000089312',
            'contacto' => 'María Elena Chavarría',
            'telefono' => '+505 8715 4401',
            'correo' => 'ventas@frutasselectas.com',
            'direccion' => ' Valle de Sébaco, Bodega Central 4',
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $provEmpaques = DB::table('proveedores')->insertGetId([
            'nombre' => 'Empaques Térmicos BioPack',
            'ruc_nit' => 'J031000091204',
            'contacto' => 'Lic. Fernando Rivas',
            'telefono' => '+505 8920 3310',
            'correo' => 'comercial@biopack.com',
            'direccion' => 'Pista Suburbana, Módulo 12B',
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $clienteMostrador = DB::table('clientes')->insertGetId([
            'nombre' => 'Cliente de Mostrador',
            'identificacion' => 'CF-0001',
            'telefono' => '—',
            'correo' => 'mostrador@gelawitto.com',
            'tipo' => 'Mostrador',
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $clienteMirador = DB::table('clientes')->insertGetId([
            'nombre' => 'Cafetería y Pastelería El Mirador',
            'identificacion' => 'J031000077210',
            'telefono' => '+505 2278 4012',
            'correo' => 'compras@cafeteriaelmirador.com',
            'tipo' => 'Mayorista',
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $insLeche = DB::table('insumos')->insertGetId([
            'codigo' => 'INS-001',
            'nombre' => 'Leche entera fresca pasteurizada',
            'categoria' => 'Lácteos',
            'unidad_id' => $litroId,
            'stock_actual' => 85.000,
            'stock_minimo' => 30.000,
            'costo_promedio' => 1.35,
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $insCrema = DB::table('insumos')->insertGetId([
            'codigo' => 'INS-002',
            'nombre' => 'Crema de leche batible 35% grasa',
            'categoria' => 'Lácteos',
            'unidad_id' => $litroId,
            'stock_actual' => 28.500,
            'stock_minimo' => 15.000,
            'costo_promedio' => 4.80,
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $insFresa = DB::table('insumos')->insertGetId([
            'codigo' => 'INS-003',
            'nombre' => 'Fresa orgánica seleccionada',
            'categoria' => 'Frutas',
            'unidad_id' => $kgId,
            'stock_actual' => 18.000,
            'stock_minimo' => 12.000,
            'costo_promedio' => 3.90,
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $insFrambuesa = DB::table('insumos')->insertGetId([
            'codigo' => 'INS-004',
            'nombre' => 'Frambuesa silvestre congelada IQF',
            'categoria' => 'Frutas',
            'unidad_id' => $kgId,
            'stock_actual' => 6.500,
            'stock_minimo' => 10.000,
            'costo_promedio' => 6.40,
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $insMora = DB::table('insumos')->insertGetId([
            'codigo' => 'INS-005',
            'nombre' => 'Mora de altura seleccionada',
            'categoria' => 'Frutas',
            'unidad_id' => $kgId,
            'stock_actual' => 14.000,
            'stock_minimo' => 10.000,
            'costo_promedio' => 3.50,
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $insDurazno = DB::table('insumos')->insertGetId([
            'codigo' => 'INS-006',
            'nombre' => 'Pulpa natural de durazno criollo',
            'categoria' => 'Frutas',
            'unidad_id' => $kgId,
            'stock_actual' => 8.000,
            'stock_minimo' => 10.000,
            'costo_promedio' => 4.20,
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $insAzucar = DB::table('insumos')->insertGetId([
            'codigo' => 'INS-007',
            'nombre' => 'Azúcar blanca cristalina y dextrosa',
            'categoria' => 'Endulzantes',
            'unidad_id' => $kgId,
            'stock_actual' => 64.000,
            'stock_minimo' => 25.000,
            'costo_promedio' => 1.10,
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $insEnvase = DB::table('insumos')->insertGetId([
            'codigo' => 'INS-008',
            'nombre' => 'Envase isotérmico biodegradable 1 Litro',
            'categoria' => 'Empaques',
            'unidad_id' => $unidadId,
            'stock_actual' => 140.000,
            'stock_minimo' => 50.000,
            'costo_promedio' => 0.65,
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $prodFresa = DB::table('productos')->insertGetId([
            'sku' => 'GEL-FRE-1L',
            'nombre' => 'Gelato Artesanal Fresa Imperial',
            'sabor' => 'Fresa',
            'presentacion' => 'Tina 1 Litro',
            'precio_venta' => 14.50,
            'costo_unitario' => 5.40,
            'stock_actual' => 24.000,
            'stock_minimo' => 8.000,
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $prodFrambuesa = DB::table('productos')->insertGetId([
            'sku' => 'GEL-FRA-1L',
            'nombre' => 'Gelato Frambuesa Silvestre',
            'sabor' => 'Frambuesa',
            'presentacion' => 'Tina 1 Litro',
            'precio_venta' => 16.00,
            'costo_unitario' => 6.20,
            'stock_actual' => 16.000,
            'stock_minimo' => 8.000,
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $prodMora = DB::table('productos')->insertGetId([
            'sku' => 'GEL-MOR-1L',
            'nombre' => 'Sorbete Artesanal de Mora Intensa',
            'sabor' => 'Mora',
            'presentacion' => 'Tina 1 Litro',
            'precio_venta' => 13.80,
            'costo_unitario' => 4.90,
            'stock_actual' => 19.000,
            'stock_minimo' => 6.000,
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $prodDurazno = DB::table('productos')->insertGetId([
            'sku' => 'GEL-DUR-1L',
            'nombre' => 'Gelato Cremoso de Durazno Asado',
            'sabor' => 'Durazno',
            'presentacion' => 'Tina 1 Litro',
            'precio_venta' => 15.00,
            'costo_unitario' => 5.60,
            'stock_actual' => 11.000,
            'stock_minimo' => 8.000,
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $prodNata = DB::table('productos')->insertGetId([
            'sku' => 'GEL-NAT-1L',
            'nombre' => 'Gelato Fior di Nata Clásico',
            'sabor' => 'Nata',
            'presentacion' => 'Tina 1 Litro',
            'precio_venta' => 13.50,
            'costo_unitario' => 4.75,
            'stock_actual' => 22.000,
            'stock_minimo' => 10.000,
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $recFresa = DB::table('recetas')->insertGetId([
            'producto_id' => $prodFresa,
            'nombre' => 'Fórmula Maestra Fresa Imperial (Base 10 Litros)',
            'rendimiento_unidades' => 10.000,
            'tiempo_preparacion_min' => 50,
            'instrucciones' => 'Pasteurizar base láctea a 85 °C, madurar 6 horas a 4 °C, incorporar fruta fresca triturada y mantecar a -9 °C.',
            'activa' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('receta_insumos')->insert([
            ['receta_id' => $recFresa, 'insumo_id' => $insLeche, 'cantidad_requerida' => 5.000, 'merma_estimada_porcentaje' => 2.00, 'created_at' => now(), 'updated_at' => now()],
            ['receta_id' => $recFresa, 'insumo_id' => $insCrema, 'cantidad_requerida' => 2.500, 'merma_estimada_porcentaje' => 1.50, 'created_at' => now(), 'updated_at' => now()],
            ['receta_id' => $recFresa, 'insumo_id' => $insFresa, 'cantidad_requerida' => 3.200, 'merma_estimada_porcentaje' => 4.00, 'created_at' => now(), 'updated_at' => now()],
            ['receta_id' => $recFresa, 'insumo_id' => $insAzucar, 'cantidad_requerida' => 1.600, 'merma_estimada_porcentaje' => 0.50, 'created_at' => now(), 'updated_at' => now()],
            ['receta_id' => $recFresa, 'insumo_id' => $insEnvase, 'cantidad_requerida' => 10.000, 'merma_estimada_porcentaje' => 0.00, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $recFrambuesa = DB::table('recetas')->insertGetId([
            'producto_id' => $prodFrambuesa,
            'nombre' => 'Fórmula Maestra Frambuesa Silvestre (Base 10 Litros)',
            'rendimiento_unidades' => 10.000,
            'tiempo_preparacion_min' => 55,
            'instrucciones' => 'Infusionar base cremosa en frío, tamizar semillas al 60% y mantecar a baja revolución.',
            'activa' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('receta_insumos')->insert([
            ['receta_id' => $recFrambuesa, 'insumo_id' => $insLeche, 'cantidad_requerida' => 4.800, 'merma_estimada_porcentaje' => 2.00, 'created_at' => now(), 'updated_at' => now()],
            ['receta_id' => $recFrambuesa, 'insumo_id' => $insCrema, 'cantidad_requerida' => 2.500, 'merma_estimada_porcentaje' => 1.50, 'created_at' => now(), 'updated_at' => now()],
            ['receta_id' => $recFrambuesa, 'insumo_id' => $insFrambuesa, 'cantidad_requerida' => 3.500, 'merma_estimada_porcentaje' => 5.00, 'created_at' => now(), 'updated_at' => now()],
            ['receta_id' => $recFrambuesa, 'insumo_id' => $insAzucar, 'cantidad_requerida' => 1.700, 'merma_estimada_porcentaje' => 0.50, 'created_at' => now(), 'updated_at' => now()],
            ['receta_id' => $recFrambuesa, 'insumo_id' => $insEnvase, 'cantidad_requerida' => 10.000, 'merma_estimada_porcentaje' => 0.00, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $oc1 = DB::table('ordenes_compra')->insertGetId([
            'folio' => 'OC-2026-0001',
            'proveedor_id' => $provLacteos,
            'user_id' => $admin->id,
            'fecha_emision' => now()->subDays(3)->toDateString(),
            'fecha_recepcion' => now()->subDays(3)->toDateString(),
            'estado' => 'Recibida',
            'subtotal' => 251.25,
            'impuestos' => 0.00,
            'total' => 251.25,
            'notas' => 'Entrega refrigerada a 3 °C verificada en recepción.',
            'created_at' => now()->subDays(3),
            'updated_at' => now()->subDays(3),
        ]);

        DB::table('orden_compra_lineas')->insert([
            ['orden_compra_id' => $oc1, 'insumo_id' => $insLeche, 'cantidad' => 95.000, 'costo_unitario' => 1.35, 'subtotal' => 128.25, 'created_at' => now()->subDays(3), 'updated_at' => now()->subDays(3)],
            ['orden_compra_id' => $oc1, 'insumo_id' => $insCrema, 'cantidad' => 25.625, 'costo_unitario' => 4.80, 'subtotal' => 123.00, 'created_at' => now()->subDays(3), 'updated_at' => now()->subDays(3)],
        ]);

        $oc2 = DB::table('ordenes_compra')->insertGetId([
            'folio' => 'OC-2026-0002',
            'proveedor_id' => $provFrutas,
            'user_id' => $admin->id,
            'fecha_emision' => now()->subDays(2)->toDateString(),
            'fecha_recepcion' => now()->subDays(2)->toDateString(),
            'estado' => 'Recibida',
            'subtotal' => 185.40,
            'impuestos' => 0.00,
            'total' => 185.40,
            'notas' => 'Cosecha fresca de altura.',
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ]);

        DB::table('orden_compra_lineas')->insert([
            ['orden_compra_id' => $oc2, 'insumo_id' => $insFresa, 'cantidad' => 30.000, 'costo_unitario' => 3.90, 'subtotal' => 117.00, 'created_at' => now()->subDays(2), 'updated_at' => now()->subDays(2)],
            ['orden_compra_id' => $oc2, 'insumo_id' => $insFrambuesa, 'cantidad' => 10.688, 'costo_unitario' => 6.40, 'subtotal' => 68.40, 'created_at' => now()->subDays(2), 'updated_at' => now()->subDays(2)],
        ]);

        DB::table('lotes_produccion')->insert([
            [
                'codigo_lote' => 'LOT-2026-001',
                'receta_id' => $recFresa,
                'producto_id' => $prodFresa,
                'user_id' => $produccion->id,
                'fecha_produccion' => now()->subDay()->toDateString(),
                'cantidad_programada' => 20.000,
                'cantidad_obtenida' => 19.500,
                'merma' => 0.500,
                'costo_total_lote' => 105.30,
                'costo_unitario_resultante' => 5.40,
                'estado' => 'Finalizado',
                'notas' => 'Textura sedosa óptima, overrun del 28%.',
                'created_at' => now()->subDay(),
                'updated_at' => now()->subDay(),
            ],
            [
                'codigo_lote' => 'LOT-2026-002',
                'receta_id' => $recFrambuesa,
                'producto_id' => $prodFrambuesa,
                'user_id' => $produccion->id,
                'fecha_produccion' => now()->toDateString(),
                'cantidad_programada' => 10.000,
                'cantidad_obtenida' => 9.800,
                'merma' => 0.200,
                'costo_total_lote' => 60.76,
                'costo_unitario_resultante' => 6.20,
                'estado' => 'Finalizado',
                'notas' => 'Acidez balanceada, lote destinado a vitrina principal.',
                'created_at' => now()->subHours(5),
                'updated_at' => now()->subHours(5),
            ],
        ]);

        DB::table('movimientos_inventario')->insert([
            [
                'fecha' => now()->subDays(3),
                'tipo_item' => 'insumo',
                'item_id' => $insLeche,
                'nombre_item' => 'Leche entera fresca pasteurizada',
                'tipo_movimiento' => 'Entrada por Compra',
                'referencia' => 'OC-2026-0001',
                'cantidad' => 95.000,
                'stock_resultante' => 95.000,
                'costo_unitario' => 1.35,
                'user_id' => $admin->id,
                'created_at' => now()->subDays(3),
                'updated_at' => now()->subDays(3),
            ],
            [
                'fecha' => now()->subDay(),
                'tipo_item' => 'insumo',
                'item_id' => $insLeche,
                'nombre_item' => 'Leche entera fresca pasteurizada',
                'tipo_movimiento' => 'Consumo de Producción',
                'referencia' => 'LOT-2026-001',
                'cantidad' => -10.000,
                'stock_resultante' => 85.000,
                'costo_unitario' => 1.35,
                'user_id' => $produccion->id,
                'created_at' => now()->subDay(),
                'updated_at' => now()->subDay(),
            ],
            [
                'fecha' => now()->subDay(),
                'tipo_item' => 'producto',
                'item_id' => $prodFresa,
                'nombre_item' => 'Gelato Artesanal Fresa Imperial',
                'tipo_movimiento' => 'Ingreso de Producción',
                'referencia' => 'LOT-2026-001',
                'cantidad' => 19.500,
                'stock_resultante' => 28.000,
                'costo_unitario' => 5.40,
                'user_id' => $produccion->id,
                'created_at' => now()->subDay(),
                'updated_at' => now()->subDay(),
            ],
            [
                'fecha' => now()->subHours(3),
                'tipo_item' => 'producto',
                'item_id' => $prodFresa,
                'nombre_item' => 'Gelato Artesanal Fresa Imperial',
                'tipo_movimiento' => 'Salida por Venta',
                'referencia' => 'VTA-2026-0001',
                'cantidad' => -4.000,
                'stock_resultante' => 24.000,
                'costo_unitario' => 5.40,
                'user_id' => $cajero->id,
                'created_at' => now()->subHours(3),
                'updated_at' => now()->subHours(3),
            ],
        ]);

        $turnoAbierto = DB::table('cajas_turnos')->insertGetId([
            'codigo_turno' => 'TUR-2026-001',
            'user_id' => $cajero->id,
            'fecha_apertura' => now()->subHours(6),
            'fecha_cierre' => null,
            'monto_inicial' => 150.00,
            'ventas_efectivo' => 101.50,
            'ventas_tarjeta' => 90.00,
            'ventas_transferencia' => 45.00,
            'monto_esperado' => 251.50,
            'monto_declarado' => null,
            'diferencia' => 0.00,
            'estado' => 'Abierta',
            'observaciones' => 'Turno matutino vitrina principal.',
            'created_at' => now()->subHours(6),
            'updated_at' => now()->subHours(1),
        ]);

        $vta1 = DB::table('ventas')->insertGetId([
            'folio' => 'VTA-2026-0001',
            'caja_turno_id' => $turnoAbierto,
            'cliente_id' => $clienteMostrador,
            'user_id' => $cajero->id,
            'fecha_venta' => now()->subHours(3),
            'metodo_pago' => 'Efectivo',
            'subtotal' => 101.50,
            'impuestos' => 0.00,
            'total' => 101.50,
            'estado' => 'Completada',
            'created_at' => now()->subHours(3),
            'updated_at' => now()->subHours(3),
        ]);

        DB::table('venta_lineas')->insert([
            ['venta_id' => $vta1, 'producto_id' => $prodFresa, 'cantidad' => 4.000, 'precio_unitario' => 14.50, 'costo_unitario' => 5.40, 'subtotal' => 58.00, 'created_at' => now()->subHours(3), 'updated_at' => now()->subHours(3)],
            ['venta_id' => $vta1, 'producto_id' => $prodNata, 'cantidad' => 2.000, 'precio_unitario' => 13.50, 'costo_unitario' => 4.75, 'subtotal' => 27.00, 'created_at' => now()->subHours(3), 'updated_at' => now()->subHours(3)],
            ['venta_id' => $vta1, 'producto_id' => $prodFrambuesa, 'cantidad' => 1.000, 'precio_unitario' => 16.50, 'costo_unitario' => 6.20, 'subtotal' => 16.50, 'created_at' => now()->subHours(3), 'updated_at' => now()->subHours(3)],
        ]);

        $vta2 = DB::table('ventas')->insertGetId([
            'folio' => 'VTA-2026-0002',
            'caja_turno_id' => $turnoAbierto,
            'cliente_id' => $clienteMirador,
            'user_id' => $cajero->id,
            'fecha_venta' => now()->subHours(1),
            'metodo_pago' => 'Tarjeta',
            'subtotal' => 135.00,
            'impuestos' => 0.00,
            'total' => 135.00,
            'estado' => 'Completada',
            'created_at' => now()->subHours(1),
            'updated_at' => now()->subHours(1),
        ]);

        DB::table('venta_lineas')->insert([
            ['venta_id' => $vta2, 'producto_id' => $prodDurazno, 'cantidad' => 5.000, 'precio_unitario' => 15.00, 'costo_unitario' => 5.60, 'subtotal' => 75.00, 'created_at' => now()->subHours(1), 'updated_at' => now()->subHours(1)],
            ['venta_id' => $vta2, 'producto_id' => $prodFrambuesa, 'cantidad' => 3.750, 'precio_unitario' => 16.00, 'costo_unitario' => 6.20, 'subtotal' => 60.00, 'created_at' => now()->subHours(1), 'updated_at' => now()->subHours(1)],
        ]);

        DB::table('finanzas_movimientos')->insert([
            [
                'folio' => 'FIN-2026-001',
                'fecha' => now()->subDays(3)->toDateString(),
                'tipo' => 'Egreso',
                'categoria' => 'Compra de Insumos',
                'concepto' => 'Pago factura Lácteos del Valle S.A. (OC-2026-0001)',
                'referencia' => 'OC-2026-0001',
                'monto' => 251.25,
                'estado' => 'Pagado',
                'fecha_vencimiento' => null,
                'user_id' => $admin->id,
                'created_at' => now()->subDays(3),
                'updated_at' => now()->subDays(3),
            ],
            [
                'folio' => 'FIN-2026-002',
                'fecha' => now()->subDays(2)->toDateString(),
                'tipo' => 'Cuenta por Pagar',
                'categoria' => 'Proveedores',
                'concepto' => 'Factura a crédito 15 días Frutas Selectas (OC-2026-0002)',
                'referencia' => 'OC-2026-0002',
                'monto' => 185.40,
                'estado' => 'Pendiente',
                'fecha_vencimiento' => now()->addDays(13)->toDateString(),
                'user_id' => $admin->id,
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
            ],
            [
                'folio' => 'FIN-2026-003',
                'fecha' => now()->toDateString(),
                'tipo' => 'Ingreso',
                'categoria' => 'Ventas Mostrador y Mayorista',
                'concepto' => 'Recaudación acumulada turno TUR-2026-001',
                'referencia' => 'TUR-2026-001',
                'monto' => 236.50,
                'estado' => 'Pagado',
                'fecha_vencimiento' => null,
                'user_id' => $cajero->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('bitacora_auditoria')->insert([
            [
                'user_id' => $admin->id,
                'modulo' => 'Seguridad',
                'accion' => 'Inicialización de Sistema',
                'descripcion' => 'Configuración de parámetros maestros y roles operativos de Gelawitto ERP.',
                'ip_address' => '127.0.0.1',
                'created_at' => now()->subDays(3),
            ],
            [
                'user_id' => $produccion->id,
                'modulo' => 'Producción',
                'accion' => 'Cierre de Lote',
                'descripcion' => 'Lote LOT-2026-001 finalizado con 19.500 L de Gelato Fresa Imperial.',
                'ip_address' => '127.0.0.1',
                'created_at' => now()->subDay(),
            ],
            [
                'user_id' => $cajero->id,
                'modulo' => 'Ventas y Caja',
                'accion' => 'Apertura de Turno',
                'descripcion' => 'Apertura de turno TUR-2026-001 con fondo inicial de $150.00.',
                'ip_address' => '127.0.0.1',
                'created_at' => now()->subHours(6),
            ],
        ]);

        $this->call(GelawittoBomSeeder::class);
    }
}
