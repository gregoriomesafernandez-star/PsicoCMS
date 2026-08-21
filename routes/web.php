<?php

use App\Http\Controllers\Auth\AccesoController;
use App\Http\Controllers\Instalacion\InstalacionController;
use App\Http\Controllers\Panel\AdjuntoController;
use App\Http\Controllers\Panel\AparienciaController;
use App\Http\Controllers\Panel\AyudaController;
use App\Http\Controllers\Panel\BuscadorController;
use App\Http\Controllers\Panel\BlogController;
use App\Http\Controllers\Panel\CalendarioController;
use App\Http\Controllers\Panel\CategoriaController;
use App\Http\Controllers\Panel\CitaController;
use App\Http\Controllers\Panel\ContenidoWebController;
use App\Http\Controllers\Panel\PacienteController;
use App\Http\Controllers\Panel\DisponibilidadController;
use App\Http\Controllers\Panel\DocumentoController;
use App\Http\Controllers\Panel\EspecialidadController;
use App\Http\Controllers\Panel\FaqController;
use App\Http\Controllers\Panel\FraseController;
use App\Http\Controllers\Panel\ImagenController;
use App\Http\Controllers\Panel\ModuloController;
use App\Http\Controllers\Panel\NotificacionController;
use App\Http\Controllers\Panel\PerfilController;
use App\Http\Controllers\Panel\RedSocialController;
use App\Http\Controllers\Panel\PlanController;
use App\Http\Controllers\Panel\ServicioController;
use App\Http\Controllers\Panel\TemaController;
use App\Http\Controllers\Panel\HistoriaController;
use App\Http\Controllers\Panel\InicioController;
use App\Http\Controllers\Panel\VacacionesController;
use App\Http\Controllers\Publico\PaginaController;
use App\Http\Controllers\Publico\SitemapController;
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

Route::controller(PaginaController::class)->group(function () {
    Route::get('/', 'home')->name('home');
    Route::get('/sobre-mi', 'sobreMi')->name('publico.sobre-mi');
    Route::get('/servicios', 'servicios')->name('publico.servicios');
    Route::get('/blog', 'blog')->middleware('modulo:blog')->name('publico.blog');
    Route::get('/preguntas-frecuentes', 'faq')->middleware('modulo:faq')->name('publico.faq');
    Route::get('/pide-cita', 'pideCita')->middleware('modulo:reservas')->name('publico.pide-cita');
});

Route::controller(SitemapController::class)->group(function () {
    Route::get('/sitemap.xml', 'sitemap')->name('publico.sitemap');
    Route::get('/robots.txt', 'robots')->name('publico.robots');
});

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

    Route::controller(ContenidoWebController::class)->prefix('contenido')->group(function () {
        Route::get('/', 'index')->name('contenido');
        Route::put('/perfil', 'guardarPerfil')->name('contenido.perfil');
        Route::delete('/perfil/foto', 'borrarFoto')->name('contenido.foto');
        Route::put('/sobre-mi', 'guardarSobreMi')->name('contenido.sobre-mi');
        Route::put('/consulta', 'guardarConsulta')->name('contenido.consulta');
        Route::put('/seo', 'guardarSeo')->name('contenido.seo');
    });

    Route::controller(PlanController::class)->prefix('contenido/planes')->name('planes.')->group(function () {
        Route::post('/', 'store')->name('store');
        Route::put('/{plan}', 'update')->name('update');
        Route::patch('/{plan}/mover/{direccion}', 'mover')
            ->whereIn('direccion', ['arriba', 'abajo'])
            ->name('mover');
        Route::delete('/{plan}', 'destroy')->name('destroy');
    });

    Route::controller(ServicioController::class)->prefix('servicios')->name('servicios.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::put('/{servicio}', 'update')->name('update');
        Route::patch('/{servicio}/activo', 'activo')->name('activo');
        Route::patch('/{servicio}/mover/{direccion}', 'mover')
            ->whereIn('direccion', ['arriba', 'abajo'])
            ->name('mover');
        Route::delete('/{servicio}', 'destroy')->name('destroy');
    });

    Route::get('/buscar', BuscadorController::class)->name('buscador');
    Route::get('/ayuda', AyudaController::class)->name('ayuda');

    Route::controller(FraseController::class)->prefix('frases')->group(function () {
        Route::get('/', 'index')->name('frases');
        Route::put('/', 'update')->name('frases.update');
        Route::put('/restaurar', 'restaurarTodas')->name('frases.restaurar-todas');
        Route::put('/{frase}/restaurar', 'restaurar')->whereNumber('frase')->name('frases.restaurar');
    });

    Route::controller(RedSocialController::class)->prefix('redes')->group(function () {
        Route::get('/', 'index')->name('redes');
        Route::put('/', 'update')->name('redes.update');
    });

    Route::controller(NotificacionController::class)->prefix('notificaciones')->group(function () {
        Route::get('/', 'index')->name('notificaciones');
        Route::put('/', 'update')->name('notificaciones.update');
        Route::post('/probar', 'probar')->name('notificaciones.probar');
        Route::delete('/', 'olvidar')->name('notificaciones.olvidar');
    });

    Route::controller(PerfilController::class)->prefix('perfil')->group(function () {
        Route::get('/', 'index')->name('perfil');
        Route::put('/', 'update')->name('perfil.update');
        Route::put('/password', 'password')->name('perfil.password');
        Route::delete('/avatar', 'borrarAvatar')->name('perfil.avatar');
    });

    Route::put('/apariencia', [AparienciaController::class, 'update'])->name('apariencia');

    Route::controller(ModuloController::class)->prefix('modulos')->group(function () {
        Route::get('/', 'index')->name('modulos');
        Route::put('/', 'update')->name('modulos.update');
    });

    Route::controller(ImagenController::class)->prefix('imagenes')->group(function () {
        Route::get('/', 'index')->name('imagenes');
        Route::post('/{imagen}', 'update')->whereNumber('imagen')->name('imagenes.update');
        Route::delete('/{imagen}', 'restaurar')->whereNumber('imagen')->name('imagenes.restaurar');
    });

    Route::controller(TemaController::class)->prefix('temas')->name('temas.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::put('/modo', 'modo')->name('modo');
        Route::put('/{slug}', 'activar')->name('activar');
        Route::get('/{slug}/previsualizar/{modo?}', 'previsualizar')
            ->whereIn('modo', ['landing', 'multipagina'])
            ->name('previsualizar');
    });

    Route::controller(EspecialidadController::class)->prefix('especialidades')->name('especialidades.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::post('/ejemplos', 'ejemplos')->name('ejemplos');
        Route::put('/{especialidad}', 'update')->name('update');
        Route::patch('/{especialidad}/activo', 'activo')->name('activo');
        Route::patch('/{especialidad}/mover/{direccion}', 'mover')
            ->whereIn('direccion', ['arriba', 'abajo'])
            ->name('mover');
        Route::delete('/{especialidad}', 'destroy')->name('destroy');
    });

});
