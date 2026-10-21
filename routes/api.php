<?php

use App\Http\Controllers\Iiot\LecturaTemperaturaController;
use Illuminate\Support\Facades\Route;

Route::middleware('iiot.token')->prefix('iiot')->group(function () {
    Route::post('/lecturas', [LecturaTemperaturaController::class, 'store'])->name('iiot.lecturas.store');
});
