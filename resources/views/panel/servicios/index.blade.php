@extends('panel.layouts.app')

@section('titulo', 'Servicios')
@section('encabezado', 'Servicios')
@section('subtitulo', 'Lo que ofreces a tus pacientes, tal y como aparecera en tu web.')

@section('migas')
    <x-panel.migas :items="[
        ['texto' => 'Inicio', 'url' => route('panel.inicio')],
        ['texto' => 'Servicios'],
    ]" />
@endsection

@section('acciones')
    <button class="boton" type="button" data-nuevo>
        <i class="fa-solid fa-plus"></i> Nuevo servicio
    </button>
@endsection

@section('contenido')

    <div class="rejilla rejilla--3 separado">
        <x-panel.card class="metrica">
            <span class="metrica__icono metrica__icono--azul"><i class="fa-solid fa-hand-holding-heart"></i></span>
            <p class="metrica__etiqueta">Servicios en total</p>
            <p class="metrica__valor">{{ $resumen['total'] }}</p>
        </x-panel.card>

        <x-panel.card class="metrica">
            <span class="metrica__icono metrica__icono--verde"><i class="fa-solid fa-eye"></i></span>
            <p class="metrica__etiqueta">Visibles en la web</p>
            <p class="metrica__valor">{{ $resumen['activos'] }}</p>
        </x-panel.card>

        <x-panel.card class="metrica">
            <span class="metrica__icono metrica__icono--gris"><i class="fa-solid fa-eye-slash"></i></span>
            <p class="metrica__etiqueta">Ocultos</p>
            <p class="metrica__valor">{{ $resumen['ocultos'] }}</p>
        </x-panel.card>
    </div>

    @if ($resumen['total'] > 0)
        <x-panel.card titulo="Buscar" icono="fa-filter" class="separado">
            <form method="GET" action="{{ route('panel.servicios.index') }}" class="filtros">
                <div class="filtros__busqueda">
                    <x-panel.campo nombre="q" etiqueta="Buscar" :valor="$filtros['q'] ?? null"
                                   placeholder="Titulo o descripcion del servicio" />

                    <div class="filtros__botones">
                        <button class="boton" type="submit">
                            <i class="fa-solid fa-magnifying-glass"></i> Buscar
                        </button>
                        <a class="boton boton--secundario" href="{{ route('panel.servicios.index') }}">
                            <i class="fa-solid fa-rotate-left"></i> Limpiar
                        </a>
                    </div>
                </div>
            </form>
        </x-panel.card>
    @endif

    <x-panel.card titulo="Tus servicios" icono="fa-hand-holding-heart"
                  :descripcion="$servicios->isEmpty() ? null : 'Con las flechas cambias el orden en el que se ven en tu web.'">

        @if ($servicios->isEmpty())
            @if ($resumen['total'] > 0)
                <x-panel.empty-state icono="fa-magnifying-glass" titulo="Ningun servicio coincide"
                                     mensaje="Prueba con otras palabras o limpia la busqueda para verlos todos.">
                    <a class="boton boton--secundario" href="{{ route('panel.servicios.index') }}">
                        <i class="fa-solid fa-rotate-left"></i> Limpiar busqueda
                    </a>
                </x-panel.empty-state>
            @else
                <x-panel.empty-state icono="fa-hand-holding-heart" titulo="Todavia no hay servicios"
                                     mensaje="Anade lo que ofreces: terapia individual, terapia de pareja, orientacion a familias...">
                    <button class="boton" type="button" data-nuevo>
                        <i class="fa-solid fa-plus"></i> Crear el primero
                    </button>
                </x-panel.empty-state>
            @endif
        @else
            <ol class="ordenable">
                @foreach ($servicios as $servicio)
                    @php
                        $datosServicio = json_encode([
                            'id' => $servicio->id,
                            'titulo' => $servicio->titulo,
                            'descripcion' => $servicio->descripcion,
                            'icono' => $servicio->icono,
                            'activo' => $servicio->activo,
                            'urlImagen' => $servicio->url_imagen,
                        ]);
                    @endphp

                    <li class="ordenable__fila {{ $servicio->activo ? '' : 'ordenable__fila--oculta' }}">

                        @include('panel.partials.flechas-orden', [
                            'rutaMover' => 'panel.servicios.mover',
                            'registro' => $servicio,
                            'primero' => $loop->first,
                            'ultimo' => $loop->last,
                            'posicion' => $loop->iteration,
                        ])

                        <div class="ordenable__cuerpo">
                            <p class="ordenable__titulo">
                                <span class="ordenable__icono">
                                    <i class="fa-solid {{ $servicio->icono_visible }}" aria-hidden="true"></i>
                                </span>
                                {{ $servicio->titulo }}
                            </p>

                            @if (filled($servicio->descripcion))
                                <div class="contenido-rico contenido-rico--compacto">{!! $servicio->descripcion !!}</div>
                            @else
                                <p class="celda__secundario celda__secundario--cursiva">
                                    Este servicio todavia no tiene descripcion.
                                </p>
                            @endif
                        </div>

                        @if ($servicio->url_imagen)
                            <img class="ordenable__imagen" src="{{ $servicio->url_imagen }}"
                                 alt="Imagen del servicio {{ $servicio->titulo }}">
                        @endif

                        <div class="ordenable__acciones">
                            <form method="POST" action="{{ route('panel.servicios.activo', $servicio) }}">
                                @csrf
                                @method('PATCH')
                                <button class="boton boton--secundario boton--icono" type="submit"
                                        aria-label="{{ $servicio->activo ? 'Ocultar de la web' : 'Mostrar en la web' }}"
                                        title="{{ $servicio->activo ? 'Se ve en tu web. Pulsa para ocultarlo' : 'Oculto. Pulsa para mostrarlo' }}">
                                    <i class="fa-solid {{ $servicio->activo ? 'fa-eye' : 'fa-eye-slash' }}"></i>
                                </button>
                            </form>

                            <button class="boton boton--secundario boton--icono" type="button"
                                    data-editar data-item="{{ $datosServicio }}"
                                    aria-label="Editar el servicio" title="Editar">
                                <i class="fa-solid fa-pen"></i>
                            </button>

                            <x-panel.confirmar-borrado :accion="route('panel.servicios.destroy', $servicio)"
                                                       titulo="Eliminar servicio"
                                                       :mensaje="'Se borrara para siempre el servicio: ' . $servicio->titulo"
                                                       etiqueta="Eliminar servicio" solo-icono />
                        </div>

                    </li>
                @endforeach
            </ol>
        @endif

    </x-panel.card>

    <x-panel.modal id="modal-servicio" titulo="Nuevo servicio" icono="fa-hand-holding-heart" ancho="ancho">
        <form method="POST" action="{{ route('panel.servicios.store') }}" id="form-servicio"
              enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="_method" value="POST" id="servicio-metodo">

            <x-panel.campo nombre="titulo" id="servicio-titulo" etiqueta="Titulo del servicio" maxlength="255"
                           placeholder="Por ejemplo: Terapia individual" obligatorio />

            <div class="campo">
                <label class="campo__etiqueta" for="servicio-descripcion">Descripcion</label>

                <textarea class="campo__control editor-rico @error('descripcion') campo__control--error @enderror"
                          id="servicio-descripcion" name="descripcion" data-editor="compacto"
                          placeholder="Explica en que consiste y a quien va dirigido">{{ old('descripcion') }}</textarea>

                @error('descripcion')
                    <p class="campo__error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p>
                @enderror
            </div>

            @include('panel.partials.selector-iconos', ['iconoActual' => old('icono')])

            <div class="imagen-servicio" data-imagen-actual hidden>
                <img class="imagen-servicio__vista" data-imagen-vista src="" alt="Imagen actual del servicio">

                <label class="casilla" for="servicio-eliminar-imagen">
                    <input type="checkbox" name="eliminar_imagen" id="servicio-eliminar-imagen" value="1">
                    <span>Quitar la imagen actual al guardar</span>
                </label>
            </div>

            <x-panel.campo nombre="imagen" id="servicio-imagen" etiqueta="Imagen (opcional)" tipo="file"
                           accept="image/jpeg,image/png,image/webp"
                           ayuda="JPG, PNG o WEBP. Maximo 2 MB. Si no subes ninguna se usara el icono." />

            <x-panel.toggle nombre="activo" id="servicio-activo" activo
                            etiqueta="Mostrar este servicio en mi web" />
        </form>

        <x-slot:pie>
            <button class="boton boton--secundario" type="button" data-cerrar-modal>Cancelar</button>
            <button class="boton" type="submit" form="form-servicio">
                <i class="fa-solid fa-floppy-disk"></i> Guardar servicio
            </button>
        </x-slot:pie>
    </x-panel.modal>

@endsection

@include('panel.partials.assets-editor')

@push('estilos')
    <link rel="stylesheet" href="{{ recurso('panel/css/citas.css') }}">
    <link rel="stylesheet" href="{{ recurso('panel/css/contenido.css') }}">
@endpush

@push('scripts')
    @php
        $datosServicios = [
            'urlCrear' => route('panel.servicios.store'),
            'urlBase' => url('/panel-psicologa/servicios'),
            'modal' => 'modal-servicio',
            'formulario' => 'form-servicio',
            'metodo' => 'servicio-metodo',
            'editor' => 'servicio-descripcion',
            'tituloNuevo' => 'Nuevo servicio',
            'tituloEditar' => 'Editar servicio',
            'errores' => $errors->any(),
        ];
    @endphp

    <script id="datos-catalogo" type="application/json">@json($datosServicios)</script>
    <script src="{{ recurso('panel/js/catalogo.js') }}"></script>
@endpush
