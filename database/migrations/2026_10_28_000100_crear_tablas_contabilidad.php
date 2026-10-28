<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cuentas_contables', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();
            $table->string('nombre', 120);
            $table->string('tipo', 30);
            $table->string('naturaleza', 20);
            $table->timestamps();
        });

        Schema::create('asientos_contables', function (Blueprint $table) {
            $table->id();
            $table->string('numero_asiento', 30)->unique();
            $table->date('fecha');
            $table->string('modulo_origen', 40);
            $table->string('referencia', 50)->nullable();
            $table->string('concepto', 200);
            $table->decimal('total_debe', 12, 2);
            $table->decimal('total_haber', 12, 2);
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('partidas_asiento', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asiento_contable_id')->constrained('asientos_contables')->cascadeOnDelete();
            $table->foreignId('cuenta_contable_id')->constrained('cuentas_contables')->restrictOnDelete();
            $table->string('descripcion', 180)->nullable();
            $table->decimal('debe', 12, 2)->default(0);
            $table->decimal('haber', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partidas_asiento');
        Schema::dropIfExists('asientos_contables');
        Schema::dropIfExists('cuentas_contables');
    }
};
