<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DespachoController;

Route::post('/consultar-cupo', [DespachoController::class, 'consultarCupo']);
Route::post('/registrar-despacho', [DespachoController::class, 'registrarDespacho']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
