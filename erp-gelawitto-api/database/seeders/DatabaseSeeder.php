<?php

namespace Database\Seeders;

use App\Models\Catalogos\Insumo;
use App\Models\Catalogos\Producto;
use App\Models\Produccion\Receta;
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

        DB::table('unidades_medida')->insert([
            ['nombre' => 'Kilogramo', 'abreviatura' => 'kg', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Litro', 'abreviatura' => 'L', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Unidad', 'abreviatura' => 'ud', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $provArroz = DB::table('proveedores')->insertGetId([
            'nombre' => 'Agroindustria Arrocera de Sébaco S.A.',
            'ruc_nit' => 'J031000045129',
            'contacto' => 'Ing. Roberto Solís',
            'telefono' => '+505 8842 1920',
            'correo' => 'pedidos@arrocerasebaco.com',
            'direccion' => 'Km 103 Carretera Norte, Valle de Sébaco',
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $provAlimentos = DB::table('proveedores')->insertGetId([
            'nombre' => 'Distribuidora Alimentaria del Valle S.A.',
            'ruc_nit' => 'J031000062810',
            'contacto' => 'Lic. Patricia Morales',
            'telefono' => '+505 8630 5190',
            'correo' => 'ventas@alimentariadelvalle.com',
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
            'direccion' => 'Matagalpa, Bodega Central 4',
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

        $clienteUni = DB::table('clientes')->insertGetId([
            'nombre' => 'Kiosco Estudiantil y Comedor Saludable UNI',
            'identificacion' => 'J031000081905',
            'telefono' => '+505 2270 1518',
            'correo' => 'abastecimiento@kioscouni.edu.ni',
            'tipo' => 'Mayorista',
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Sembrar BOM de 2 niveles, Centros de Trabajo y Rutas de Operación
        $this->call(GelawittoBomSeeder::class);

        $insArroz = Insumo::where('codigo', 'INS-001')->firstOrFail();
        $insGelatina = Insumo::where('codigo', 'INS-002')->firstOrFail();
        $insAgua = Insumo::where('codigo', 'INS-003')->firstOrFail();
        $insAzucar = Insumo::where('codigo', 'INS-004')->firstOrFail();
        $insFrutas = Insumo::where('codigo', 'INS-005')->firstOrFail();
        $insLecheCond = Insumo::where('codigo', 'INS-006')->firstOrFail();
        $insVainilla = Insumo::where('codigo', 'INS-007')->firstOrFail();
        $insVaso = Insumo::where('codigo', 'INS-008')->firstOrFail();
        $insEtiqueta = Insumo::where('codigo', 'INS-009')->firstOrFail();
        $insBandeja = Insumo::where('codigo', 'INS-010')->firstOrFail();

        $prodTrad = Producto::where('sku', 'GLW-TRA-100')->firstOrFail();
        $prodFrut = Producto::where('sku', 'GLW-FRU-100')->firstOrFail();
        $prodLco = Producto::where('sku', 'GLW-LCO-100')->firstOrFail();
        $subBase = Producto::where('sku', 'SUB-GEL-BASE')->firstOrFail();

        $recTrad = Receta::where('producto_id', $prodTrad->id)->firstOrFail();
        $recFrut = Receta::where('producto_id', $prodFrut->id)->firstOrFail();
        $recLco = Receta::where('producto_id', $prodLco->id)->firstOrFail();
        $recBase = Receta::where('producto_id', $subBase->id)->firstOrFail();

        // Órdenes de Compra de Insumos Gelawitto con lote_proveedor y fecha_vencimiento
        $oc1 = DB::table('ordenes_compra')->insertGetId([
            'folio' => 'OC-2026-0001',
            'proveedor_id' => $provArroz,
            'user_id' => $admin->id,
            'fecha_emision' => now()->subDays(5)->toDateString(),
            'fecha_recepcion' => now()->subDays(5)->toDateString(),
            'estado' => 'Recibida',
            'subtotal' => 2745.00,
            'impuestos' => 0.00,
            'total' => 2745.00,
            'notas' => 'Harina de arroz extrafina y azúcar cristalina certificadas.',
            'created_at' => now()->subDays(5),
            'updated_at' => now()->subDays(5),
        ]);

        $ocl1 = DB::table('orden_compra_lineas')->insertGetId([
            'orden_compra_id' => $oc1,
            'insumo_id' => $insArroz->id,
            'cantidad' => 30.000,
            'costo_unitario' => 45.00,
            'subtotal' => 1350.00,
            'lote_proveedor' => 'LPR-ARR-2601',
            'fecha_vencimiento' => now()->addDays(120)->toDateString(),
            'created_at' => now()->subDays(5),
            'updated_at' => now()->subDays(5),
        ]);

        $ocl2 = DB::table('orden_compra_lineas')->insertGetId([
            'orden_compra_id' => $oc1,
            'insumo_id' => $insAzucar->id,
            'cantidad' => 34.875,
            'costo_unitario' => 40.00,
            'subtotal' => 1395.00,
            'lote_proveedor' => 'LPR-AZU-2601',
            'fecha_vencimiento' => now()->addDays(180)->toDateString(),
            'created_at' => now()->subDays(5),
            'updated_at' => now()->subDays(5),
        ]);

        $oc2 = DB::table('ordenes_compra')->insertGetId([
            'folio' => 'OC-2026-0002',
            'proveedor_id' => $provAlimentos,
            'user_id' => $admin->id,
            'fecha_emision' => now()->subDays(4)->toDateString(),
            'fecha_recepcion' => now()->subDays(4)->toDateString(),
            'estado' => 'Recibida',
            'subtotal' => 9750.00,
            'impuestos' => 0.00,
            'total' => 9750.00,
            'notas' => 'Gelatina Great Value, agua purificada, leche condensada y vainilla.',
            'created_at' => now()->subDays(4),
            'updated_at' => now()->subDays(4),
        ]);

        $ocl3 = DB::table('orden_compra_lineas')->insertGetId([
            'orden_compra_id' => $oc2,
            'insumo_id' => $insGelatina->id,
            'cantidad' => 6.000,
            'costo_unitario' => 1125.00,
            'subtotal' => 6750.00,
            'lote_proveedor' => 'LPR-GEL-2601',
            'fecha_vencimiento' => now()->addDays(150)->toDateString(),
            'created_at' => now()->subDays(4),
            'updated_at' => now()->subDays(4),
        ]);

        $ocl4 = DB::table('orden_compra_lineas')->insertGetId([
            'orden_compra_id' => $oc2,
            'insumo_id' => $insAgua->id,
            'cantidad' => 150.000,
            'costo_unitario' => 1.00,
            'subtotal' => 150.00,
            'lote_proveedor' => 'LPR-AGU-2601',
            'fecha_vencimiento' => now()->addDays(60)->toDateString(),
            'created_at' => now()->subDays(4),
            'updated_at' => now()->subDays(4),
        ]);

        $ocl5 = DB::table('orden_compra_lineas')->insertGetId([
            'orden_compra_id' => $oc2,
            'insumo_id' => $insLecheCond->id,
            'cantidad' => 10.000,
            'costo_unitario' => 135.00,
            'subtotal' => 1350.00,
            'lote_proveedor' => 'LPR-LCO-2601',
            'fecha_vencimiento' => now()->addDays(90)->toDateString(),
            'created_at' => now()->subDays(4),
            'updated_at' => now()->subDays(4),
        ]);

        $ocl6 = DB::table('orden_compra_lineas')->insertGetId([
            'orden_compra_id' => $oc2,
            'insumo_id' => $insVainilla->id,
            'cantidad' => 4.688,
            'costo_unitario' => 320.00,
            'subtotal' => 1500.00,
            'lote_proveedor' => 'LPR-VAI-2601',
            'fecha_vencimiento' => now()->addDays(180)->toDateString(),
            'created_at' => now()->subDays(4),
            'updated_at' => now()->subDays(4),
        ]);

        $oc3 = DB::table('ordenes_compra')->insertGetId([
            'folio' => 'OC-2026-0003',
            'proveedor_id' => $provFrutas,
            'user_id' => $admin->id,
            'fecha_emision' => now()->subDays(3)->toDateString(),
            'fecha_recepcion' => now()->subDays(3)->toDateString(),
            'estado' => 'Recibida',
            'subtotal' => 1200.00,
            'impuestos' => 0.00,
            'total' => 1200.00,
            'notas' => 'Frutas tropicales frescas seleccionadas en cadena de frío.',
            'created_at' => now()->subDays(3),
            'updated_at' => now()->subDays(3),
        ]);

        $ocl7 = DB::table('orden_compra_lineas')->insertGetId([
            'orden_compra_id' => $oc3,
            'insumo_id' => $insFrutas->id,
            'cantidad' => 8.000,
            'costo_unitario' => 150.00,
            'subtotal' => 1200.00,
            'lote_proveedor' => 'LPR-FRU-2601',
            'fecha_vencimiento' => now()->addDays(12)->toDateString(),
            'created_at' => now()->subDays(3),
            'updated_at' => now()->subDays(3),
        ]);

        $oc4 = DB::table('ordenes_compra')->insertGetId([
            'folio' => 'OC-2026-0004',
            'proveedor_id' => $provEmpaques,
            'user_id' => $admin->id,
            'fecha_emision' => now()->subDays(2)->toDateString(),
            'fecha_recepcion' => now()->subDays(2)->toDateString(),
            'estado' => 'Recibida',
            'subtotal' => 2875.00,
            'impuestos' => 0.00,
            'total' => 2875.00,
            'notas' => 'Vasos 100 mL con tapa domo, etiquetas y bandejas x5.',
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ]);

        $ocl8 = DB::table('orden_compra_lineas')->insertGetId([
            'orden_compra_id' => $oc4,
            'insumo_id' => $insVaso->id,
            'cantidad' => 250.000,
            'costo_unitario' => 8.00,
            'subtotal' => 2000.00,
            'lote_proveedor' => 'LPR-VAS-2601',
            'fecha_vencimiento' => now()->addDays(365)->toDateString(),
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ]);

        $ocl9 = DB::table('orden_compra_lineas')->insertGetId([
            'orden_compra_id' => $oc4,
            'insumo_id' => $insEtiqueta->id,
            'cantidad' => 300.000,
            'costo_unitario' => 1.50,
            'subtotal' => 450.00,
            'lote_proveedor' => 'LPR-ETI-2601',
            'fecha_vencimiento' => now()->addDays(365)->toDateString(),
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ]);

        $ocl10 = DB::table('orden_compra_lineas')->insertGetId([
            'orden_compra_id' => $oc4,
            'insumo_id' => $insBandeja->id,
            'cantidad' => 70.833,
            'costo_unitario' => 6.00,
            'subtotal' => 425.00,
            'lote_proveedor' => 'LPR-BAN-2601',
            'fecha_vencimiento' => now()->addDays(365)->toDateString(),
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ]);

        // Lotes de Insumo (FEFO) generados por las recepciones de compra
        $liArroz = DB::table('lotes_insumo')->insertGetId([
            'codigo_lote_insumo' => 'LI-2026-0001-1',
            'insumo_id' => $insArroz->id,
            'orden_compra_linea_id' => $ocl1,
            'lote_proveedor' => 'LPR-ARR-2601',
            'cantidad_inicial' => 30.000,
            'cantidad_disponible' => 25.000,
            'fecha_ingreso' => now()->subDays(5)->toDateString(),
            'fecha_vencimiento' => now()->addDays(120)->toDateString(),
            'created_at' => now()->subDays(5),
            'updated_at' => now()->subDays(5),
        ]);

        $liAzucar = DB::table('lotes_insumo')->insertGetId([
            'codigo_lote_insumo' => 'LI-2026-0001-2',
            'insumo_id' => $insAzucar->id,
            'orden_compra_linea_id' => $ocl2,
            'lote_proveedor' => 'LPR-AZU-2601',
            'cantidad_inicial' => 34.875,
            'cantidad_disponible' => 30.000,
            'fecha_ingreso' => now()->subDays(5)->toDateString(),
            'fecha_vencimiento' => now()->addDays(180)->toDateString(),
            'created_at' => now()->subDays(5),
            'updated_at' => now()->subDays(5),
        ]);

        $liGelatina = DB::table('lotes_insumo')->insertGetId([
            'codigo_lote_insumo' => 'LI-2026-0002-1',
            'insumo_id' => $insGelatina->id,
            'orden_compra_linea_id' => $ocl3,
            'lote_proveedor' => 'LPR-GEL-2601',
            'cantidad_inicial' => 6.000,
            'cantidad_disponible' => 4.500,
            'fecha_ingreso' => now()->subDays(4)->toDateString(),
            'fecha_vencimiento' => now()->addDays(150)->toDateString(),
            'created_at' => now()->subDays(4),
            'updated_at' => now()->subDays(4),
        ]);

        $liAgua = DB::table('lotes_insumo')->insertGetId([
            'codigo_lote_insumo' => 'LI-2026-0002-2',
            'insumo_id' => $insAgua->id,
            'orden_compra_linea_id' => $ocl4,
            'lote_proveedor' => 'LPR-AGU-2601',
            'cantidad_inicial' => 150.000,
            'cantidad_disponible' => 120.000,
            'fecha_ingreso' => now()->subDays(4)->toDateString(),
            'fecha_vencimiento' => now()->addDays(60)->toDateString(),
            'created_at' => now()->subDays(4),
            'updated_at' => now()->subDays(4),
        ]);

        $liLecheCond = DB::table('lotes_insumo')->insertGetId([
            'codigo_lote_insumo' => 'LI-2026-0002-3',
            'insumo_id' => $insLecheCond->id,
            'orden_compra_linea_id' => $ocl5,
            'lote_proveedor' => 'LPR-LCO-2601',
            'cantidad_inicial' => 10.000,
            'cantidad_disponible' => 8.000,
            'fecha_ingreso' => now()->subDays(4)->toDateString(),
            'fecha_vencimiento' => now()->addDays(90)->toDateString(),
            'created_at' => now()->subDays(4),
            'updated_at' => now()->subDays(4),
        ]);

        $liVainilla = DB::table('lotes_insumo')->insertGetId([
            'codigo_lote_insumo' => 'LI-2026-0002-4',
            'insumo_id' => $insVainilla->id,
            'orden_compra_linea_id' => $ocl6,
            'lote_proveedor' => 'LPR-VAI-2601',
            'cantidad_inicial' => 5.500,
            'cantidad_disponible' => 5.000,
            'fecha_ingreso' => now()->subDays(4)->toDateString(),
            'fecha_vencimiento' => now()->addDays(180)->toDateString(),
            'created_at' => now()->subDays(4),
            'updated_at' => now()->subDays(4),
        ]);

        $liFrutas = DB::table('lotes_insumo')->insertGetId([
            'codigo_lote_insumo' => 'LI-2026-0003-1',
            'insumo_id' => $insFrutas->id,
            'orden_compra_linea_id' => $ocl7,
            'lote_proveedor' => 'LPR-FRU-2601',
            'cantidad_inicial' => 8.000,
            'cantidad_disponible' => 6.500,
            'fecha_ingreso' => now()->subDays(3)->toDateString(),
            'fecha_vencimiento' => now()->addDays(12)->toDateString(),
            'created_at' => now()->subDays(3),
            'updated_at' => now()->subDays(3),
        ]);

        $liVaso = DB::table('lotes_insumo')->insertGetId([
            'codigo_lote_insumo' => 'LI-2026-0004-1',
            'insumo_id' => $insVaso->id,
            'orden_compra_linea_id' => $ocl8,
            'lote_proveedor' => 'LPR-VAS-2601',
            'cantidad_inicial' => 250.000,
            'cantidad_disponible' => 180.000,
            'fecha_ingreso' => now()->subDays(2)->toDateString(),
            'fecha_vencimiento' => now()->addDays(365)->toDateString(),
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ]);

        $liEtiqueta = DB::table('lotes_insumo')->insertGetId([
            'codigo_lote_insumo' => 'LI-2026-0004-2',
            'insumo_id' => $insEtiqueta->id,
            'orden_compra_linea_id' => $ocl9,
            'lote_proveedor' => 'LPR-ETI-2601',
            'cantidad_inicial' => 300.000,
            'cantidad_disponible' => 200.000,
            'fecha_ingreso' => now()->subDays(2)->toDateString(),
            'fecha_vencimiento' => now()->addDays(365)->toDateString(),
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ]);

        $liBandeja = DB::table('lotes_insumo')->insertGetId([
            'codigo_lote_insumo' => 'LI-2026-0004-3',
            'insumo_id' => $insBandeja->id,
            'orden_compra_linea_id' => $ocl10,
            'lote_proveedor' => 'LPR-BAN-2601',
            'cantidad_inicial' => 70.000,
            'cantidad_disponible' => 50.000,
            'fecha_ingreso' => now()->subDays(2)->toDateString(),
            'fecha_vencimiento' => now()->addDays(365)->toDateString(),
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ]);

        // Lotes de Producción con tiempos reales para cálculo de OEE y Tiempo de Ciclo
        $loteBase = DB::table('lotes_produccion')->insertGetId([
            'codigo_lote' => 'LOT-2026-001',
            'receta_id' => $recBase->id,
            'producto_id' => $subBase->id,
            'user_id' => $produccion->id,
            'fecha_produccion' => now()->subDays(3)->toDateString(),
            'fecha_vencimiento' => now()->addDays(10)->toDateString(),
            'inicio_real' => now()->subDays(3)->setTime(8, 0, 0),
            'fin_real' => now()->subDays(3)->setTime(14, 55, 0),
            'tiempo_paro_min' => 5.00,
            'cantidad_programada' => 20.000,
            'cantidad_obtenida' => 20.000,
            'cantidad_disponible' => 24.500,
            'merma' => 0.000,
            'costo_total_lote' => 1950.00,
            'costo_unitario_resultante' => 97.50,
            'estado' => 'Finalizado',
            'estado_calidad' => 'Liberado',
            'notas' => 'Subensamble Nivel 1 Base Gelificada de Arroz homogénea a 85 °C.',
            'created_at' => now()->subDays(3),
            'updated_at' => now()->subDays(3),
        ]);

        $loteTrad1 = DB::table('lotes_produccion')->insertGetId([
            'codigo_lote' => 'LOT-2026-002',
            'receta_id' => $recTrad->id,
            'producto_id' => $prodTrad->id,
            'user_id' => $produccion->id,
            'fecha_produccion' => now()->subDays(2)->toDateString(),
            'fecha_vencimiento' => now()->addDays(12)->toDateString(),
            'inicio_real' => now()->subDays(2)->setTime(8, 0, 0),
            'fin_real' => now()->subDays(2)->setTime(11, 20, 0),
            'tiempo_paro_min' => 8.00,
            'cantidad_programada' => 30.000,
            'cantidad_obtenida' => 30.000,
            'cantidad_disponible' => 5.000,
            'merma' => 0.000,
            'costo_total_lote' => 623.10,
            'costo_unitario_resultante' => 20.77,
            'estado' => 'Finalizado',
            'estado_calidad' => 'Liberado',
            'notas' => 'Corrida estándar de Gelawitto Tradicional de Arroz.',
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ]);

        $loteFrut1 = DB::table('lotes_produccion')->insertGetId([
            'codigo_lote' => 'LOT-2026-003',
            'receta_id' => $recFrut->id,
            'producto_id' => $prodFrut->id,
            'user_id' => $produccion->id,
            'fecha_produccion' => now()->subDays(2)->toDateString(),
            'fecha_vencimiento' => now()->addDays(11)->toDateString(),
            'inicio_real' => now()->subDays(2)->setTime(13, 0, 0),
            'fin_real' => now()->subDays(2)->setTime(16, 40, 0),
            'tiempo_paro_min' => 10.00,
            'cantidad_programada' => 35.000,
            'cantidad_obtenida' => 35.000,
            'cantidad_disponible' => 10.000,
            'merma' => 0.000,
            'costo_total_lote' => 752.50,
            'costo_unitario_resultante' => 21.50,
            'estado' => 'Finalizado',
            'estado_calidad' => 'Liberado',
            'notas' => 'Gelawitto con Frutas Variadas (20 g fruta + 80 mL base por vaso).',
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ]);

        $loteLco1 = DB::table('lotes_produccion')->insertGetId([
            'codigo_lote' => 'LOT-2026-004',
            'receta_id' => $recLco->id,
            'producto_id' => $prodLco->id,
            'user_id' => $produccion->id,
            'fecha_produccion' => now()->subDay()->toDateString(),
            'fecha_vencimiento' => now()->addDays(14)->toDateString(),
            'inicio_real' => now()->subDay()->setTime(8, 30, 0),
            'fin_real' => now()->subDay()->setTime(12, 10, 0),
            'tiempo_paro_min' => 6.00,
            'cantidad_programada' => 36.000,
            'cantidad_obtenida' => 36.000,
            'cantidad_disponible' => 18.000,
            'merma' => 0.000,
            'costo_total_lote' => 774.72,
            'costo_unitario_resultante' => 21.52,
            'estado' => 'Finalizado',
            'estado_calidad' => 'Liberado',
            'notas' => 'Gelawitto Tres Leches y Arroz con emulsión de leche condensada.',
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

        $loteTrad2 = DB::table('lotes_produccion')->insertGetId([
            'codigo_lote' => 'LOT-2026-005',
            'receta_id' => $recTrad->id,
            'producto_id' => $prodTrad->id,
            'user_id' => $produccion->id,
            'fecha_produccion' => now()->subDay()->toDateString(),
            'fecha_vencimiento' => now()->addDays(15)->toDateString(),
            'inicio_real' => now()->subDay()->setTime(14, 0, 0),
            'fin_real' => now()->subDay()->setTime(16, 55, 0),
            'tiempo_paro_min' => 5.00,
            'cantidad_programada' => 25.000,
            'cantidad_obtenida' => 25.000,
            'cantidad_disponible' => 20.000,
            'merma' => 0.000,
            'costo_total_lote' => 519.25,
            'costo_unitario_resultante' => 20.77,
            'estado' => 'Finalizado',
            'estado_calidad' => 'Liberado',
            'notas' => 'Segundo lote de Gelawitto Tradicional liberado tras inspección.',
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

        $loteFrut2 = DB::table('lotes_produccion')->insertGetId([
            'codigo_lote' => 'LOT-2026-006',
            'receta_id' => $recFrut->id,
            'producto_id' => $prodFrut->id,
            'user_id' => $produccion->id,
            'fecha_produccion' => now()->toDateString(),
            'fecha_vencimiento' => now()->addDays(14)->toDateString(),
            'inicio_real' => now()->subHours(5),
            'fin_real' => now()->subHours(3)->subMinutes(15),
            'tiempo_paro_min' => 5.00,
            'cantidad_programada' => 10.000,
            'cantidad_obtenida' => 10.000,
            'cantidad_disponible' => 9.000,
            'merma' => 0.000,
            'costo_total_lote' => 215.00,
            'costo_unitario_resultante' => 21.50,
            'estado' => 'Finalizado',
            'estado_calidad' => 'Scrap',
            'notas' => 'Inspección CC-2026-006: 1 vaso descartado por gelificación incompleta tras alerta IIoT.',
            'created_at' => now()->subHours(5),
            'updated_at' => now()->subHours(3),
        ]);

        // Trazabilidad de consumo de lotes de insumo y subensambles (consumos_lote)
        DB::table('consumos_lote')->insert([
            ['lote_produccion_id' => $loteBase, 'lote_insumo_id' => $liArroz, 'subproducto_lote_id' => null, 'cantidad' => 0.750, 'created_at' => now()->subDays(3), 'updated_at' => now()->subDays(3)],
            ['lote_produccion_id' => $loteBase, 'lote_insumo_id' => $liGelatina, 'subproducto_lote_id' => null, 'cantidad' => 0.600, 'created_at' => now()->subDays(3), 'updated_at' => now()->subDays(3)],
            ['lote_produccion_id' => $loteBase, 'lote_insumo_id' => $liAgua, 'subproducto_lote_id' => null, 'cantidad' => 7.500, 'created_at' => now()->subDays(3), 'updated_at' => now()->subDays(3)],
            ['lote_produccion_id' => $loteBase, 'lote_insumo_id' => $liAzucar, 'subproducto_lote_id' => null, 'cantidad' => 0.375, 'created_at' => now()->subDays(3), 'updated_at' => now()->subDays(3)],

            ['lote_produccion_id' => $loteTrad1, 'lote_insumo_id' => null, 'subproducto_lote_id' => $loteBase, 'cantidad' => 3.000, 'created_at' => now()->subDays(2), 'updated_at' => now()->subDays(2)],
            ['lote_produccion_id' => $loteTrad1, 'lote_insumo_id' => $liVainilla, 'subproducto_lote_id' => null, 'cantidad' => 0.030, 'created_at' => now()->subDays(2), 'updated_at' => now()->subDays(2)],
            ['lote_produccion_id' => $loteTrad1, 'lote_insumo_id' => $liVaso, 'subproducto_lote_id' => null, 'cantidad' => 30.000, 'created_at' => now()->subDays(2), 'updated_at' => now()->subDays(2)],
            ['lote_produccion_id' => $loteTrad1, 'lote_insumo_id' => $liEtiqueta, 'subproducto_lote_id' => null, 'cantidad' => 30.000, 'created_at' => now()->subDays(2), 'updated_at' => now()->subDays(2)],
            ['lote_produccion_id' => $loteTrad1, 'lote_insumo_id' => $liBandeja, 'subproducto_lote_id' => null, 'cantidad' => 6.000, 'created_at' => now()->subDays(2), 'updated_at' => now()->subDays(2)],

            ['lote_produccion_id' => $loteFrut1, 'lote_insumo_id' => null, 'subproducto_lote_id' => $loteBase, 'cantidad' => 2.800, 'created_at' => now()->subDays(2), 'updated_at' => now()->subDays(2)],
            ['lote_produccion_id' => $loteFrut1, 'lote_insumo_id' => $liFrutas, 'subproducto_lote_id' => null, 'cantidad' => 0.700, 'created_at' => now()->subDays(2), 'updated_at' => now()->subDays(2)],
            ['lote_produccion_id' => $loteFrut1, 'lote_insumo_id' => $liVaso, 'subproducto_lote_id' => null, 'cantidad' => 35.000, 'created_at' => now()->subDays(2), 'updated_at' => now()->subDays(2)],
            ['lote_produccion_id' => $loteFrut1, 'lote_insumo_id' => $liEtiqueta, 'subproducto_lote_id' => null, 'cantidad' => 35.000, 'created_at' => now()->subDays(2), 'updated_at' => now()->subDays(2)],
            ['lote_produccion_id' => $loteFrut1, 'lote_insumo_id' => $liBandeja, 'subproducto_lote_id' => null, 'cantidad' => 7.000, 'created_at' => now()->subDays(2), 'updated_at' => now()->subDays(2)],

            ['lote_produccion_id' => $loteLco1, 'lote_insumo_id' => null, 'subproducto_lote_id' => $loteBase, 'cantidad' => 2.880, 'created_at' => now()->subDay(), 'updated_at' => now()->subDay()],
            ['lote_produccion_id' => $loteLco1, 'lote_insumo_id' => $liLecheCond, 'subproducto_lote_id' => null, 'cantidad' => 0.720, 'created_at' => now()->subDay(), 'updated_at' => now()->subDay()],
            ['lote_produccion_id' => $loteLco1, 'lote_insumo_id' => $liVaso, 'subproducto_lote_id' => null, 'cantidad' => 36.000, 'created_at' => now()->subDay(), 'updated_at' => now()->subDay()],

            ['lote_produccion_id' => $loteTrad2, 'lote_insumo_id' => null, 'subproducto_lote_id' => $loteBase, 'cantidad' => 2.500, 'created_at' => now()->subDay(), 'updated_at' => now()->subDay()],
            ['lote_produccion_id' => $loteTrad2, 'lote_insumo_id' => $liVaso, 'subproducto_lote_id' => null, 'cantidad' => 25.000, 'created_at' => now()->subDay(), 'updated_at' => now()->subDay()],

            ['lote_produccion_id' => $loteFrut2, 'lote_insumo_id' => null, 'subproducto_lote_id' => $loteBase, 'cantidad' => 0.800, 'created_at' => now()->subHours(5), 'updated_at' => now()->subHours(5)],
            ['lote_produccion_id' => $loteFrut2, 'lote_insumo_id' => $liFrutas, 'subproducto_lote_id' => null, 'cantidad' => 0.200, 'created_at' => now()->subHours(5), 'updated_at' => now()->subHours(5)],
            ['lote_produccion_id' => $loteFrut2, 'lote_insumo_id' => $liVaso, 'subproducto_lote_id' => null, 'cantidad' => 10.000, 'created_at' => now()->subHours(5), 'updated_at' => now()->subHours(5)],
        ]);

        // Controles de Calidad e Integración IIoT
        DB::table('controles_calidad')->insert([
            [
                'codigo_control' => 'CC-2026-001',
                'lote_id' => $loteBase,
                'user_id' => $produccion->id,
                'fecha_inspeccion' => now()->subDays(3)->setTime(11, 0, 0),
                'unidades_inspeccionadas' => 20.000,
                'unidades_no_conformes' => 0.000,
                'defecto' => null,
                'disposicion' => 'Liberación',
                'costo_perdido' => 0.00,
                'notas' => 'Viscosidad y pH dentro de especificación.',
                'created_at' => now()->subDays(3),
                'updated_at' => now()->subDays(3),
            ],
            [
                'codigo_control' => 'CC-2026-002',
                'lote_id' => $loteTrad1,
                'user_id' => $produccion->id,
                'fecha_inspeccion' => now()->subDays(2)->setTime(11, 30, 0),
                'unidades_inspeccionadas' => 30.000,
                'unidades_no_conformes' => 0.000,
                'defecto' => null,
                'disposicion' => 'Liberación',
                'costo_perdido' => 0.00,
                'notas' => 'Gelificación firme, sellado hermético.',
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
            ],
            [
                'codigo_control' => 'CC-2026-003',
                'lote_id' => $loteFrut1,
                'user_id' => $produccion->id,
                'fecha_inspeccion' => now()->subDays(2)->setTime(17, 0, 0),
                'unidades_inspeccionadas' => 35.000,
                'unidades_no_conformes' => 0.000,
                'defecto' => null,
                'disposicion' => 'Liberación',
                'costo_perdido' => 0.00,
                'notas' => 'Distribución uniforme de fruta en suspensión.',
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
            ],
            [
                'codigo_control' => 'CC-2026-004',
                'lote_id' => $loteLco1,
                'user_id' => $produccion->id,
                'fecha_inspeccion' => now()->subDay()->setTime(12, 20, 0),
                'unidades_inspeccionadas' => 36.000,
                'unidades_no_conformes' => 0.000,
                'defecto' => null,
                'disposicion' => 'Liberación',
                'costo_perdido' => 0.00,
                'notas' => 'Emulsión estable sin sinéresis.',
                'created_at' => now()->subDay(),
                'updated_at' => now()->subDay(),
            ],
            [
                'codigo_control' => 'CC-2026-005',
                'lote_id' => $loteTrad2,
                'user_id' => $produccion->id,
                'fecha_inspeccion' => now()->subDay()->setTime(17, 10, 0),
                'unidades_inspeccionadas' => 25.000,
                'unidades_no_conformes' => 0.000,
                'defecto' => null,
                'disposicion' => 'Liberación',
                'costo_perdido' => 0.00,
                'notas' => 'Lote conforme liberado a cámara de despacho.',
                'created_at' => now()->subDay(),
                'updated_at' => now()->subDay(),
            ],
            [
                'codigo_control' => 'CC-2026-006',
                'lote_id' => $loteFrut2,
                'user_id' => $produccion->id,
                'fecha_inspeccion' => now()->subHours(3),
                'unidades_inspeccionadas' => 10.000,
                'unidades_no_conformes' => 1.000,
                'defecto' => 'Gelificación incompleta en 1 vaso tras excursión térmica a 8.90 °C en REF-01',
                'disposicion' => 'Scrap',
                'costo_perdido' => 21.50,
                'notas' => '9 vasos conformes liberados, 1 vaso dado de baja por Scrap.',
                'created_at' => now()->subHours(3),
                'updated_at' => now()->subHours(3),
            ],
        ]);

        DB::table('lecturas_temperatura')->insert([
            ['dispositivo' => 'REF-01', 'origen' => 'Sensor', 'temperatura' => 3.80, 'fuera_de_rango' => false, 'fecha_lectura' => now()->subHours(5), 'created_at' => now()->subHours(5), 'updated_at' => now()->subHours(5)],
            ['dispositivo' => 'REF-01', 'origen' => 'Sensor', 'temperatura' => 4.10, 'fuera_de_rango' => false, 'fecha_lectura' => now()->subHours(4), 'created_at' => now()->subHours(4), 'updated_at' => now()->subHours(4)],
            ['dispositivo' => 'REF-01', 'origen' => 'Sensor', 'temperatura' => 8.90, 'fuera_de_rango' => true, 'fecha_lectura' => now()->subHours(3)->subMinutes(30), 'created_at' => now()->subHours(3)->subMinutes(30), 'updated_at' => now()->subHours(3)->subMinutes(30)],
            ['dispositivo' => 'REF-01', 'origen' => 'Sensor', 'temperatura' => 4.20, 'fuera_de_rango' => false, 'fecha_lectura' => now()->subHours(2), 'created_at' => now()->subHours(2), 'updated_at' => now()->subHours(2)],
        ]);

        // Movimientos de Inventario (Kardex)
        DB::table('movimientos_inventario')->insert([
            ['fecha' => now()->subDays(5), 'tipo_item' => 'insumo', 'item_id' => $insArroz->id, 'nombre_item' => $insArroz->nombre, 'tipo_movimiento' => 'Entrada por Compra', 'referencia' => 'OC-2026-0001', 'cantidad' => 30.000, 'stock_resultante' => 30.000, 'costo_unitario' => 45.00, 'user_id' => $admin->id, 'created_at' => now()->subDays(5), 'updated_at' => now()->subDays(5)],
            ['fecha' => now()->subDays(4), 'tipo_item' => 'insumo', 'item_id' => $insGelatina->id, 'nombre_item' => $insGelatina->nombre, 'tipo_movimiento' => 'Entrada por Compra', 'referencia' => 'OC-2026-0002', 'cantidad' => 6.000, 'stock_resultante' => 6.000, 'costo_unitario' => 1125.00, 'user_id' => $admin->id, 'created_at' => now()->subDays(4), 'updated_at' => now()->subDays(4)],
            ['fecha' => now()->subDays(3), 'tipo_item' => 'insumo', 'item_id' => $insArroz->id, 'nombre_item' => $insArroz->nombre, 'tipo_movimiento' => 'Consumo de Producción', 'referencia' => 'LOT-2026-001', 'cantidad' => -0.750, 'stock_resultante' => 25.000, 'costo_unitario' => 45.00, 'user_id' => $produccion->id, 'created_at' => now()->subDays(3), 'updated_at' => now()->subDays(3)],
            ['fecha' => now()->subDays(3), 'tipo_item' => 'insumo', 'item_id' => $insGelatina->id, 'nombre_item' => $insGelatina->nombre, 'tipo_movimiento' => 'Consumo de Producción', 'referencia' => 'LOT-2026-001', 'cantidad' => -0.600, 'stock_resultante' => 4.500, 'costo_unitario' => 1125.00, 'user_id' => $produccion->id, 'created_at' => now()->subDays(3), 'updated_at' => now()->subDays(3)],
            ['fecha' => now()->subDays(3), 'tipo_item' => 'insumo', 'item_id' => $insAgua->id, 'nombre_item' => $insAgua->nombre, 'tipo_movimiento' => 'Consumo de Producción', 'referencia' => 'LOT-2026-001', 'cantidad' => -7.500, 'stock_resultante' => 120.000, 'costo_unitario' => 1.00, 'user_id' => $produccion->id, 'created_at' => now()->subDays(3), 'updated_at' => now()->subDays(3)],
            ['fecha' => now()->subDays(3), 'tipo_item' => 'producto', 'item_id' => $subBase->id, 'nombre_item' => $subBase->nombre, 'tipo_movimiento' => 'Ingreso de Producción', 'referencia' => 'LOT-2026-001', 'cantidad' => 7.500, 'stock_resultante' => 30.000, 'costo_unitario' => 97.50, 'user_id' => $produccion->id, 'created_at' => now()->subDays(3), 'updated_at' => now()->subDays(3)],

            ['fecha' => now()->subDays(2), 'tipo_item' => 'producto', 'item_id' => $subBase->id, 'nombre_item' => $subBase->nombre, 'tipo_movimiento' => 'Consumo de Producción', 'referencia' => 'LOT-2026-002', 'cantidad' => -3.000, 'stock_resultante' => 27.000, 'costo_unitario' => 97.50, 'user_id' => $produccion->id, 'created_at' => now()->subDays(2), 'updated_at' => now()->subDays(2)],
            ['fecha' => now()->subDays(2), 'tipo_item' => 'insumo', 'item_id' => $insVaso->id, 'nombre_item' => $insVaso->nombre, 'tipo_movimiento' => 'Consumo de Producción', 'referencia' => 'LOT-2026-002', 'cantidad' => -30.000, 'stock_resultante' => 220.000, 'costo_unitario' => 8.00, 'user_id' => $produccion->id, 'created_at' => now()->subDays(2), 'updated_at' => now()->subDays(2)],
            ['fecha' => now()->subDays(2), 'tipo_item' => 'producto', 'item_id' => $prodTrad->id, 'nombre_item' => $prodTrad->nombre, 'tipo_movimiento' => 'Ingreso de Producción', 'referencia' => 'LOT-2026-002', 'cantidad' => 30.000, 'stock_resultante' => 30.000, 'costo_unitario' => 20.77, 'user_id' => $produccion->id, 'created_at' => now()->subDays(2), 'updated_at' => now()->subDays(2)],

            ['fecha' => now()->subDays(2), 'tipo_item' => 'producto', 'item_id' => $subBase->id, 'nombre_item' => $subBase->nombre, 'tipo_movimiento' => 'Consumo de Producción', 'referencia' => 'LOT-2026-003', 'cantidad' => -2.800, 'stock_resultante' => 24.200, 'costo_unitario' => 97.50, 'user_id' => $produccion->id, 'created_at' => now()->subDays(2), 'updated_at' => now()->subDays(2)],
            ['fecha' => now()->subDays(2), 'tipo_item' => 'insumo', 'item_id' => $insFrutas->id, 'nombre_item' => $insFrutas->nombre, 'tipo_movimiento' => 'Consumo de Producción', 'referencia' => 'LOT-2026-003', 'cantidad' => -0.700, 'stock_resultante' => 7.300, 'costo_unitario' => 150.00, 'user_id' => $produccion->id, 'created_at' => now()->subDays(2), 'updated_at' => now()->subDays(2)],
            ['fecha' => now()->subDays(2), 'tipo_item' => 'producto', 'item_id' => $prodFrut->id, 'nombre_item' => $prodFrut->nombre, 'tipo_movimiento' => 'Ingreso de Producción', 'referencia' => 'LOT-2026-003', 'cantidad' => 35.000, 'stock_resultante' => 35.000, 'costo_unitario' => 21.50, 'user_id' => $produccion->id, 'created_at' => now()->subDays(2), 'updated_at' => now()->subDays(2)],

            ['fecha' => now()->subDay(), 'tipo_item' => 'producto', 'item_id' => $subBase->id, 'nombre_item' => $subBase->nombre, 'tipo_movimiento' => 'Consumo de Producción', 'referencia' => 'LOT-2026-004', 'cantidad' => -2.880, 'stock_resultante' => 21.320, 'costo_unitario' => 97.50, 'user_id' => $produccion->id, 'created_at' => now()->subDay(), 'updated_at' => now()->subDay()],
            ['fecha' => now()->subDay(), 'tipo_item' => 'insumo', 'item_id' => $insLecheCond->id, 'nombre_item' => $insLecheCond->nombre, 'tipo_movimiento' => 'Consumo de Producción', 'referencia' => 'LOT-2026-004', 'cantidad' => -0.720, 'stock_resultante' => 8.000, 'costo_unitario' => 135.00, 'user_id' => $produccion->id, 'created_at' => now()->subDay(), 'updated_at' => now()->subDay()],
            ['fecha' => now()->subDay(), 'tipo_item' => 'producto', 'item_id' => $prodLco->id, 'nombre_item' => $prodLco->nombre, 'tipo_movimiento' => 'Ingreso de Producción', 'referencia' => 'LOT-2026-004', 'cantidad' => 36.000, 'stock_resultante' => 36.000, 'costo_unitario' => 21.52, 'user_id' => $produccion->id, 'created_at' => now()->subDay(), 'updated_at' => now()->subDay()],

            ['fecha' => now()->subHours(5), 'tipo_item' => 'producto', 'item_id' => $subBase->id, 'nombre_item' => $subBase->nombre, 'tipo_movimiento' => 'Consumo de Producción', 'referencia' => 'LOT-2026-006', 'cantidad' => -0.800, 'stock_resultante' => 24.500, 'costo_unitario' => 97.50, 'user_id' => $produccion->id, 'created_at' => now()->subHours(5), 'updated_at' => now()->subHours(5)],
            ['fecha' => now()->subHours(5), 'tipo_item' => 'insumo', 'item_id' => $insFrutas->id, 'nombre_item' => $insFrutas->nombre, 'tipo_movimiento' => 'Consumo de Producción', 'referencia' => 'LOT-2026-006', 'cantidad' => -0.200, 'stock_resultante' => 6.500, 'costo_unitario' => 150.00, 'user_id' => $produccion->id, 'created_at' => now()->subHours(5), 'updated_at' => now()->subHours(5)],
            ['fecha' => now()->subHours(5), 'tipo_item' => 'insumo', 'item_id' => $insVaso->id, 'nombre_item' => $insVaso->nombre, 'tipo_movimiento' => 'Consumo de Producción', 'referencia' => 'LOT-2026-006', 'cantidad' => -10.000, 'stock_resultante' => 180.000, 'costo_unitario' => 8.00, 'user_id' => $produccion->id, 'created_at' => now()->subHours(5), 'updated_at' => now()->subHours(5)],
            ['fecha' => now()->subHours(3), 'tipo_item' => 'producto', 'item_id' => $prodFrut->id, 'nombre_item' => $prodFrut->nombre, 'tipo_movimiento' => 'Scrap por Calidad', 'referencia' => 'LOT-2026-006', 'cantidad' => -1.000, 'stock_resultante' => 19.000, 'costo_unitario' => 21.50, 'user_id' => $produccion->id, 'created_at' => now()->subHours(3), 'updated_at' => now()->subHours(3)],
        ]);

        // Turno de Caja y Ventas con trazabilidad FEFO (lote_produccion_id)
        $turnoAbierto = DB::table('cajas_turnos')->insertGetId([
            'codigo_turno' => 'TUR-2026-001',
            'user_id' => $cajero->id,
            'fecha_apertura' => now()->subHours(6),
            'fecha_cierre' => null,
            'monto_inicial' => 3500.00,
            'ventas_efectivo' => 2995.00,
            'ventas_tarjeta' => 2580.00,
            'ventas_transferencia' => 2460.00,
            'monto_esperado' => 6495.00,
            'monto_declarado' => null,
            'diferencia' => 0.00,
            'estado' => 'Abierta',
            'observaciones' => 'Turno principal punto de venta Gelawitto.',
            'created_at' => now()->subHours(6),
            'updated_at' => now()->subHours(1),
        ]);

        $vta1 = DB::table('ventas')->insertGetId([
            'folio' => 'VTA-2026-0001',
            'caja_turno_id' => $turnoAbierto,
            'cliente_id' => $clienteMostrador,
            'user_id' => $cajero->id,
            'fecha_venta' => now()->subHours(4),
            'metodo_pago' => 'Efectivo',
            'subtotal' => 1920.00,
            'impuestos' => 0.00,
            'total' => 1920.00,
            'estado' => 'Completada',
            'created_at' => now()->subHours(4),
            'updated_at' => now()->subHours(4),
        ]);

        DB::table('venta_lineas')->insert([
            ['venta_id' => $vta1, 'producto_id' => $prodTrad->id, 'lote_produccion_id' => $loteTrad1, 'cantidad' => 10.000, 'precio_unitario' => 100.00, 'costo_unitario' => 20.77, 'subtotal' => 1000.00, 'created_at' => now()->subHours(4), 'updated_at' => now()->subHours(4)],
            ['venta_id' => $vta1, 'producto_id' => $prodFrut->id, 'lote_produccion_id' => $loteFrut1, 'cantidad' => 8.000, 'precio_unitario' => 115.00, 'costo_unitario' => 21.50, 'subtotal' => 920.00, 'created_at' => now()->subHours(4), 'updated_at' => now()->subHours(4)],
        ]);

        $vta2 = DB::table('ventas')->insertGetId([
            'folio' => 'VTA-2026-0002',
            'caja_turno_id' => $turnoAbierto,
            'cliente_id' => $clienteMirador,
            'user_id' => $cajero->id,
            'fecha_venta' => now()->subHours(3),
            'metodo_pago' => 'Tarjeta',
            'subtotal' => 2580.00,
            'impuestos' => 0.00,
            'total' => 2580.00,
            'estado' => 'Completada',
            'created_at' => now()->subHours(3),
            'updated_at' => now()->subHours(3),
        ]);

        DB::table('venta_lineas')->insert([
            ['venta_id' => $vta2, 'producto_id' => $prodFrut->id, 'lote_produccion_id' => $loteFrut1, 'cantidad' => 12.000, 'precio_unitario' => 115.00, 'costo_unitario' => 21.50, 'subtotal' => 1380.00, 'created_at' => now()->subHours(3), 'updated_at' => now()->subHours(3)],
            ['venta_id' => $vta2, 'producto_id' => $prodLco->id, 'lote_produccion_id' => $loteLco1, 'cantidad' => 10.000, 'precio_unitario' => 120.00, 'costo_unitario' => 21.52, 'subtotal' => 1200.00, 'created_at' => now()->subHours(3), 'updated_at' => now()->subHours(3)],
        ]);

        $vta3 = DB::table('ventas')->insertGetId([
            'folio' => 'VTA-2026-0003',
            'caja_turno_id' => $turnoAbierto,
            'cliente_id' => $clienteUni,
            'user_id' => $cajero->id,
            'fecha_venta' => now()->subHours(2),
            'metodo_pago' => 'Transferencia',
            'subtotal' => 2460.00,
            'impuestos' => 0.00,
            'total' => 2460.00,
            'estado' => 'Completada',
            'created_at' => now()->subHours(2),
            'updated_at' => now()->subHours(2),
        ]);

        DB::table('venta_lineas')->insert([
            ['venta_id' => $vta3, 'producto_id' => $prodTrad->id, 'lote_produccion_id' => $loteTrad1, 'cantidad' => 15.000, 'precio_unitario' => 100.00, 'costo_unitario' => 20.77, 'subtotal' => 1500.00, 'created_at' => now()->subHours(2), 'updated_at' => now()->subHours(2)],
            ['venta_id' => $vta3, 'producto_id' => $prodLco->id, 'lote_produccion_id' => $loteLco1, 'cantidad' => 8.000, 'precio_unitario' => 120.00, 'costo_unitario' => 21.52, 'subtotal' => 960.00, 'created_at' => now()->subHours(2), 'updated_at' => now()->subHours(2)],
        ]);

        $vta4 = DB::table('ventas')->insertGetId([
            'folio' => 'VTA-2026-0004',
            'caja_turno_id' => $turnoAbierto,
            'cliente_id' => $clienteMirador,
            'user_id' => $cajero->id,
            'fecha_venta' => now()->subHour(),
            'metodo_pago' => 'Efectivo',
            'subtotal' => 1075.00,
            'impuestos' => 0.00,
            'total' => 1075.00,
            'estado' => 'Completada',
            'created_at' => now()->subHour(),
            'updated_at' => now()->subHour(),
        ]);

        DB::table('venta_lineas')->insert([
            ['venta_id' => $vta4, 'producto_id' => $prodTrad->id, 'lote_produccion_id' => $loteTrad2, 'cantidad' => 5.000, 'precio_unitario' => 100.00, 'costo_unitario' => 20.77, 'subtotal' => 500.00, 'created_at' => now()->subHour(), 'updated_at' => now()->subHour()],
            ['venta_id' => $vta4, 'producto_id' => $prodFrut->id, 'lote_produccion_id' => $loteFrut1, 'cantidad' => 5.000, 'precio_unitario' => 115.00, 'costo_unitario' => 21.50, 'subtotal' => 575.00, 'created_at' => now()->subHour(), 'updated_at' => now()->subHour()],
        ]);

        // Movimientos Financieros
        DB::table('finanzas_movimientos')->insert([
            [
                'folio' => 'FIN-2026-001',
                'fecha' => now()->subDays(5)->toDateString(),
                'tipo' => 'Egreso',
                'categoria' => 'Compra de Insumos',
                'concepto' => 'Pago factura Agroindustria Arrocera de Sébaco (OC-2026-0001)',
                'referencia' => 'OC-2026-0001',
                'monto' => 2745.00,
                'estado' => 'Pagado',
                'fecha_vencimiento' => null,
                'user_id' => $admin->id,
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(5),
            ],
            [
                'folio' => 'FIN-2026-002',
                'fecha' => now()->subDays(4)->toDateString(),
                'tipo' => 'Egreso',
                'categoria' => 'Compra de Insumos',
                'concepto' => 'Pago factura Distribuidora Alimentaria del Valle (OC-2026-0002)',
                'referencia' => 'OC-2026-0002',
                'monto' => 9750.00,
                'estado' => 'Pagado',
                'fecha_vencimiento' => null,
                'user_id' => $admin->id,
                'created_at' => now()->subDays(4),
                'updated_at' => now()->subDays(4),
            ],
            [
                'folio' => 'FIN-2026-003',
                'fecha' => now()->subDays(2)->toDateString(),
                'tipo' => 'Cuenta por Pagar',
                'categoria' => 'Proveedores',
                'concepto' => 'Factura a crédito 15 días Empaques Térmicos BioPack (OC-2026-0004)',
                'referencia' => 'OC-2026-0004',
                'monto' => 2875.00,
                'estado' => 'Pendiente',
                'fecha_vencimiento' => now()->addDays(13)->toDateString(),
                'user_id' => $admin->id,
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
            ],
            [
                'folio' => 'FIN-2026-004',
                'fecha' => now()->toDateString(),
                'tipo' => 'Ingreso',
                'categoria' => 'Ventas Mostrador y Mayorista',
                'concepto' => 'Recaudación acumulada turno TUR-2026-001 (VTA-0001 a VTA-0004)',
                'referencia' => 'TUR-2026-001',
                'monto' => 8035.00,
                'estado' => 'Pagado',
                'fecha_vencimiento' => null,
                'user_id' => $cajero->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Bitácora de Auditoría
        DB::table('bitacora_auditoria')->insert([
            [
                'user_id' => $admin->id,
                'modulo' => 'Seguridad',
                'accion' => 'Inicialización de Sistema',
                'descripcion' => 'Configuración de parámetros maestros, BOM de 2 niveles y centros de trabajo de Gelawitto ERP.',
                'ip_address' => '127.0.0.1',
                'created_at' => now()->subDays(5),
            ],
            [
                'user_id' => $produccion->id,
                'modulo' => 'Producción',
                'accion' => 'Cierre de Lote',
                'descripcion' => 'Lote LOT-2026-001 finalizado con 7.500 L de Base Gelificada de Arroz (Subensamble Nivel 1).',
                'ip_address' => '127.0.0.1',
                'created_at' => now()->subDays(3),
            ],
            [
                'user_id' => $produccion->id,
                'modulo' => 'Calidad',
                'accion' => 'Inspección de Lote',
                'descripcion' => 'Inspección CC-2026-006 en LOT-2026-006: 1 vaso descartado por Scrap tras alerta térmica IIoT (8.90 °C).',
                'ip_address' => '127.0.0.1',
                'created_at' => now()->subHours(3),
            ],
            [
                'user_id' => $cajero->id,
                'modulo' => 'Ventas y Caja',
                'accion' => 'Apertura de Turno',
                'descripcion' => 'Apertura de turno TUR-2026-001 con fondo inicial de C$ 3,500.00 y despacho FEFO por lote.',
                'ip_address' => '127.0.0.1',
                'created_at' => now()->subHours(6),
            ],
        ]);

        // Contabilidad de Partida Doble
        $contabilidad = app(\App\Services\Finanzas\ContabilidadService::class);
        $contabilidad->registrarAsiento(
            'Contabilidad',
            'APE-2026',
            'Asiento de apertura de operaciones y capital social inicial Gelawitto',
            [
                ['codigo' => '1101', 'descripcion' => 'Fondo inicial en caja y bancos', 'debe' => 50000.00, 'haber' => 0],
                ['codigo' => '1104', 'descripcion' => 'Inventario inicial de materia prima y empaques', 'debe' => 16570.00, 'haber' => 0],
                ['codigo' => '1105', 'descripcion' => 'Inventario inicial de producto y semielaborado', 'debe' => 3704.86, 'haber' => 0],
                ['codigo' => '3101', 'descripcion' => 'Aportación patrimonial de socios', 'debe' => 0, 'haber' => 70274.86],
            ],
            $admin->id
        );
        $contabilidad->asentarCompraRecibida('OC-2026-0001', 2745.00, 0.00, 2745.00, 'Pagado', $admin->id);
        $contabilidad->asentarCompraRecibida('OC-2026-0002', 9750.00, 0.00, 9750.00, 'Pagado', $admin->id);
        $contabilidad->asentarLoteProduccion('LOT-2026-001', 1950.00, 'Base Gelificada de Arroz (Subensamble)', $produccion->id);
        $contabilidad->asentarLoteProduccion('LOT-2026-002', 623.10, 'Gelawitto Tradicional de Arroz', $produccion->id);
        $contabilidad->asentarLoteProduccion('LOT-2026-003', 752.50, 'Gelawitto con Frutas Variadas', $produccion->id);
        $contabilidad->asentarVenta('VTA-2026-0001', 1920.00, 0.00, 1920.00, 379.70, 'Efectivo', $cajero->id);
        $contabilidad->asentarVenta('VTA-2026-0002', 2580.00, 0.00, 2580.00, 473.20, 'Tarjeta', $cajero->id);

        // Consumos, saldos por lote, Kardex y stock coherentes con la BOM y los movimientos sembrados
        $this->call(ConsistenciaInventarioSeeder::class);
    }
}
