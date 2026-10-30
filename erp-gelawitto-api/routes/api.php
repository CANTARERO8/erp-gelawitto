<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Calidad\CalidadController;
use App\Http\Controllers\Catalogos\CatalogosController;
use App\Http\Controllers\Compras\ComprasController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Finanzas\FinanzasController;
use App\Http\Controllers\Iiot\LecturaTemperaturaController;
use App\Http\Controllers\Inventario\InventarioController;
use App\Http\Controllers\Produccion\ProduccionController;
use App\Http\Controllers\Seguridad\SeguridadController;
use App\Http\Controllers\Ventas\VentasController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'showLogin'])->name('api.login.status');
Route::post('/login', [AuthController::class, 'login'])->name('api.login');
Route::get('/parametros', [AuthController::class, 'parametros'])->name('api.parametros');

Route::middleware('iiot.token')->prefix('iiot')->group(function () {
    Route::post('/lecturas', [LecturaTemperaturaController::class, 'store'])->name('iiot.lecturas.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/me', [AuthController::class, 'me'])->name('api.me');
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('api.dashboard');

    Route::middleware('rol:Producción,Contador')->group(function () {
        Route::get('/catalogos', [CatalogosController::class, 'index'])->name('api.catalogos');
        Route::post('/catalogos/productos', [CatalogosController::class, 'storeProducto'])->name('api.catalogos.productos.store');
        Route::post('/catalogos/insumos', [CatalogosController::class, 'storeInsumo'])->name('api.catalogos.insumos.store');
        Route::post('/catalogos/proveedores', [CatalogosController::class, 'storeProveedor'])->name('api.catalogos.proveedores.store');
        Route::post('/catalogos/clientes', [CatalogosController::class, 'storeCliente'])->name('api.catalogos.clientes.store');
    });

    Route::middleware('rol:Contador')->group(function () {
        Route::get('/compras', [ComprasController::class, 'index'])->name('api.compras');
        Route::post('/compras', [ComprasController::class, 'store'])->name('api.compras.store');
        Route::post('/compras/{orden}/recibir', [ComprasController::class, 'recibir'])->name('api.compras.recibir');
    });

    Route::middleware('rol:Producción,Contador,Cajero')->group(function () {
        Route::get('/inventario', [InventarioController::class, 'index'])->name('api.inventario');
        Route::post('/inventario/ajustes', [InventarioController::class, 'storeAjuste'])->name('api.inventario.ajustes.store');
    });

    Route::middleware('rol:Producción')->group(function () {
        Route::get('/produccion', [ProduccionController::class, 'index'])->name('api.produccion');
        Route::post('/produccion/recetas', [ProduccionController::class, 'storeReceta'])->name('api.produccion.recetas.store');
        Route::post('/produccion/lotes', [ProduccionController::class, 'store'])->name('api.produccion.lotes.store');
        Route::post('/produccion/lotes/{lote}/ejecutar', [ProduccionController::class, 'ejecutar'])->name('api.produccion.lotes.ejecutar');

        Route::get('/calidad', [CalidadController::class, 'index'])->name('api.calidad');
        Route::post('/calidad/lotes/{lote}/inspeccion', [CalidadController::class, 'inspeccionar'])->name('api.calidad.inspeccionar');
    });

    Route::middleware('rol:Cajero')->group(function () {
        Route::get('/ventas', [VentasController::class, 'index'])->name('api.ventas');
        Route::post('/ventas', [VentasController::class, 'store'])->name('api.ventas.store');
        Route::post('/ventas/caja', [VentasController::class, 'gestionarCaja'])->name('api.ventas.caja');
    });

    Route::middleware('rol:Contador')->group(function () {
        Route::get('/finanzas', [FinanzasController::class, 'index'])->name('api.finanzas');
        Route::post('/finanzas/gastos', [FinanzasController::class, 'storeGasto'])->name('api.finanzas.gastos.store');
        Route::post('/finanzas/liquidar/{id}', [FinanzasController::class, 'liquidar'])->name('api.finanzas.liquidar');
    });

    Route::middleware('rol:Administrador')->group(function () {
        Route::get('/seguridad', [SeguridadController::class, 'index'])->name('api.seguridad');
        Route::post('/seguridad/usuarios', [SeguridadController::class, 'store'])->name('api.seguridad.usuarios.store');
        Route::post('/seguridad/usuarios/{id}/toggle', [SeguridadController::class, 'toggle'])->name('api.seguridad.usuarios.toggle');
    });
});
