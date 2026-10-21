<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Una orden de producción programada aún no tiene resultados: la cantidad
     * obtenida y los costos se conocen solo al ejecutarla.
     */
    public function up(): void
    {
        Schema::table('lotes_produccion', function (Blueprint $table) {
            $table->decimal('cantidad_obtenida', 12, 3)->nullable()->change();
            $table->decimal('costo_total_lote', 12, 2)->nullable()->change();
            $table->decimal('costo_unitario_resultante', 12, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        // Las órdenes programadas no tienen resultados, así que se eliminan antes de volver a exigirlos.
        \Illuminate\Support\Facades\DB::table('lotes_produccion')->where('estado', 'Programada')->delete();

        Schema::table('lotes_produccion', function (Blueprint $table) {
            $table->decimal('cantidad_obtenida', 12, 3)->nullable(false)->change();
            $table->decimal('costo_total_lote', 12, 2)->nullable(false)->change();
            $table->decimal('costo_unitario_resultante', 12, 2)->nullable(false)->change();
        });
    }
};
