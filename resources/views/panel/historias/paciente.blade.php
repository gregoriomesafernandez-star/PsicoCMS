@extends('panel.layouts.app')

@php
    $hayFiltros = filled($filtros['q'] ?? null) || filled($filtros['desde'] ?? null) || filled($filtros['hasta'] ?? null);
    $urlNueva = route('panel.historias.create', ['paciente' => $paciente->id]);
    $urlSeccion = route('panel.historias.index', ['paciente' => $paciente->id]);
@endphp

@section('titulo', 'Historia de ' . $paciente->nombre_completo)
@section('encabezado', 'Historia de ' . $paciente->nombre_completo)

@section('migas')
    <x-panel.migas :items="[
        ['texto' => 'Inicio', 'url' => route('panel.inicio')],
        ['texto' => 'Historias clinicas', 'url' => route('panel.historias.index')],
        ['texto' => $paciente->nombre_completo],
    ]" />
@endsection

@section('subtitulo')
    <span class="pagina__meta"><i class="fa-solid fa-phone"></i> {{ $paciente->telefono }}</span>
    <span class="pagina__meta"><i class="fa-solid fa-notes-medical"></i> {{ $resumen['total'] }} sesion(es) anotadas</span>
@endsection

@section('acciones')
    <a class="boton boton--secundario" href="{{ route('panel.historias.index') }}">
        <i class="fa-solid fa-arrow-left"></i> Todas las historias
    </a>

    <a class="boton boton--secundario" href="{{ route('panel.pacientes.show', $paciente) }}">
        <i class="fa-solid fa-address-card"></i> Su ficha
    </a>

    <a class="boton" href="{{ $urlNueva }}">
        <i class="fa-solid fa-plus"></i> Nueva entrada
    </a>
@endsection

@section('contenido')

    <x-panel.aviso tono="info" titulo="Esta informacion es confidencial" class="separado">
        <p>El contenido de cada sesion se guarda cifrado y los archivos adjuntos solo se pueden abrir
            desde aqui, con tu sesion iniciada. Nadie mas tiene acceso.</p>
    </x-panel.aviso>

    <div class="rejilla rejilla--3 separado">
        <x-panel.card class="metrica">
            <span class="metrica__icono metrica__icono--azul"><i class="fa-solid fa-notes-medical"></i></span>
            <p class="metrica__etiqueta">Sesiones anotadas</p>
            <p class="metrica__valor">{{ $resumen['total'] }}</p>
        </x-panel.card>

        <x-panel.card class="metrica">
            <span class="metrica__icono metrica__icono--verde"><i class="fa-solid fa-calendar-day"></i></span>
            <p class="metrica__etiqueta">Escritas este mes</p>
            <p class="metrica__valor">{{ $resumen['mes'] }}</p>
        </x-panel.card>

        <x-panel.card class="metrica">
            <span class="metrica__icono metrica__icono--naranja"><i class="fa-solid fa-paperclip"></i></span>
            <p class="metrica__etiqueta">Archivos adjuntos</p>
            <p class="metrica__valor">{{ $resumen['adjuntos'] }}</p>
        </x-panel.card>
    </div>

    <x-panel.card titulo="Filtrar sus sesiones" icono="fa-filter" class="separado">
        <form method="GET" action="{{ route('panel.historias.index') }}" class="filtros">
            <input type="hidden" name="paciente" value="{{ $paciente->id }}">

            <div class="rejilla rejilla--3">
                <x-panel.campo nombre="q" etiqueta="Buscar" :valor="$filtros['q'] ?? null"
                               placeholder="Titulo de la entrada"
                               ayuda="El contenido esta cifrado, asi que no se puede buscar dentro." />

                <x-panel.campo nombre="desde" etiqueta="Desde" tipo="date" :valor="$filtros['desde'] ?? null" />
                <x-panel.campo nombre="hasta" etiqueta="Hasta" tipo="date" :valor="$filtros['hasta'] ?? null" />
            </div>

            <div class="filtros__botones">
                <button class="boton" type="submit">
                    <i class="fa-solid fa-magnifying-glass"></i> Filtrar
                </button>
                <a class="boton boton--secundario" href="{{ $urlSeccion }}">
                    <i class="fa-solid fa-rotate-left"></i> Limpiar
                </a>
            </div>
        </form>
    </x-panel.card>

    <x-panel.card titulo="Linea de tiempo" icono="fa-timeline"
                  :descripcion="$entradas->isEmpty() ? null : 'De la sesion mas reciente a la mas antigua.'">

        @if ($entradas->isEmpty())
            @if ($hayFiltros)
                <x-panel.empty-state icono="fa-magnifying-glass" titulo="Ninguna entrada coincide"
                                     mensaje="Prueba a cambiar los filtros o a limpiarlos para ver todas sus sesiones.">
                    <a class="boton boton--secundario" href="{{ $urlSeccion }}">
                        <i class="fa-solid fa-rotate-left"></i> Limpiar filtros
                    </a>
                </x-panel.empty-state>
            @else
                <x-panel.empty-state icono="fa-notes-medical" titulo="Sin sesiones anotadas todavia"
                                     mensaje="Despues de cada sesion puedes anotar aqui como ha ido y adjuntar fotos o PDF escaneados.">
                    <a class="boton" href="{{ $urlNueva }}">
                        <i class="fa-solid fa-plus"></i> Escribir la primera entrada
                    </a>
                </x-panel.empty-state>
            @endif
        @else
            <ol class="linea-tiempo">
                @foreach ($entradas as $entrada)
                    <li class="linea-tiempo__item">

                        <div class="linea-tiempo__marca">
                            <span class="linea-tiempo__dia">{{ $entrada->fecha_sesion->format('d') }}</span>
                            <span class="linea-tiempo__mes">{{ $entrada->fecha_sesion->locale('es')->isoFormat('MMM') }}</span>
                            <span class="linea-tiempo__ano">{{ $entrada->fecha_sesion->format('Y') }}</span>
                        </div>

                        <article class="entrada">

                            <header class="entrada__cabecera">
                                <h3 class="entrada__titulo">
                                    <a href="{{ route('panel.historias.show', $entrada) }}">{{ $entrada->titulo_legible }}</a>
                                </h3>

                                <div class="entrada__etiquetas">
                                    @if ($entrada->adjuntos_count > 0)
                                        <x-panel.badge tono="neutro" icono="fa-paperclip">
                                            {{ $entrada->adjuntos_count }}
                                        </x-panel.badge>
                                    @endif

                                    @if ($entrada->cita)
                                        <x-panel.badge :tono="$entrada->cita->modalidad === 'online' ? 'info' : 'exito'"
                                                       :icono="$entrada->cita->modalidad === 'online' ? 'fa-video' : 'fa-location-dot'">
                                            {{ $entrada->cita->modalidad_legible }}
                                        </x-panel.badge>
                                    @endif
                                </div>
                            </header>

                            @if (filled($entrada->resumen))
                                <p class="entrada__resumen">{{ $entrada->resumen }}</p>
                            @else
                                <p class="entrada__resumen entrada__resumen--vacio">Esta entrada no tiene texto escrito.</p>
                            @endif

                            <footer class="entrada__pie">
                                <span class="celda__secundario">
                                    <i class="fa-solid fa-clock"></i>
                                    {{ $entrada->fecha_sesion->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY') }}
                                </span>

                                <div class="acciones-fila">
                                    <a class="boton boton--secundario boton--icono"
                                       href="{{ route('panel.historias.show', $entrada) }}"
                                       aria-label="Leer la entrada completa" title="Leer la entrada completa">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>

                                    <a class="boton boton--secundario boton--icono"
                                       href="{{ route('panel.historias.edit', $entrada) }}"
                                       aria-label="Editar entrada" title="Editar entrada">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>

                                    <x-panel.confirmar-borrado :accion="route('panel.historias.destroy', $entrada)"
                                                               titulo="Eliminar entrada"
                                                               :mensaje="'Se borrara para siempre la entrada del ' . $entrada->fecha_sesion->format('d/m/Y') . ($entrada->adjuntos_count > 0 ? ' y sus ' . $entrada->adjuntos_count . ' archivo(s) adjunto(s)' : '') . '. No se podra recuperar.'"
                                                               etiqueta="Eliminar entrada" solo-icono />
                                </div>
                            </footer>

                        </article>

                    </li>
                @endforeach
            </ol>

            <x-panel.paginacion :coleccion="$entradas" nombre="entradas" />
        @endif

    </x-panel.card>

@endsection

@push('estilos')
    <link rel="stylesheet" href="{{ recurso('panel/css/citas.css') }}">
    <link rel="stylesheet" href="{{ recurso('panel/css/historias.css') }}">
@endpush
