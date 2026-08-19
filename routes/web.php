<?php

use App\Http\Controllers\Auth\AccesoController;
use App\Http\Controllers\Instalacion\InstalacionController;
use App\Http\Controllers\Panel\AdjuntoController;
use App\Http\Controllers\Panel\BlogController;
use App\Http\Controllers\Panel\CalendarioController;
use App\Http\Controllers\Panel\CategoriaController;
use App\Http\Controllers\Panel\CitaController;
use App\Http\Controllers\Panel\PacienteController;
use App\Http\Controllers\Panel\DisponibilidadController;
use App\Http\Controllers\Panel\DocumentoController;
use App\Http\Controllers\Panel\FaqController;
use App\Http\Controllers\Panel\HistoriaController;
use App\Http\Controllers\Panel\InicioController;
use App\Http\Controllers\Panel\VacacionesController;
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

    Route::get('/', InicioController::class)->name('inicio');
    Route::post('/salir', [AccesoController::class, 'salir'])->name('salir');

    Route::controller(DisponibilidadController::class)->prefix('disponibilidad')->group(function () {
        Route::get('/', 'index')->name('disponibilidad');
        Route::put('/configuracion', 'guardarConfiguracion')->name('disponibilidad.configuracion');
        Route::put('/franjas', 'guardarFranjas')->name('disponibilidad.franjas');
        Route::put('/modo-vacaciones', 'modoVacaciones')->name('disponibilidad.modo-vacaciones');
    });

    Route::controller(VacacionesController::class)->prefix('vacaciones')->name('vacaciones.')->group(function () {
        Route::post('/', 'store')->name('store');
        Route::delete('/{periodo}', 'destroy')->name('destroy');
    });

    Route::controller(CalendarioController::class)->group(function () {
        Route::get('/calendario', 'index')->name('calendario');
        Route::get('/citas/eventos', 'eventos')->name('citas.eventos');
        Route::patch('/citas/{cita}/mover', 'mover')->name('citas.mover');
    });

    Route::controller(CitaController::class)->prefix('citas')->name('citas.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/huecos', 'huecos')->name('huecos');
        Route::post('/', 'store')->name('store');
        Route::put('/{cita}', 'update')->name('update');
        Route::patch('/{cita}/estado', 'cambiarEstado')->name('estado');
        Route::delete('/{cita}', 'destroy')->name('destroy');
    });

    Route::controller(PacienteController::class)->prefix('pacientes')->name('pacientes.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/nuevo', 'create')->name('create');
        Route::get('/buscar', 'buscar')->name('buscar');
        Route::post('/', 'store')->name('store');
        Route::get('/{paciente}', 'show')->whereNumber('paciente')->name('show');
        Route::get('/{paciente}/editar', 'edit')->whereNumber('paciente')->name('edit');
        Route::put('/{paciente}', 'update')->whereNumber('paciente')->name('update');
        Route::delete('/{paciente}', 'destroy')->whereNumber('paciente')->name('destroy');
    });

    Route::controller(FaqController::class)->prefix('faqs')->name('faqs.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::post('/ejemplos', 'ejemplos')->name('ejemplos');
        Route::put('/{faq}', 'update')->name('update');
        Route::patch('/{faq}/activa', 'activa')->name('activa');
        Route::patch('/{faq}/mover/{direccion}', 'mover')
            ->whereIn('direccion', ['arriba', 'abajo'])
            ->name('mover');
        Route::delete('/{faq}', 'destroy')->name('destroy');
    });

    Route::controller(DocumentoController::class)->prefix('documentos')->group(function () {
        Route::get('/', 'index')->name('documentos');
        Route::put('/', 'update')->name('documentos.update');
        Route::put('/restaurar', 'restaurar')->name('documentos.restaurar');
        Route::get('/plantilla.pdf', 'plantillaEnBlanco')->name('documentos.plantilla');
    });

    Route::get('/pacientes/{paciente}/proteccion-datos.pdf', [DocumentoController::class, 'documentoDelPaciente'])
        ->whereNumber('paciente')
        ->name('pacientes.proteccion-datos');

    Route::controller(AdjuntoController::class)->prefix('historias/adjuntos')->name('historias.adjuntos.')->group(function () {
        Route::get('/{adjunto}', 'ver')->whereNumber('adjunto')->name('ver');
        Route::get('/{adjunto}/descargar', 'descargar')->whereNumber('adjunto')->name('descargar');
        Route::delete('/{adjunto}', 'destroy')->whereNumber('adjunto')->name('destroy');
    });

    Route::controller(HistoriaController::class)->prefix('historias')->name('historias.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/nueva', 'create')->name('create');
        Route::get('/citas', 'citasDelPaciente')->name('citas');
        Route::post('/', 'store')->name('store');
        Route::get('/{historia}', 'show')->whereNumber('historia')->name('show');
        Route::get('/{historia}/editar', 'edit')->whereNumber('historia')->name('edit');
        Route::put('/{historia}', 'update')->whereNumber('historia')->name('update');
        Route::delete('/{historia}', 'destroy')->whereNumber('historia')->name('destroy');
    });

    Route::controller(BlogController::class)->prefix('blog')->name('blog.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/nuevo', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{articulo}/editar', 'edit')->name('edit');
        Route::get('/{articulo}/previsualizar', 'previsualizar')->name('previsualizar');
        Route::put('/{articulo}', 'update')->name('update');
        Route::delete('/{articulo}', 'destroy')->name('destroy');
    });

    Route::controller(CategoriaController::class)->prefix('categorias')->name('categorias.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::put('/{categoria}', 'update')->name('update');
        Route::delete('/{categoria}', 'destroy')->name('destroy');
    });

});
