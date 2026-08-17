@extends('panel.layouts.app')

@section('titulo', $articulo->titulo)
@section('encabezado', $articulo->titulo)

@section('migas')
    <x-panel.migas :items="[
        ['texto' => 'Inicio', 'url' => route('panel.inicio')],
        ['texto' => 'Blog'],
        ['texto' => 'Articulos', 'url' => route('panel.blog.index')],
        ['texto' => $articulo->titulo, 'url' => route('panel.blog.edit', $articulo)],
        ['texto' => 'Vista previa'],
    ]" />
@endsection

@section('subtitulo')
    <x-panel.badge :tono="$articulo->tono_estado">{{ $articulo->estado_legible }}</x-panel.badge>
    <span class="pagina__meta">
        @if ($articulo->publicado_at)
            &middot; {{ $articulo->publicado_at->format('d/m/Y H:i') }}
        @else
            &middot; Sin fecha de publicacion
        @endif

        @if ($articulo->categoria)
            &middot; {{ $articulo->categoria->nombre }}
        @endif
    </span>
@endsection

@section('acciones')
    <a class="boton boton--secundario" href="{{ route('panel.blog.edit', $articulo) }}"
       title="Volver a la edicion de este articulo">
        <i class="fa-solid fa-arrow-left"></i> Volver
    </a>
    <a class="boton" href="{{ route('panel.blog.edit', $articulo) }}">
        <i class="fa-solid fa-pen"></i> Editar
    </a>
@endsection

@section('contenido')

    @if ($articulo->estado !== 'publicado')
        <x-panel.aviso tono="atencion" titulo="Asi se vera cuando lo publiques" class="separado">
            <p>
                @if ($articulo->estado === 'programado')
                    Este articulo esta programado para el {{ $articulo->publicado_at->format('d/m/Y \a \l\a\s H:i') }}
                    y todavia no es visible en tu web.
                @else
                    Este articulo es un borrador y todavia no es visible en tu web.
                @endif
            </p>
        </x-panel.aviso>
    @endif

    <x-panel.card class="previsualizacion">

        @if ($articulo->url_imagen)
            <img class="previsualizacion__portada" src="{{ $articulo->url_imagen }}"
                 alt="Imagen destacada de {{ $articulo->titulo }}">
        @endif

        @if ($articulo->extracto)
            <p class="previsualizacion__extracto">{{ $articulo->extracto }}</p>
        @endif

        @if (trim((string) $articulo->contenido) !== '')
            <div class="contenido-rico">{!! $articulo->contenido !!}</div>
        @else
            <x-panel.empty-state icono="fa-align-left" titulo="Este articulo no tiene contenido"
                                 mensaje="Vuelve a la edicion y escribe el cuerpo del articulo.">
                <a class="boton" href="{{ route('panel.blog.edit', $articulo) }}">
                    <i class="fa-solid fa-pen"></i> Escribir el contenido
                </a>
            </x-panel.empty-state>
        @endif

    </x-panel.card>

    <x-panel.card titulo="Asi se veria en Google" icono="fa-magnifying-glass" class="separado">
        <div class="resultado-google">
            <p class="resultado-google__url">tuweb.com &rsaquo; blog &rsaquo; {{ $articulo->slug }}</p>
            <p class="resultado-google__titulo">{{ $articulo->meta_title ?: $articulo->titulo }}</p>
            <p class="resultado-google__descripcion">
                {{ $articulo->meta_description ?: ($articulo->extracto ?: 'Sin descripcion. Anadela desde la edicion del articulo.') }}
            </p>
        </div>
    </x-panel.card>

@endsection

@push('estilos')
    <link rel="stylesheet" href="{{ asset('panel/css/blog.css') }}">
@endpush
