<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lecturas_temperatura', function (Blueprint $table) {
            $table->id();
            $table->string('dispositivo', 40);
            $table->decimal('temperatura', 5, 2);
            $table->boolean('fuera_de_rango')->default(false);
            $table->string('origen', 20);
            $table->timestamp('fecha_lectura');
            $table->timestamps();

            $table->index('fecha_lectura');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lecturas_temperatura');
    }
};
