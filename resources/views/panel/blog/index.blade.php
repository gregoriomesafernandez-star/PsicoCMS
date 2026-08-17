@extends('panel.layouts.app')

@section('titulo', 'Articulos')
@section('encabezado', 'Gestion de articulos')
@section('subtitulo', 'Administra tus publicaciones y el contenido educativo de tu web.')

@section('acciones')
    <a class="boton boton--secundario" href="{{ route('panel.categorias.index') }}">
        <i class="fa-solid fa-tags"></i> Categorias
    </a>
    <a class="boton" href="{{ route('panel.blog.create') }}">
        <i class="fa-solid fa-plus"></i> Nuevo articulo
    </a>
@endsection

@section('contenido')

    <div class="rejilla rejilla--4 separado">
        <x-panel.card class="metrica">
            <span class="metrica__icono metrica__icono--azul"><i class="fa-solid fa-newspaper"></i></span>
            <p class="metrica__etiqueta">Total de articulos</p>
            <p class="metrica__valor">{{ $resumen['total'] }}</p>
        </x-panel.card>

        <x-panel.card class="metrica">
            <span class="metrica__icono metrica__icono--verde"><i class="fa-solid fa-circle-check"></i></span>
            <p class="metrica__etiqueta">Publicados</p>
            <p class="metrica__valor">{{ $resumen['publicados'] }}</p>
        </x-panel.card>

        <x-panel.card class="metrica">
            <span class="metrica__icono metrica__icono--naranja"><i class="fa-solid fa-clock"></i></span>
            <p class="metrica__etiqueta">Programados</p>
            <p class="metrica__valor">{{ $resumen['programados'] }}</p>
        </x-panel.card>

        <x-panel.card class="metrica">
            <span class="metrica__icono metrica__icono--gris"><i class="fa-solid fa-file-pen"></i></span>
            <p class="metrica__etiqueta">Borradores</p>
            <p class="metrica__valor">{{ $resumen['borradores'] }}</p>
        </x-panel.card>
    </div>

    <x-panel.card titulo="Filtrar articulos" icono="fa-filter" class="separado">
        <form method="GET" action="{{ route('panel.blog.index') }}" class="filtros">
            <div class="rejilla rejilla--3">
                <x-panel.campo nombre="q" etiqueta="Buscar" :valor="$filtros['q'] ?? null"
                               placeholder="Titulo, extracto o contenido" />

                <x-panel.campo nombre="categoria" etiqueta="Categoria" tipo="select"
                               :valor="$filtros['categoria'] ?? null"
                               :opciones="['' => 'Todas'] + $categorias" />

                <x-panel.campo nombre="estado" etiqueta="Estado" tipo="select"
                               :valor="$filtros['estado'] ?? null"
                               :opciones="['' => 'Todos'] + $estados" />
            </div>

            <div class="filtros__botones">
                <button class="boton" type="submit">
                    <i class="fa-solid fa-magnifying-glass"></i> Filtrar
                </button>
                <a class="boton boton--secundario" href="{{ route('panel.blog.index') }}">
                    <i class="fa-solid fa-rotate-left"></i> Limpiar
                </a>
            </div>
        </form>
    </x-panel.card>

    <x-panel.card titulo="Listado de articulos" icono="fa-newspaper">

        @if ($articulos->isEmpty())
            <x-panel.empty-state icono="fa-feather-pointed" titulo="Todavia no hay articulos"
                                 mensaje="Escribe tu primer articulo para empezar a compartir contenido con tus pacientes.">
                <a class="boton" href="{{ route('panel.blog.create') }}">
                    <i class="fa-solid fa-plus"></i> Escribir el primer articulo
                </a>
            </x-panel.empty-state>
        @else
            <x-panel.tabla :cabeceras="['Articulo', 'Categoria', 'Fecha', 'Estado', 'Acciones']">
                @foreach ($articulos as $articulo)
                    <tr>
                        <td>
                            <div class="articulo-fila">
                                @if ($articulo->url_imagen)
                                    <img class="articulo-fila__imagen" src="{{ $articulo->url_imagen }}"
                                         alt="Imagen de {{ $articulo->titulo }}" loading="lazy">
                                @else
                                    <span class="articulo-fila__imagen articulo-fila__imagen--vacia">
                                        <i class="fa-solid fa-image"></i>
                                    </span>
                                @endif

                                <div>
                                    <p class="celda__principal">{{ $articulo->titulo }}</p>
                                    <p class="celda__secundario">{{ $articulo->resumen ?: 'Sin extracto' }}</p>
                                </div>
                            </div>
                        </td>

                        <td>
                            @if ($articulo->categoria)
                                <x-panel.badge tono="info" icono="fa-tag">{{ $articulo->categoria->nombre }}</x-panel.badge>
                            @else
                                <span class="celda__secundario">Sin categoria</span>
                            @endif
                        </td>

                        <td>
                            @if ($articulo->publicado_at)
                                <p class="celda__principal">{{ $articulo->publicado_at->format('d/m/Y') }}</p>
                                <p class="celda__secundario">{{ $articulo->publicado_at->format('H:i') }}</p>
                            @else
                                <span class="celda__secundario">Sin fecha</span>
                            @endif
                        </td>

                        <td>
                            <x-panel.badge :tono="$articulo->tono_estado">{{ $articulo->estado_legible }}</x-panel.badge>
                        </td>

                        <td>
                            <div class="acciones-fila">
                                <a class="boton boton--secundario boton--icono"
                                   href="{{ route('panel.blog.edit', $articulo) }}"
                                   aria-label="Editar {{ $articulo->titulo }}" title="Editar articulo">
                                    <i class="fa-solid fa-pen"></i>
                                </a>

                                <x-panel.confirmar-borrado :accion="route('panel.blog.destroy', $articulo)"
                                                           titulo="Eliminar articulo"
                                                           mensaje="El articulo y su imagen destacada se borraran definitivamente."
                                                           etiqueta="Eliminar articulo" solo-icono />
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-panel.tabla>

            <x-panel.paginacion :coleccion="$articulos" nombre="articulos" />
        @endif

    </x-panel.card>

@endsection

@push('estilos')
    <link rel="stylesheet" href="{{ asset('panel/css/citas.css') }}">
    <link rel="stylesheet" href="{{ asset('panel/css/blog.css') }}">
@endpush
