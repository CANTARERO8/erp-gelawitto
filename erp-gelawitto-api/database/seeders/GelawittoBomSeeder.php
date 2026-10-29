<?php

namespace Database\Seeders;

use App\Models\Catalogos\Insumo;
use App\Models\Catalogos\Producto;
use App\Models\Catalogos\UnidadMedida;
use App\Models\Produccion\Receta;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * BOM inicial de Gelawitto según el Informe básico del proyecto.
 *
 * Rinde 5 vasos de ~100 mL por lote. Los datos que el informe no trae se
 * manejan así: la cantidad de fruta queda sin línea en la receta frutal, el
 * precio de venta es provisional (C$ 100), los costos son estimaciones y el
 * stock y el tiempo de preparación quedan en 0 o en el valor por defecto.
 *
 * Es idempotente: no duplica registros ni sobrescribe lo que el equipo edite después.
 * Los valores de creación se aplican solo a registros nuevos.
 */
class GelawittoBomSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $kg = UnidadMedida::where('abreviatura', 'kg')->firstOrFail();
            $litro = UnidadMedida::where('abreviatura', 'L')->firstOrFail();
            $unidad = UnidadMedida::where('abreviatura', 'ud')->firstOrFail();

            // Costos estimados en córdobas (C$), por unidad de medida del insumo. Por validar con proveedores.
            $gelatina = $this->insumo('INS-009', 'Gelatina en polvo Great Value 40 g', 'Gelatinas', $kg, 1125.00);
            $agua = $this->insumo('INS-010', 'Agua potable (250 mL caliente + 250 mL fría)', 'Agua', $litro, 1.00);
            $frutas = $this->insumo('INS-011', 'Frutas variadas (cantidad pendiente de estandarizar)', 'Frutas', $kg, 150.00);
            $vaso = $this->insumo('INS-012', 'Vaso reciclable 100 mL con tapa', 'Empaques', $unidad, 8.00);

            // Costo por vaso de la receta tradicional: (gelatina + agua + 5 vasos) / 5 = C$ 17.10. La fruta no está incluida.
            $tradicional = $this->producto('GLW-TRA-100', 'Gelawitto tradicional', 'Fresa y limón', 'Vaso 100 mL con tapa', 17.10);
            $frutal = $this->producto('GLW-FRU-100', 'Gelawitto con frutas variadas', 'Frutas variadas', 'Vaso 100 mL con tapa', 17.10);

            $this->receta($tradicional, 'Receta base Gelawitto tradicional (lote de 5)', $this->instruccionesTradicional(), [
                [$gelatina, 0.040],
                [$agua, 0.500],
                [$vaso, 5],
            ]);

            // La cantidad de fruta aún no está definida, así que no se agrega línea de frutas.
            $this->receta($frutal, 'Receta base Gelawitto con frutas (lote de 5)', $this->instruccionesFrutal(), [
                [$gelatina, 0.040],
                [$agua, 0.500],
                [$vaso, 5],
            ]);
        });
    }

    private function insumo(string $codigo, string $nombre, string $categoria, UnidadMedida $unidad, float $costoPromedio): Insumo
    {
        return Insumo::firstOrCreate(
            ['codigo' => $codigo],
            [
                'nombre' => $nombre,
                'categoria' => $categoria,
                'unidad_id' => $unidad->id,
                'costo_promedio' => $costoPromedio,
                'activo' => true,
            ]
        );
    }

    private function producto(string $sku, string $nombre, string $sabor, string $presentacion, float $costoUnitario): Producto
    {
        return Producto::firstOrCreate(
            ['sku' => $sku],
            [
                'nombre' => $nombre,
                'sabor' => $sabor,
                'presentacion' => $presentacion,
                'precio_venta' => 100.00,
                'costo_unitario' => $costoUnitario,
                'activo' => true,
            ]
        );
    }

    /**
     * @param  array<int, array{0: Insumo, 1: float|int}>  $ingredientes
     */
    private function receta(Producto $producto, string $nombre, string $instrucciones, array $ingredientes): void
    {
        $receta = Receta::firstOrCreate(
            ['producto_id' => $producto->id],
            [
                'nombre' => $nombre,
                'rendimiento_unidades' => 5,
                'instrucciones' => $instrucciones,
                'activa' => true,
            ]
        );

        if ($receta->ingredientes()->doesntExist()) {
            $receta->ingredientes()->createMany(array_map(fn (array $linea) => [
                'insumo_id' => $linea[0]->id,
                'cantidad_requerida' => $linea[1],
                'merma_estimada_porcentaje' => 0,
            ], $ingredientes));
        }
    }

    private function instruccionesTradicional(): string
    {
        return implode("\n", [
            '1. Verificar disponibilidad de materiales.',
            '2. Medir 250 mL de agua caliente y 250 mL de agua fría.',
            '3. Contar 5 vasos reciclables con tapa.',
            '4. Disolver 40 g de gelatina en el agua caliente.',
            '5. Mezclar hasta eliminar grumos.',
            '6. Agregar el agua fría y homogeneizar.',
            '7. Dosificar la mezcla en los vasos.',
            '8. Colocar las tapas.',
            '9. Refrigerar hasta solidificar (tiempo pendiente de validar).',
            '10. Inspeccionar la calidad del producto.',
            '11. Etiquetar, registrar el lote terminado y almacenar en refrigeración.',
        ]);
    }

    private function instruccionesFrutal(): string
    {
        return implode("\n", [
            '1. Verificar disponibilidad de materiales.',
            '2. Medir 250 mL de agua caliente y 250 mL de agua fría.',
            '3. Contar 5 vasos reciclables con tapa.',
            '4. Disolver 40 g de gelatina en el agua caliente.',
            '5. Mezclar hasta eliminar grumos.',
            '6. Agregar el agua fría y homogeneizar la mezcla.',
            '7. Preparar las frutas variadas (cantidad por vaso pendiente de estandarizar).',
            '8. Dosificar la fruta en cada vaso.',
            '9. Dosificar la mezcla en los vasos hasta completar aproximadamente 100 mL.',
            '10. Colocar las tapas.',
            '11. Refrigerar hasta solidificar (tiempo pendiente de validar).',
            '12. Inspeccionar la calidad del producto.',
            '13. Etiquetar, registrar el lote terminado y almacenar en refrigeración.',
        ]);
    }
}
