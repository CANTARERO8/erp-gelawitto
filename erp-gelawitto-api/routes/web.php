<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Calidad\CalidadController;
use App\Http\Controllers\Catalogos\CatalogosController;
use App\Http\Controllers\Compras\ComprasController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Finanzas\FinanzasController;
use App\Http\Controllers\Inventario\InventarioController;
use App\Http\Controllers\Produccion\ProduccionController;
use App\Http\Controllers\Seguridad\SeguridadController;
use App\Http\Controllers\Ventas\VentasController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::middleware('rol:Producción,Contador')->group(function () {
        Route::get('/catalogos', [CatalogosController::class, 'index'])->name('catalogos');
        Route::post('/catalogos/productos', [CatalogosController::class, 'storeProducto'])->name('catalogos.productos.store');
        Route::post('/catalogos/insumos', [CatalogosController::class, 'storeInsumo'])->name('catalogos.insumos.store');
        Route::post('/catalogos/proveedores', [CatalogosController::class, 'storeProveedor'])->name('catalogos.proveedores.store');
        Route::post('/catalogos/clientes', [CatalogosController::class, 'storeCliente'])->name('catalogos.clientes.store');
    });

    Route::middleware('rol:Contador')->group(function () {
        Route::get('/compras', [ComprasController::class, 'index'])->name('compras');
        Route::post('/compras', [ComprasController::class, 'store'])->name('compras.store');
        Route::post('/compras/{orden}/recibir', [ComprasController::class, 'recibir'])->name('compras.recibir');
    });

    Route::middleware('rol:Producción,Contador,Cajero')->group(function () {
        Route::get('/inventario', [InventarioController::class, 'index'])->name('inventario');
        Route::post('/inventario/ajustes', [InventarioController::class, 'storeAjuste'])->name('inventario.ajustes.store');
    });

    Route::middleware('rol:Producción')->group(function () {
        Route::get('/produccion', [ProduccionController::class, 'index'])->name('produccion');
        Route::post('/produccion/recetas', [ProduccionController::class, 'storeReceta'])->name('produccion.recetas.store');
        Route::post('/produccion/lotes', [ProduccionController::class, 'store'])->name('produccion.lotes.store');
        Route::post('/produccion/lotes/{lote}/ejecutar', [ProduccionController::class, 'ejecutar'])->name('produccion.lotes.ejecutar');

        Route::get('/calidad', [CalidadController::class, 'index'])->name('calidad');
        Route::post('/calidad/lotes/{lote}/inspeccion', [CalidadController::class, 'inspeccionar'])->name('calidad.inspeccionar');
    });

    Route::middleware('rol:Cajero')->group(function () {
        Route::get('/ventas', [VentasController::class, 'index'])->name('ventas');
        Route::post('/ventas', [VentasController::class, 'store'])->name('ventas.store');
        Route::post('/ventas/caja', [VentasController::class, 'gestionarCaja'])->name('ventas.caja');
    });

    Route::middleware('rol:Contador')->group(function () {
        Route::get('/finanzas', [FinanzasController::class, 'index'])->name('finanzas');
        Route::post('/finanzas/gastos', [FinanzasController::class, 'storeGasto'])->name('finanzas.gastos.store');
        Route::post('/finanzas/liquidar/{id}', [FinanzasController::class, 'liquidar'])->name('finanzas.liquidar');
    });

    Route::middleware('rol:Administrador')->group(function () {
        Route::get('/seguridad', [SeguridadController::class, 'index'])->name('seguridad');
        Route::post('/seguridad/usuarios', [SeguridadController::class, 'store'])->name('seguridad.usuarios.store');
        Route::post('/seguridad/usuarios/{id}/toggle', [SeguridadController::class, 'toggle'])->name('seguridad.usuarios.toggle');
    });
});
