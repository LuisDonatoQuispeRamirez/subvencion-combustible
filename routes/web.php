<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthEstacionController;

Route::get('/', function () {
    return redirect('/despacho');
});

Route::get('/login', [AuthEstacionController::class, 'mostrarLogin'])->name('login');
Route::post('/login', [AuthEstacionController::class, 'login']);
Route::post('/logout', [AuthEstacionController::class, 'logout'])->name('logout');

Route::get('/despacho', function () {
    return view('despacho');
})->middleware('auth:estacion');