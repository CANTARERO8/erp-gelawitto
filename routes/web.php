<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ErpController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [ErpController::class, 'dashboard'])->name('dashboard');

    Route::get('/catalogos', [ErpController::class, 'catalogos'])->name('catalogos');
    Route::post('/catalogos/productos', [ErpController::class, 'storeProducto'])->name('catalogos.productos.store');
    Route::post('/catalogos/insumos', [ErpController::class, 'storeInsumo'])->name('catalogos.insumos.store');

    Route::get('/compras', [ErpController::class, 'compras'])->name('compras');
    Route::post('/compras', [ErpController::class, 'storeCompra'])->name('compras.store');

    Route::get('/inventario', [ErpController::class, 'inventario'])->name('inventario');
    Route::post('/inventario/ajustes', [ErpController::class, 'storeAjuste'])->name('inventario.ajustes.store');

    Route::get('/produccion', [ErpController::class, 'produccion'])->name('produccion');
    Route::post('/produccion/lotes', [ErpController::class, 'storeLote'])->name('produccion.lotes.store');

    Route::get('/ventas', [ErpController::class, 'ventas'])->name('ventas');
    Route::post('/ventas', [ErpController::class, 'storeVenta'])->name('ventas.store');
    Route::post('/ventas/caja', [ErpController::class, 'gestionarCaja'])->name('ventas.caja');

    Route::get('/finanzas', [ErpController::class, 'finanzas'])->name('finanzas');
    Route::post('/finanzas/gastos', [ErpController::class, 'storeGasto'])->name('finanzas.gastos.store');
    Route::post('/finanzas/liquidar/{id}', [ErpController::class, 'liquidarCuenta'])->name('finanzas.liquidar');

    Route::get('/seguridad', [ErpController::class, 'seguridad'])->name('seguridad');
    Route::post('/seguridad/usuarios', [ErpController::class, 'storeUsuario'])->name('seguridad.usuarios.store');
    Route::post('/seguridad/usuarios/{id}/toggle', [ErpController::class, 'toggleUsuario'])->name('seguridad.usuarios.toggle');
});
