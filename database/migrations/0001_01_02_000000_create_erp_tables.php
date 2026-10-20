<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bitacora_auditoria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('modulo', 60);
            $table->string('accion', 80);
            $table->text('descripcion');
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('unidades_medida', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 60);
            $table->string('abreviatura', 15)->unique();
            $table->timestamps();
        });

        Schema::create('proveedores', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 120);
            $table->string('ruc_nit', 40)->unique();
            $table->string('contacto', 100);
            $table->string('telefono', 40);
            $table->string('correo', 120)->nullable();
            $table->string('direccion', 200)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 120);
            $table->string('identificacion', 40)->nullable();
            $table->string('telefono', 40)->nullable();
            $table->string('correo', 120)->nullable();
            $table->string('tipo', 40)->default('Mostrador');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('insumos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 30)->unique();
            $table->string('nombre', 120);
            $table->string('categoria', 60);
            $table->foreignId('unidad_id')->constrained('unidades_medida');
            $table->decimal('stock_actual', 12, 3)->default(0);
            $table->decimal('stock_minimo', 12, 3)->default(0);
            $table->decimal('costo_promedio', 12, 2)->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->string('sku', 30)->unique();
            $table->string('nombre', 120);
            $table->string('sabor', 60);
            $table->string('presentacion', 60);
            $table->decimal('precio_venta', 12, 2);
            $table->decimal('costo_unitario', 12, 2)->default(0);
            $table->decimal('stock_actual', 12, 3)->default(0);
            $table->decimal('stock_minimo', 12, 3)->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('recetas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();
            $table->string('nombre', 140);
            $table->decimal('rendimiento_unidades', 12, 3);
            $table->integer('tiempo_preparacion_min')->default(45);
            $table->text('instrucciones')->nullable();
            $table->boolean('activa')->default(true);
            $table->timestamps();
        });

        Schema::create('receta_insumos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('receta_id')->constrained('recetas')->cascadeOnDelete();
            $table->foreignId('insumo_id')->constrained('insumos')->restrictOnDelete();
            $table->decimal('cantidad_requerida', 12, 3);
            $table->decimal('merma_estimada_porcentaje', 5, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('ordenes_compra', function (Blueprint $table) {
            $table->id();
            $table->string('folio', 30)->unique();
            $table->foreignId('proveedor_id')->constrained('proveedores');
            $table->foreignId('user_id')->constrained('users');
            $table->date('fecha_emision');
            $table->date('fecha_recepcion')->nullable();
            $table->string('estado', 30)->default('Recibida');
            $table->decimal('subtotal', 12, 2);
            $table->decimal('impuestos', 12, 2)->default(0);
            $table->decimal('total', 12, 2);
            $table->text('notas')->nullable();
            $table->timestamps();
        });

        Schema::create('orden_compra_lineas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orden_compra_id')->constrained('ordenes_compra')->cascadeOnDelete();
            $table->foreignId('insumo_id')->constrained('insumos');
            $table->decimal('cantidad', 12, 3);
            $table->decimal('costo_unitario', 12, 2);
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();
        });

        Schema::create('lotes_produccion', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_lote', 30)->unique();
            $table->foreignId('receta_id')->constrained('recetas');
            $table->foreignId('producto_id')->constrained('productos');
            $table->foreignId('user_id')->constrained('users');
            $table->date('fecha_produccion');
            $table->decimal('cantidad_programada', 12, 3);
            $table->decimal('cantidad_obtenida', 12, 3);
            $table->decimal('merma', 12, 3)->default(0);
            $table->decimal('costo_total_lote', 12, 2);
            $table->decimal('costo_unitario_resultante', 12, 2);
            $table->string('estado', 30)->default('Finalizado');
            $table->text('notas')->nullable();
            $table->timestamps();
        });

        Schema::create('movimientos_inventario', function (Blueprint $table) {
            $table->id();
            $table->timestamp('fecha')->useCurrent();
            $table->string('tipo_item', 20);
            $table->unsignedBigInteger('item_id');
            $table->string('nombre_item', 140);
            $table->string('tipo_movimiento', 50);
            $table->string('referencia', 50);
            $table->decimal('cantidad', 12, 3);
            $table->decimal('stock_resultante', 12, 3);
            $table->decimal('costo_unitario', 12, 2)->default(0);
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('cajas_turnos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_turno', 30)->unique();
            $table->foreignId('user_id')->constrained('users');
            $table->timestamp('fecha_apertura');
            $table->timestamp('fecha_cierre')->nullable();
            $table->decimal('monto_inicial', 12, 2);
            $table->decimal('ventas_efectivo', 12, 2)->default(0);
            $table->decimal('ventas_tarjeta', 12, 2)->default(0);
            $table->decimal('ventas_transferencia', 12, 2)->default(0);
            $table->decimal('monto_esperado', 12, 2)->default(0);
            $table->decimal('monto_declarado', 12, 2)->nullable();
            $table->decimal('diferencia', 12, 2)->default(0);
            $table->string('estado', 20)->default('Abierta');
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });

        Schema::create('ventas', function (Blueprint $table) {
            $table->id();
            $table->string('folio', 30)->unique();
            $table->foreignId('caja_turno_id')->constrained('cajas_turnos');
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->timestamp('fecha_venta')->useCurrent();
            $table->string('metodo_pago', 30);
            $table->decimal('subtotal', 12, 2);
            $table->decimal('impuestos', 12, 2)->default(0);
            $table->decimal('total', 12, 2);
            $table->string('estado', 25)->default('Completada');
            $table->timestamps();
        });

        Schema::create('venta_lineas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venta_id')->constrained('ventas')->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos');
            $table->decimal('cantidad', 12, 3);
            $table->decimal('precio_unitario', 12, 2);
            $table->decimal('costo_unitario', 12, 2);
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();
        });

        Schema::create('finanzas_movimientos', function (Blueprint $table) {
            $table->id();
            $table->string('folio', 30)->unique();
            $table->date('fecha');
            $table->string('tipo', 30);
            $table->string('categoria', 60);
            $table->string('concepto', 180);
            $table->string('referencia', 50)->nullable();
            $table->decimal('monto', 12, 2);
            $table->string('estado', 25)->default('Pagado');
            $table->date('fecha_vencimiento')->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finanzas_movimientos');
        Schema::dropIfExists('venta_lineas');
        Schema::dropIfExists('ventas');
        Schema::dropIfExists('cajas_turnos');
        Schema::dropIfExists('movimientos_inventario');
        Schema::dropIfExists('lotes_produccion');
        Schema::dropIfExists('orden_compra_lineas');
        Schema::dropIfExists('ordenes_compra');
        Schema::dropIfExists('receta_insumos');
        Schema::dropIfExists('recetas');
        Schema::dropIfExists('productos');
        Schema::dropIfExists('insumos');
        Schema::dropIfExists('clientes');
        Schema::dropIfExists('proveedores');
        Schema::dropIfExists('unidades_medida');
        Schema::dropIfExists('bitacora_auditoria');
    }
};
