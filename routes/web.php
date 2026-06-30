<?php

use App\Http\Controllers\CarreraController;
use App\Http\Controllers\InteligenciaController;
use App\Http\Controllers\PublicoController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicoController::class, 'index'])->name('publico.index');
Route::post('/estudiantes', [PublicoController::class, 'registrarInteres'])->name('estudiantes.store');

Route::prefix('sistemas')->name('sistemas.')->group(function () {
    Route::get('/carreras', [CarreraController::class, 'index'])->name('carreras.index');
    Route::post('/carreras', [CarreraController::class, 'store'])->name('carreras.store');

    Route::get('/ia', [InteligenciaController::class, 'index'])->name('ia.index');
    Route::post('/ia/entrenar', [InteligenciaController::class, 'entrenar'])->name('ia.entrenar');
});
