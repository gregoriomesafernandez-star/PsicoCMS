<?php

use App\Http\Controllers\Auth\AccesoController;
use App\Http\Controllers\Instalacion\InstalacionController;
use Illuminate\Support\Facades\Route;

Route::controller(InstalacionController::class)->prefix('instalacion')->name('instalacion.')->group(function () {
    Route::get('/', 'inicio')->name('inicio');
    Route::get('/paso/{paso}', 'paso')->whereNumber('paso')->name('paso');
    Route::post('/probar-conexion', 'probarConexion')->name('probar-conexion');
    Route::post('/paso/1', 'guardarBaseDatos')->name('guardar.base-datos');
    Route::post('/paso/2', 'guardarCuenta')->name('guardar.cuenta');
    Route::post('/paso/3', 'guardarPerfil')->name('guardar.perfil');
    Route::post('/paso/4', 'guardarServicios')->name('guardar.servicios');
    Route::post('/paso/5', 'finalizar')->name('finalizar');
});

Route::view('/', 'publico.pendiente')->name('home');

Route::controller(AccesoController::class)->middleware('guest')->group(function () {
    Route::get('/acceso-psicologa', 'mostrar')->name('acceso');
    Route::post('/acceso-psicologa', 'acceder')->name('acceso.enviar');
});

Route::middleware('auth')->prefix('panel-psicologa')->name('panel.')->group(function () {
    Route::view('/', 'panel.pendiente')->name('inicio');
    Route::post('/salir', [AccesoController::class, 'salir'])->name('salir');
});
