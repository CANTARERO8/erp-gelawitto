<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lotes_produccion', function (Blueprint $table) {
            $table->string('estado_calidad', 30)->default('Pendiente')->after('estado');
        });

        Schema::create('controles_calidad', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_control', 30)->unique();
            $table->foreignId('lote_id')->constrained('lotes_produccion')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->timestamp('fecha_inspeccion')->useCurrent();
            $table->decimal('unidades_inspeccionadas', 12, 3);
            $table->decimal('unidades_no_conformes', 12, 3)->default(0);
            $table->text('defecto')->nullable();
            $table->string('disposicion', 20);
            $table->decimal('costo_perdido', 12, 2)->default(0);
            $table->text('notas')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('controles_calidad');

        Schema::table('lotes_produccion', function (Blueprint $table) {
            $table->dropColumn('estado_calidad');
        });
    }
};
