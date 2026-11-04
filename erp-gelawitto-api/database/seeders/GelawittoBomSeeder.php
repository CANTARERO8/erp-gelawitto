<?php

namespace Database\Seeders;

use App\Models\Catalogos\Insumo;
use App\Models\Catalogos\Producto;
use App\Models\Catalogos\UnidadMedida;
use App\Models\Produccion\CentroTrabajo;
use App\Models\Produccion\Receta;
use App\Models\Produccion\RutaOperacion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Estructura de Lista de Materiales (BOM) de 2 niveles, Centros de Trabajo y Rutas
 * de Operación para Gelawitto — Postre Funcional de Gelatina a Base de Arroz.
 *
 * Nivel 0: Productos Terminados (GLW-TRA-100, GLW-FRU-100, GLW-LCO-100)
 * Nivel 1: Subensamble Base Gelificada de Arroz (SUB-GEL-BASE) + Fruta/Leche/Empaques
 * Nivel 2: Materias primas de la Base Gelificada (Harina de arroz, Gelatina, Agua, Azúcar)
 */
class GelawittoBomSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $kg = UnidadMedida::where('abreviatura', 'kg')->firstOrFail();
            $litro = UnidadMedida::where('abreviatura', 'L')->firstOrFail();
            $unidad = UnidadMedida::where('abreviatura', 'ud')->firstOrFail();

            // 1. Insumos maestros de Gelawitto (Nivel 2 y Nivel 1)
            $arroz = $this->insumo('INS-001', 'Harina de arroz blanco seleccionado', 'Granos y Cereales', $kg, 45.00, 25.000, 10.000);
            $gelatina = $this->insumo('INS-002', 'Gelatina en polvo Great Value 40 g', 'Gelificantes', $kg, 1125.00, 4.500, 2.000);
            $agua = $this->insumo('INS-003', 'Agua purificada tratada (ósmosis inversa)', 'Agua', $litro, 1.00, 120.000, 40.000);
            $azucar = $this->insumo('INS-004', 'Azúcar blanca cristalina refinada', 'Endulzantes', $kg, 40.00, 30.000, 12.000);
            $frutas = $this->insumo('INS-005', 'Mix de frutas naturales en cubos (fresa, durazno y kiwi)', 'Frutas', $kg, 150.00, 6.500, 10.000);
            $lecheCond = $this->insumo('INS-006', 'Leche condensada azucarada pasteurizada', 'Lácteos y Derivados', $kg, 135.00, 8.000, 10.000);
            $vainilla = $this->insumo('INS-007', 'Extracto natural de vainilla y canela', 'Saborizantes', $litro, 320.00, 5.000, 2.000);
            $vaso = $this->insumo('INS-008', 'Vaso reciclable biorientado 100 mL con tapa domo', 'Empaques', $unidad, 8.00, 180.000, 60.000);
            $etiqueta = $this->insumo('INS-009', 'Etiqueta autoadhesiva impermeable Gelawitto', 'Empaques', $unidad, 1.50, 200.000, 60.000);
            $bandeja = $this->insumo('INS-010', 'Bandeja termoformada empaque secundario x5 vasos', 'Empaques', $unidad, 6.00, 50.000, 15.000);

            // 2. Productos Terminados (Nivel 0) y Subensamble Semielaborado (Nivel 1)
            $tradicional = $this->producto(
                'GLW-TRA-100',
                'Gelawitto Tradicional de Arroz',
                'Arroz con Vainilla y Canela',
                'Vaso 100 mL con tapa',
                'terminado',
                100.00,
                20.77,
                25.000,
                10.000
            );

            $frutal = $this->producto(
                'GLW-FRU-100',
                'Gelawitto con Frutas Variadas',
                'Frutas Tropicales en Base de Arroz',
                'Vaso 100 mL con tapa',
                'terminado',
                115.00,
                21.50,
                19.000,
                10.000
            );

            $lecheCondProd = $this->producto(
                'GLW-LCO-100',
                'Gelawitto Tres Leches y Arroz',
                'Leche Condensada y Arroz',
                'Vaso 100 mL con tapa',
                'terminado',
                120.00,
                21.52,
                18.000,
                8.000
            );

            $subBase = $this->producto(
                'SUB-GEL-BASE',
                'Base Gelificada de Arroz (Subensamble)',
                'Base Neutra de Arroz',
                'Litro de base homogénea',
                'semielaborado',
                0.00,
                97.50,
                24.500,
                5.000
            );

            // 3. Centros de Trabajo de la Planta
            $ctCoccion = CentroTrabajo::firstOrCreate(
                ['codigo' => 'CT-01'],
                [
                    'nombre' => 'Estación de Cocción e Hidratación Coloidal',
                    'estacion' => 'Área Térmica (Marmita Inoxidable)',
                    'capacidad_unidades_hora' => 40.00,
                    'activo' => true,
                ]
            );

            $ctDosificado = CentroTrabajo::firstOrCreate(
                ['codigo' => 'CT-02'],
                [
                    'nombre' => 'Mesa de Mezclado, Dosificación y Envasado',
                    'estacion' => 'Área de Proceso Inocuo',
                    'capacidad_unidades_hora' => 120.00,
                    'activo' => true,
                ]
            );

            $ctRefrigeracion = CentroTrabajo::firstOrCreate(
                ['codigo' => 'CT-03'],
                [
                    'nombre' => 'Cámara de Refrigeración y Gelificación Controlada (REF-01)',
                    'estacion' => 'Cuarto Frío IIoT (2.0 a 6.0 °C)',
                    'capacidad_unidades_hora' => 200.00,
                    'activo' => true,
                ]
            );

            $ctCalidad = CentroTrabajo::firstOrCreate(
                ['codigo' => 'CT-04'],
                [
                    'nombre' => 'Estación de Inspección de Calidad y Etiquetado',
                    'estacion' => 'Mesa de Liberación y Empaque Secundario',
                    'capacidad_unidades_hora' => 150.00,
                    'activo' => true,
                ]
            );

            // 4. Receta Nivel 0 - Producto 1: Gelawitto Tradicional (5 vasos x 100 mL = 0.500 L)
            $recTradicional = $this->receta(
                $tradicional,
                'BOM 2 Niveles: Gelawitto Tradicional de Arroz (Lote 5 vasos)',
                5.000,
                43,
                $this->instruccionesTradicional(),
                [
                    ['subproducto_id' => $subBase->id, 'insumo_id' => null, 'nivel' => 1, 'cantidad' => 0.500, 'merma' => 0.00],
                    ['subproducto_id' => null, 'insumo_id' => $vainilla->id, 'nivel' => 1, 'cantidad' => 0.005, 'merma' => 0.00],
                    ['subproducto_id' => null, 'insumo_id' => $vaso->id, 'nivel' => 1, 'cantidad' => 5.000, 'merma' => 0.00],
                    ['subproducto_id' => null, 'insumo_id' => $etiqueta->id, 'nivel' => 1, 'cantidad' => 5.000, 'merma' => 0.00],
                    ['subproducto_id' => null, 'insumo_id' => $bandeja->id, 'nivel' => 1, 'cantidad' => 1.000, 'merma' => 0.00],
                ]
            );

            $this->rutas($recTradicional, [
                [$ctDosificado->id, 1, 'Aromatizado con vainilla/canela, dosificación de 100 mL por vaso y sellado', 5.00, 1.200],
                [$ctRefrigeracion->id, 2, 'Gelificación controlada en cámara fría REF-01 (2.0 a 6.0 °C)', 5.00, 4.000],
                [$ctCalidad->id, 3, 'Inspección organoléptica, etiquetado impermeable y empaque en bandeja x5', 3.00, 0.800],
            ]);

            // 5. Receta Nivel 0 - Producto 2: Gelawitto con Frutas Variadas (5 vasos = 0.400 L base + 0.100 kg fruta)
            $recFrutal = $this->receta(
                $frutal,
                'BOM 2 Niveles: Gelawitto con Frutas Variadas (Lote 5 vasos)',
                5.000,
                48,
                $this->instruccionesFrutal(),
                [
                    ['subproducto_id' => $subBase->id, 'insumo_id' => null, 'nivel' => 1, 'cantidad' => 0.400, 'merma' => 0.00],
                    ['subproducto_id' => null, 'insumo_id' => $frutas->id, 'nivel' => 1, 'cantidad' => 0.100, 'merma' => 2.00],
                    ['subproducto_id' => null, 'insumo_id' => $vaso->id, 'nivel' => 1, 'cantidad' => 5.000, 'merma' => 0.00],
                    ['subproducto_id' => null, 'insumo_id' => $etiqueta->id, 'nivel' => 1, 'cantidad' => 5.000, 'merma' => 0.00],
                    ['subproducto_id' => null, 'insumo_id' => $bandeja->id, 'nivel' => 1, 'cantidad' => 1.000, 'merma' => 0.00],
                ]
            );

            $this->rutas($recFrutal, [
                [$ctDosificado->id, 1, 'Dosificación de 20 g de fruta en cubos + 80 mL de base gelificada de arroz por vaso', 8.00, 1.600],
                [$ctRefrigeracion->id, 2, 'Gelificación controlada en cámara fría REF-01 (2.0 a 6.0 °C)', 5.00, 4.000],
                [$ctCalidad->id, 3, 'Inspección de suspensión de fruta, etiquetado y empaque en bandeja x5', 3.00, 0.800],
            ]);

            // 6. Receta Nivel 0 - Producto 3: Gelawitto Tres Leches y Arroz (5 vasos = 0.400 L base + 0.100 kg leche cond.)
            $recLecheCond = $this->receta(
                $lecheCondProd,
                'BOM 2 Niveles: Gelawitto Tres Leches y Arroz (Lote 5 vasos)',
                5.000,
                45,
                $this->instruccionesLecheCondensada(),
                [
                    ['subproducto_id' => $subBase->id, 'insumo_id' => null, 'nivel' => 1, 'cantidad' => 0.400, 'merma' => 0.00],
                    ['subproducto_id' => null, 'insumo_id' => $lecheCond->id, 'nivel' => 1, 'cantidad' => 0.100, 'merma' => 0.00],
                    ['subproducto_id' => null, 'insumo_id' => $vainilla->id, 'nivel' => 1, 'cantidad' => 0.005, 'merma' => 0.00],
                    ['subproducto_id' => null, 'insumo_id' => $vaso->id, 'nivel' => 1, 'cantidad' => 5.000, 'merma' => 0.00],
                    ['subproducto_id' => null, 'insumo_id' => $etiqueta->id, 'nivel' => 1, 'cantidad' => 5.000, 'merma' => 0.00],
                    ['subproducto_id' => null, 'insumo_id' => $bandeja->id, 'nivel' => 1, 'cantidad' => 1.000, 'merma' => 0.00],
                ]
            );

            $this->rutas($recLecheCond, [
                [$ctDosificado->id, 1, 'Incorporación de 20 g de leche condensada + 80 mL de base de arroz por vaso', 6.00, 1.400],
                [$ctRefrigeracion->id, 2, 'Gelificación controlada en cámara fría REF-01 (2.0 a 6.0 °C)', 5.00, 4.000],
                [$ctCalidad->id, 3, 'Inspección de fases, etiquetado impermeable y empaque en bandeja x5', 3.00, 0.800],
            ]);

            // 7. Receta Nivel 1 -> Nivel 2: Subensamble Base Gelificada de Arroz (Rendimiento: 1.000 Litro)
            $recSubBase = $this->receta(
                $subBase,
                'Subensamble Nivel 1: Base Gelificada de Arroz (Batch 1.000 L)',
                1.000,
                35,
                $this->instruccionesSubensambleBase(),
                [
                    ['subproducto_id' => null, 'insumo_id' => $arroz->id, 'nivel' => 2, 'cantidad' => 0.100, 'merma' => 1.00],
                    ['subproducto_id' => null, 'insumo_id' => $gelatina->id, 'nivel' => 2, 'cantidad' => 0.080, 'merma' => 0.00],
                    ['subproducto_id' => null, 'insumo_id' => $agua->id, 'nivel' => 2, 'cantidad' => 1.000, 'merma' => 0.00],
                    ['subproducto_id' => null, 'insumo_id' => $azucar->id, 'nivel' => 2, 'cantidad' => 0.050, 'merma' => 0.00],
                ]
            );

            $this->rutas($recSubBase, [
                [$ctCoccion->id, 1, 'Cocción coloidal de harina de arroz e hidratación de gelatina en 500 mL de agua a 85 °C', 10.00, 15.000],
                [$ctDosificado->id, 2, 'Choque térmico con 500 mL de agua fría, endulzado y homogeneizado de 1.000 L de base', 5.00, 5.000],
            ]);
        });
    }

    private function insumo(
        string $codigo,
        string $nombre,
        string $categoria,
        UnidadMedida $unidad,
        float $costoPromedio,
        float $stockActual,
        float $stockMinimo
    ): Insumo {
        return Insumo::updateOrCreate(
            ['codigo' => $codigo],
            [
                'nombre' => $nombre,
                'categoria' => $categoria,
                'unidad_id' => $unidad->id,
                'stock_actual' => $stockActual,
                'stock_minimo' => $stockMinimo,
                'costo_promedio' => $costoPromedio,
                'activo' => true,
            ]
        );
    }

    private function producto(
        string $sku,
        string $nombre,
        string $sabor,
        string $presentacion,
        string $tipo,
        float $precioVenta,
        float $costoUnitario,
        float $stockActual,
        float $stockMinimo
    ): Producto {
        return Producto::updateOrCreate(
            ['sku' => $sku],
            [
                'nombre' => $nombre,
                'sabor' => $sabor,
                'presentacion' => $presentacion,
                'tipo' => $tipo,
                'precio_venta' => $precioVenta,
                'costo_unitario' => $costoUnitario,
                'stock_actual' => $stockActual,
                'stock_minimo' => $stockMinimo,
                'activo' => true,
            ]
        );
    }

    private function receta(
        Producto $producto,
        string $nombre,
        float $rendimiento,
        int $tiempoMin,
        string $instrucciones,
        array $componentes
    ): Receta {
        $receta = Receta::updateOrCreate(
            ['producto_id' => $producto->id],
            [
                'nombre' => $nombre,
                'rendimiento_unidades' => $rendimiento,
                'tiempo_preparacion_min' => $tiempoMin,
                'instrucciones' => $instrucciones,
                'activa' => true,
            ]
        );

        if ($receta->ingredientes()->doesntExist()) {
            foreach ($componentes as $comp) {
                $receta->ingredientes()->create([
                    'insumo_id' => $comp['insumo_id'],
                    'subproducto_id' => $comp['subproducto_id'],
                    'nivel' => $comp['nivel'],
                    'cantidad_requerida' => $comp['cantidad'],
                    'merma_estimada_porcentaje' => $comp['merma'],
                ]);
            }
        }

        return $receta;
    }

    private function rutas(Receta $receta, array $definiciones): void
    {
        if ($receta->rutas()->exists()) {
            return;
        }

        foreach ($definiciones as $def) {
            RutaOperacion::create([
                'receta_id' => $receta->id,
                'centro_trabajo_id' => $def[0],
                'secuencia' => $def[1],
                'operacion' => $def[2],
                'tiempo_preparacion_min' => $def[3],
                'tiempo_ciclo_min_por_unidad' => $def[4],
            ]);
        }
    }

    private function instruccionesSubensambleBase(): string
    {
        return implode("\n", [
            '1. Pesar 100 g de harina de arroz blanco seleccionado, 80 g de gelatina en polvo y 50 g de azúcar.',
            '2. Calentar 500 mL de agua purificada a 85 °C en marmita de acero inoxidable (CT-01).',
            '3. Incorporar la harina de arroz bajo agitación continua durante 12 minutos hasta gelatinización del almidón.',
            '4. Disolver los 80 g de gelatina y 50 g de azúcar hasta obtener una solución coloidal sin grumos.',
            '5. Agregar 500 mL de agua purificada fría (choque térmico en CT-02) completando 1.000 L exacto de Base Gelificada de Arroz.',
        ]);
    }

    private function instruccionesTradicional(): string
    {
        return implode("\n", [
            '1. Extraer 0.500 L del subensamble Base Gelificada de Arroz (SUB-GEL-BASE) e incorporar 5 mL de extracto de vainilla y canela.',
            '2. Dosificar exactamente 100 mL en cada uno de los 5 vasos reciclables y colocar tapa domo (CT-02).',
            '3. Ingresar a cámara de refrigeración REF-01 (2.0 a 6.0 °C) hasta gelificación completa (CT-03).',
            '4. Inspeccionar firmeza del gel, colocar etiquetas impermeables y empacar en bandeja secundaria x5 (CT-04).',
        ]);
    }

    private function instruccionesFrutal(): string
    {
        return implode("\n", [
            '1. Preparar y sanitizar 100 g de mix de frutas naturales en cubos (20 g por vaso de 100 mL).',
            '2. Dosificar 20 g de fruta en el fondo de cada uno de los 5 vasos reciclables (CT-02).',
            '3. Completar cada vaso con 80 mL de Base Gelificada de Arroz (0.400 L totales por lote de 5 vasos) y colocar tapa domo.',
            '4. Refrigerar en cámara fría REF-01 entre 2.0 y 6.0 °C hasta solidificación (CT-03).',
            '5. Inspeccionar calidad, etiquetar y disponer en bandeja termoformada x5 (CT-04).',
        ]);
    }

    private function instruccionesLecheCondensada(): string
    {
        return implode("\n", [
            '1. Emulsionar 0.400 L de Base Gelificada de Arroz tibia con 100 g de leche condensada pasteurizada (20 g/vaso) y 5 mL de vainilla.',
            '2. Dosificar 100 mL de la emulsión homogénea en cada uno de los 5 vasos reciclables con tapa domo (CT-02).',
            '3. Gelificar en cámara de refrigeración REF-01 (2.0 a 6.0 °C) (CT-03).',
            '4. Inspeccionar ausencia de sinéresis, etiquetar y empacar en bandeja secundaria x5 (CT-04).',
        ]);
    }
}
