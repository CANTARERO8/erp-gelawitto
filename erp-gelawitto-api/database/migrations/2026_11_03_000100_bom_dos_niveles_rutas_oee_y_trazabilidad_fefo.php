<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->string('tipo', 25)->default('terminado')->after('presentacion');
        });

        Schema::table('receta_insumos', function (Blueprint $table) {
            $table->unsignedBigInteger('insumo_id')->nullable()->change();
            $table->foreignId('subproducto_id')->nullable()->after('insumo_id')->constrained('productos')->nullOnDelete();
            $table->integer('nivel')->default(1)->after('subproducto_id');
        });

        Schema::create('centros_trabajo', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();
            $table->string('nombre', 120);
            $table->string('estacion', 120)->nullable();
            $table->decimal('capacidad_unidades_hora', 10, 2)->default(60);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('rutas_operacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('receta_id')->constrained('recetas')->cascadeOnDelete();
            $table->foreignId('centro_trabajo_id')->constrained('centros_trabajo')->restrictOnDelete();
            $table->integer('secuencia')->default(1);
            $table->string('operacion', 140);
            $table->decimal('tiempo_preparacion_min', 8, 2)->default(0);
            $table->decimal('tiempo_ciclo_min_por_unidad', 8, 3)->default(0);
            $table->timestamps();
        });

        Schema::table('orden_compra_lineas', function (Blueprint $table) {
            $table->string('lote_proveedor', 50)->nullable()->after('subtotal');
            $table->date('fecha_vencimiento')->nullable()->after('lote_proveedor');
        });

        Schema::create('lotes_insumo', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_lote_insumo', 40)->unique();
            $table->foreignId('insumo_id')->constrained('insumos')->cascadeOnDelete();
            $table->foreignId('orden_compra_linea_id')->nullable()->constrained('orden_compra_lineas')->nullOnDelete();
            $table->string('lote_proveedor', 50)->nullable();
            $table->decimal('cantidad_inicial', 12, 3);
            $table->decimal('cantidad_disponible', 12, 3);
            $table->date('fecha_ingreso');
            $table->date('fecha_vencimiento')->nullable();
            $table->timestamps();
        });

        Schema::table('lotes_produccion', function (Blueprint $table) {
            $table->decimal('cantidad_disponible', 12, 3)->default(0)->after('cantidad_obtenida');
            $table->date('fecha_vencimiento')->nullable()->after('fecha_produccion');
            $table->timestamp('inicio_real')->nullable()->after('fecha_vencimiento');
            $table->timestamp('fin_real')->nullable()->after('inicio_real');
            $table->decimal('tiempo_paro_min', 8, 2)->default(0)->after('fin_real');
        });

        Schema::create('consumos_lote', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lote_produccion_id')->constrained('lotes_produccion')->cascadeOnDelete();
            $table->foreignId('lote_insumo_id')->nullable()->constrained('lotes_insumo')->nullOnDelete();
            $table->foreignId('subproducto_lote_id')->nullable()->constrained('lotes_produccion')->nullOnDelete();
            $table->decimal('cantidad', 12, 3);
            $table->timestamps();
        });

        Schema::table('venta_lineas', function (Blueprint $table) {
            $table->foreignId('lote_produccion_id')->nullable()->after('producto_id')->constrained('lotes_produccion')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('venta_lineas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('lote_produccion_id');
        });

        Schema::dropIfExists('consumos_lote');

        Schema::table('lotes_produccion', function (Blueprint $table) {
            $table->dropColumn(['cantidad_disponible', 'fecha_vencimiento', 'inicio_real', 'fin_real', 'tiempo_paro_min']);
        });

        Schema::dropIfExists('lotes_insumo');

        Schema::table('orden_compra_lineas', function (Blueprint $table) {
            $table->dropColumn(['lote_proveedor', 'fecha_vencimiento']);
        });

        Schema::dropIfExists('rutas_operacion');
        Schema::dropIfExists('centros_trabajo');

        Schema::table('receta_insumos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('subproducto_id');
            $table->dropColumn('nivel');
        });

        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn('tipo');
        });
    }
};
