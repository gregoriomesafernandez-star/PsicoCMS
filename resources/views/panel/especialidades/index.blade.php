@extends('panel.layouts.app')

@section('titulo', 'Especialidades')
@section('encabezado', 'Especialidades')
@section('subtitulo', 'Los temas que trabajas en consulta, para que cada persona se reconozca en uno.')

@section('migas')
    <x-panel.migas :items="[
        ['texto' => 'Inicio', 'url' => route('panel.inicio')],
        ['texto' => 'Especialidades'],
    ]" />
@endsection

@section('acciones')
    <button class="boton" type="button" data-nuevo>
        <i class="fa-solid fa-plus"></i> Nueva especialidad
    </button>
@endsection

@section('contenido')

    <div class="rejilla rejilla--3 separado">
        <x-panel.card class="metrica">
            <span class="metrica__icono metrica__icono--azul"><i class="fa-solid fa-brain"></i></span>
            <p class="metrica__etiqueta">Especialidades en total</p>
            <p class="metrica__valor">{{ $resumen['total'] }}</p>
        </x-panel.card>

        <x-panel.card class="metrica">
            <span class="metrica__icono metrica__icono--verde"><i class="fa-solid fa-eye"></i></span>
            <p class="metrica__etiqueta">Visibles en la web</p>
            <p class="metrica__valor">{{ $resumen['activas'] }}</p>
        </x-panel.card>

        <x-panel.card class="metrica">
            <span class="metrica__icono metrica__icono--gris"><i class="fa-solid fa-eye-slash"></i></span>
            <p class="metrica__etiqueta">Ocultas</p>
            <p class="metrica__valor">{{ $resumen['ocultas'] }}</p>
        </x-panel.card>
    </div>

    @if ($resumen['total'] > 0)
        <x-panel.card titulo="Buscar" icono="fa-filter" class="separado">
            <form method="GET" action="{{ route('panel.especialidades.index') }}" class="filtros">
                <div class="filtros__busqueda">
                    <x-panel.campo nombre="q" etiqueta="Buscar" :valor="$filtros['q'] ?? null"
                                   placeholder="Nombre o descripcion de la especialidad" />

                    <div class="filtros__botones">
                        <button class="boton" type="submit">
                            <i class="fa-solid fa-magnifying-glass"></i> Buscar
                        </button>
                        <a class="boton boton--secundario" href="{{ route('panel.especialidades.index') }}">
                            <i class="fa-solid fa-rotate-left"></i> Limpiar
                        </a>
                    </div>
                </div>
            </form>
        </x-panel.card>
    @endif

    <x-panel.card titulo="Tus especialidades" icono="fa-brain"
                  :descripcion="$especialidades->isEmpty() ? null : 'Con las flechas cambias el orden en el que se ven en tu web.'">

        @if ($especialidades->isEmpty())
            @if ($resumen['total'] > 0)
                <x-panel.empty-state icono="fa-magnifying-glass" titulo="Ninguna especialidad coincide"
                                     mensaje="Prueba con otras palabras o limpia la busqueda para verlas todas.">
                    <a class="boton boton--secundario" href="{{ route('panel.especialidades.index') }}">
                        <i class="fa-solid fa-rotate-left"></i> Limpiar busqueda
                    </a>
                </x-panel.empty-state>
            @else
                <x-panel.empty-state icono="fa-brain" titulo="Todavia no hay especialidades"
                                     mensaje="Anade los temas que trabajas: ansiedad, autoestima, duelo, pareja...">
                    <button class="boton" type="button" data-nuevo>
                        <i class="fa-solid fa-plus"></i> Crear la primera
                    </button>

                    <form method="POST" action="{{ route('panel.especialidades.ejemplos') }}" class="vacio__formulario">
                        @csrf
                        <button class="boton boton--secundario" type="submit">
                            <i class="fa-solid fa-wand-magic-sparkles"></i> Anadir las mas habituales
                        </button>
                    </form>
                </x-panel.empty-state>
            @endif
        @else
            <ol class="ordenable">
                @foreach ($especialidades as $especialidad)
                    @php
                        $datosEspecialidad = json_encode([
                            'id' => $especialidad->id,
                            'nombre' => $especialidad->nombre,
                            'descripcion' => $especialidad->descripcion,
                            'icono' => $especialidad->icono,
                            'activo' => $especialidad->activo,
                        ]);
                    @endphp

                    <li class="ordenable__fila {{ $especialidad->activo ? '' : 'ordenable__fila--oculta' }}">

                        @include('panel.partials.flechas-orden', [
                            'rutaMover' => 'panel.especialidades.mover',
                            'registro' => $especialidad,
                            'primero' => $loop->first,
                            'ultimo' => $loop->last,
                            'posicion' => $loop->iteration,
                        ])

                        <div class="ordenable__cuerpo">
                            <p class="ordenable__titulo">
                                <span class="ordenable__icono">
                                    <i class="fa-solid {{ $especialidad->icono_visible }}" aria-hidden="true"></i>
                                </span>
                                {{ $especialidad->nombre }}
                            </p>

                            @if (filled($especialidad->descripcion))
                                <div class="contenido-rico contenido-rico--compacto">{!! $especialidad->descripcion !!}</div>
                            @else
                                <p class="celda__secundario celda__secundario--cursiva">
                                    Esta especialidad todavia no tiene descripcion.
                                </p>
                            @endif
                        </div>

                        <div class="ordenable__acciones">
                            <form method="POST" action="{{ route('panel.especialidades.activo', $especialidad) }}">
                                @csrf
                                @method('PATCH')
                                <button class="boton boton--secundario boton--icono" type="submit"
                                        aria-label="{{ $especialidad->activo ? 'Ocultar de la web' : 'Mostrar en la web' }}"
                                        title="{{ $especialidad->activo ? 'Se ve en tu web. Pulsa para ocultarla' : 'Oculta. Pulsa para mostrarla' }}">
                                    <i class="fa-solid {{ $especialidad->activo ? 'fa-eye' : 'fa-eye-slash' }}"></i>
                                </button>
                            </form>

                            <button class="boton boton--secundario boton--icono" type="button"
                                    data-editar data-item="{{ $datosEspecialidad }}"
                                    aria-label="Editar la especialidad" title="Editar">
                                <i class="fa-solid fa-pen"></i>
                            </button>

                            <x-panel.confirmar-borrado :accion="route('panel.especialidades.destroy', $especialidad)"
                                                       titulo="Eliminar especialidad"
                                                       :mensaje="'Se borrara para siempre la especialidad: ' . $especialidad->nombre"
                                                       etiqueta="Eliminar especialidad" solo-icono />
                        </div>

                    </li>
                @endforeach
            </ol>
        @endif

    </x-panel.card>

    <x-panel.modal id="modal-especialidad" titulo="Nueva especialidad" icono="fa-brain" ancho="ancho">
        <form method="POST" action="{{ route('panel.especialidades.store') }}" id="form-especialidad">
            @csrf
            <input type="hidden" name="_method" value="POST" id="especialidad-metodo">

            <x-panel.campo nombre="nombre" id="especialidad-nombre" etiqueta="Nombre de la especialidad"
                           maxlength="255" placeholder="Por ejemplo: Ansiedad y estres" obligatorio />

            <div class="campo">
                <label class="campo__etiqueta" for="especialidad-descripcion">Descripcion</label>

                <textarea class="campo__control editor-rico @error('descripcion') campo__control--error @enderror"
                          id="especialidad-descripcion" name="descripcion" data-editor="compacto"
                          placeholder="Explica como trabajas este tema en consulta">{{ old('descripcion') }}</textarea>

                @error('descripcion')
                    <p class="campo__error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p>
                @enderror
            </div>

            @include('panel.partials.selector-iconos', ['iconoActual' => old('icono')])

            <x-panel.toggle nombre="activo" id="especialidad-activo" activo
                            etiqueta="Mostrar esta especialidad en mi web" />
        </form>

        <x-slot:pie>
            <button class="boton boton--secundario" type="button" data-cerrar-modal>Cancelar</button>
            <button class="boton" type="submit" form="form-especialidad">
                <i class="fa-solid fa-floppy-disk"></i> Guardar especialidad
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
        $datosEspecialidades = [
            'urlCrear' => route('panel.especialidades.store'),
            'urlBase' => url('/panel-psicologa/especialidades'),
            'modal' => 'modal-especialidad',
            'formulario' => 'form-especialidad',
            'metodo' => 'especialidad-metodo',
            'editor' => 'especialidad-descripcion',
            'tituloNuevo' => 'Nueva especialidad',
            'tituloEditar' => 'Editar especialidad',
            'errores' => $errors->any(),
        ];
    @endphp

    <script id="datos-catalogo" type="application/json">@json($datosEspecialidades)</script>
    <script src="{{ recurso('panel/js/catalogo.js') }}"></script>
@endpush
