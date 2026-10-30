<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $secuencias = [
            'folio_compras_seq' => 'ordenes_compra',
            'folio_lotes_seq' => 'lotes_produccion',
            'folio_ventas_seq' => 'ventas',
            'folio_turnos_seq' => 'cajas_turnos',
            'folio_finanzas_seq' => 'finanzas_movimientos',
            'folio_calidad_seq' => 'controles_calidad',
            'folio_asientos_seq' => 'asientos_contables',
        ];

        foreach ($secuencias as $seq => $tabla) {
            DB::statement("CREATE SEQUENCE IF NOT EXISTS {$seq} START WITH 1 INCREMENT BY 1");
            if (Schema::hasTable($tabla)) {
                $total = (int) DB::table($tabla)->count();
                if ($total > 0) {
                    DB::statement("SELECT setval('{$seq}', {$total}, true)");
                }
            }
        }

        Schema::create('parametros', function (Blueprint $table) {
            $table->string('clave', 60)->primary();
            $table->string('valor', 120);
            $table->string('descripcion', 255)->nullable();
            $table->timestamps();
        });

        $now = now();
        DB::table('parametros')->insert([
            [
                'clave' => 'iva_porcentaje',
                'valor' => '15',
                'descripcion' => 'Porcentaje de Impuesto al Valor Agregado (IVA) aplicable en compras y ventas',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'clave' => 'refrigerador_codigo',
                'valor' => 'REF-01',
                'descripcion' => 'Código del refrigerador industrial de maduración y enfriamiento de lotes',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'clave' => 'refrigerador_temp_min',
                'valor' => '2.0',
                'descripcion' => 'Temperatura mínima aceptable en grados Celsius para el refrigerador de maduración',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'clave' => 'refrigerador_temp_max',
                'valor' => '6.0',
                'descripcion' => 'Temperatura máxima aceptable en grados Celsius para el refrigerador de maduración',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 40)->unique();
            $table->string('descripcion', 180)->nullable();
            $table->timestamps();
        });

        Schema::create('modulos', function (Blueprint $table) {
            $table->id();
            $table->string('clave', 40)->unique();
            $table->string('nombre', 80);
            $table->string('ruta', 80);
            $table->string('seccion', 40)->default('Operaciones');
            $table->integer('orden')->default(1);
            $table->timestamps();
        });

        Schema::create('rol_modulo', function (Blueprint $table) {
            $table->foreignId('rol_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('modulo_id')->constrained('modulos')->cascadeOnDelete();
            $table->primary(['rol_id', 'modulo_id']);
        });

        $rolesData = [
            ['nombre' => 'Administrador', 'descripcion' => 'Control total del sistema, seguridad, usuarios y auditoría', 'created_at' => $now, 'updated_at' => $now],
            ['nombre' => 'Producción', 'descripcion' => 'Formulación técnica, simulador MRP, lotes y control de calidad', 'created_at' => $now, 'updated_at' => $now],
            ['nombre' => 'Cajero', 'descripcion' => 'Apertura de turno, punto de venta y arqueo de caja', 'created_at' => $now, 'updated_at' => $now],
            ['nombre' => 'Contador', 'descripcion' => 'Compras, libro diario de partida doble y estados financieros', 'created_at' => $now, 'updated_at' => $now],
        ];
        DB::table('roles')->insert($rolesData);

        $modulosData = [
            ['clave' => 'dashboard', 'nombre' => 'Tablero Ejecutivo', 'ruta' => '/dashboard', 'seccion' => 'Principal', 'orden' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['clave' => 'catalogos', 'nombre' => 'Catálogos Maestros', 'ruta' => '/catalogos', 'seccion' => 'Operaciones', 'orden' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['clave' => 'compras', 'nombre' => 'Compras a Proveedores', 'ruta' => '/compras', 'seccion' => 'Comercial', 'orden' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['clave' => 'inventario', 'nombre' => 'Inventario y Kardex', 'ruta' => '/inventario', 'seccion' => 'Operaciones', 'orden' => 4, 'created_at' => $now, 'updated_at' => $now],
            ['clave' => 'produccion', 'nombre' => 'Recetas y Producción', 'ruta' => '/produccion', 'seccion' => 'Operaciones', 'orden' => 5, 'created_at' => $now, 'updated_at' => $now],
            ['clave' => 'calidad', 'nombre' => 'Calidad y Trazabilidad', 'ruta' => '/calidad', 'seccion' => 'Operaciones', 'orden' => 6, 'created_at' => $now, 'updated_at' => $now],
            ['clave' => 'ventas', 'nombre' => 'Ventas y Turno de Caja', 'ruta' => '/ventas', 'seccion' => 'Comercial', 'orden' => 7, 'created_at' => $now, 'updated_at' => $now],
            ['clave' => 'finanzas', 'nombre' => 'Finanzas y Cuentas', 'ruta' => '/finanzas', 'seccion' => 'Finanzas', 'orden' => 8, 'created_at' => $now, 'updated_at' => $now],
            ['clave' => 'seguridad', 'nombre' => 'Usuarios y Auditoría', 'ruta' => '/seguridad', 'seccion' => 'Seguridad', 'orden' => 9, 'created_at' => $now, 'updated_at' => $now],
        ];
        DB::table('modulos')->insert($modulosData);

        $rolesMap = DB::table('roles')->pluck('id', 'nombre');
        $modulosMap = DB::table('modulos')->pluck('id', 'clave');

        $permisosPorRol = [
            'Administrador' => ['dashboard', 'catalogos', 'compras', 'inventario', 'produccion', 'calidad', 'ventas', 'finanzas', 'seguridad'],
            'Producción' => ['dashboard', 'catalogos', 'inventario', 'produccion', 'calidad'],
            'Cajero' => ['dashboard', 'inventario', 'ventas'],
            'Contador' => ['dashboard', 'catalogos', 'compras', 'inventario', 'finanzas'],
        ];

        $pivots = [];
        foreach ($permisosPorRol as $rolNombre => $clavesModulo) {
            $rolId = $rolesMap[$rolNombre] ?? null;
            if (! $rolId) {
                continue;
            }
            foreach ($clavesModulo as $clave) {
                $modId = $modulosMap[$clave] ?? null;
                if ($modId) {
                    $pivots[] = ['rol_id' => $rolId, 'modulo_id' => $modId];
                }
            }
        }
        DB::table('rol_modulo')->insert($pivots);

        if (! Schema::hasColumn('users', 'rol_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('rol_id')->nullable()->after('rol')->constrained('roles')->nullOnDelete();
            });
        }

        foreach ($rolesMap as $nombre => $id) {
            DB::table('users')->where('rol', $nombre)->update(['rol_id' => $id]);
        }

        if (Schema::hasTable('cuentas_contables')) {
            $cuentas = [
                ['codigo' => '1101', 'nombre' => 'Caja y Bancos (Efectivo y Equivalentes)', 'tipo' => 'Activo', 'naturaleza' => 'Deudora'],
                ['codigo' => '1102', 'nombre' => 'Cuentas por Cobrar Clientes', 'tipo' => 'Activo', 'naturaleza' => 'Deudora'],
                ['codigo' => '1103', 'nombre' => 'IVA Acreditable (15%)', 'tipo' => 'Activo', 'naturaleza' => 'Deudora'],
                ['codigo' => '1104', 'nombre' => 'Inventario de Materia Prima e Insumos', 'tipo' => 'Activo', 'naturaleza' => 'Deudora'],
                ['codigo' => '1105', 'nombre' => 'Inventario de Producto Terminado (Helados)', 'tipo' => 'Activo', 'naturaleza' => 'Deudora'],
                ['codigo' => '2101', 'nombre' => 'Cuentas por Pagar Proveedores', 'tipo' => 'Pasivo', 'naturaleza' => 'Acreedora'],
                ['codigo' => '2102', 'nombre' => 'IVA por Pagar (15%)', 'tipo' => 'Pasivo', 'naturaleza' => 'Acreedora'],
                ['codigo' => '3101', 'nombre' => 'Capital Social y Patrimonio Inicial', 'tipo' => 'Patrimonio', 'naturaleza' => 'Acreedora'],
                ['codigo' => '4101', 'nombre' => 'Ingresos por Ventas de Helados', 'tipo' => 'Ingreso', 'naturaleza' => 'Acreedora'],
                ['codigo' => '5101', 'nombre' => 'Costo de Ventas de Producción', 'tipo' => 'Costo', 'naturaleza' => 'Deudora'],
                ['codigo' => '5201', 'nombre' => 'Gastos Operativos de Planta', 'tipo' => 'Gasto', 'naturaleza' => 'Deudora'],
                ['codigo' => '5202', 'nombre' => 'Pérdidas por Mermas y Scrap de Calidad', 'tipo' => 'Gasto', 'naturaleza' => 'Deudora'],
            ];

            foreach ($cuentas as $c) {
                DB::table('cuentas_contables')->updateOrInsert(
                    ['codigo' => $c['codigo']],
                    array_merge($c, ['updated_at' => $now, 'created_at' => $now])
                );
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'rol_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropConstrainedForeignId('rol_id');
            });
        }
        Schema::dropIfExists('rol_modulo');
        Schema::dropIfExists('modulos');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('parametros');

        $secuencias = [
            'folio_compras_seq',
            'folio_lotes_seq',
            'folio_ventas_seq',
            'folio_turnos_seq',
            'folio_finanzas_seq',
            'folio_calidad_seq',
            'folio_asientos_seq',
        ];
        foreach ($secuencias as $seq) {
            DB::statement("DROP SEQUENCE IF EXISTS {$seq}");
        }
    }
};
