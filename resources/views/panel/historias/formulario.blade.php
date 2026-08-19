@extends('panel.layouts.app')

@php
    $esNueva = ! $historia->exists;
    $accion = $esNueva ? route('panel.historias.store') : route('panel.historias.update', $historia);
    $paciente = $historia->paciente;
    $volver = $paciente
        ? route('panel.historias.index', ['paciente' => $paciente->id])
        : route('panel.historias.index');
@endphp

@section('titulo', $esNueva ? 'Nueva entrada' : 'Editar entrada')
@section('encabezado', $esNueva ? 'Nueva entrada de sesion' : 'Editar entrada de sesion')
@section('subtitulo', $esNueva
    ? 'Anota como ha ido la sesion y adjunta las fotos o PDF que necesites.'
    : 'Modifica lo que escribiste y gestiona sus archivos adjuntos.')

@section('migas')
    <x-panel.migas :items="array_values(array_filter([
        ['texto' => 'Inicio', 'url' => route('panel.inicio')],
        ['texto' => 'Historias', 'url' => route('panel.historias.index')],
        $paciente ? ['texto' => $paciente->nombre_completo, 'url' => route('panel.historias.index', ['paciente' => $paciente->id])] : null,
        ['texto' => $esNueva ? 'Nueva entrada' : 'Editar'],
    ]))" />
@endsection

@section('acciones')
    <a class="boton boton--secundario" href="{{ $volver }}">
        <i class="fa-solid fa-arrow-left"></i> Volver
    </a>
@endsection

@section('contenido')

    @if ($errors->any())
        <x-panel.aviso tono="error" titulo="Revisa el formulario" class="separado">
            <p>Hay {{ $errors->count() }} campo(s) con errores. Estan marcados en rojo mas abajo.</p>
        </x-panel.aviso>
    @endif

    <form method="POST" action="{{ $accion }}" enctype="multipart/form-data" id="form-historia">
        @csrf
        @unless ($esNueva)
            @method('PUT')
        @endunless

        <div class="editor-entrada">

            <div class="editor-entrada__principal">

                <x-panel.card titulo="Como ha ido la sesion" icono="fa-pen-nib">
                    <x-panel.campo nombre="titulo" etiqueta="Titulo de la entrada"
                                   :valor="$historia->titulo" maxlength="150"
                                   placeholder="Por ejemplo: Primera sesion de evaluacion"
                                   ayuda="Opcional. Si lo dejas vacio se usara la fecha de la sesion." />

                    <div class="campo">
                        <label class="campo__etiqueta" for="contenido">Anotaciones de la sesion</label>

                        <textarea class="campo__control editor-rico @error('contenido') campo__control--error @enderror"
                                  id="contenido" name="contenido"
                                  placeholder="Escribe aqui lo trabajado en la sesion, los avances y lo que quieras recordar para la proxima">{{ old('contenido', $historia->contenido) }}</textarea>

                        @error('contenido')
                            <p class="campo__error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p>
                        @enderror

                        <p class="campo__ayuda">
                            <i class="fa-solid fa-lock"></i>
                            Este texto se guarda cifrado en la base de datos.
                        </p>
                    </div>
                </x-panel.card>

            </div>

            <aside class="editor-entrada__lateral">

                <x-panel.card titulo="Datos de la sesion" icono="fa-clipboard-list" class="separado">
                    <x-panel.campo nombre="paciente_id" etiqueta="Paciente" tipo="select"
                                   :valor="$historia->paciente_id"
                                   :opciones="['' => 'Elige un paciente'] + $pacientes" obligatorio />

                    <x-panel.campo nombre="fecha_sesion" etiqueta="Fecha de la sesion" tipo="date"
                                   :valor="$historia->fecha_sesion?->toDateString()"
                                   :max="now()->toDateString()" obligatorio />

                    <x-panel.campo nombre="cita_id" etiqueta="Vincular con una cita" tipo="select"
                                   :valor="$historia->cita_id"
                                   :opciones="['' => 'Sin vincular'] + $citas"
                                   ayuda="Opcional. Solo se muestran las citas de ese paciente." />

                    <div class="editor-entrada__acciones">
                        <button class="boton" type="submit">
                            <i class="fa-solid fa-floppy-disk"></i>
                            {{ $esNueva ? 'Guardar entrada' : 'Guardar cambios' }}
                        </button>
                    </div>
                </x-panel.card>

                <x-panel.card titulo="Archivos adjuntos" icono="fa-paperclip">

                    @if ($historia->exists && $historia->adjuntos->isNotEmpty())
                        <p class="campo__etiqueta">Ya adjuntados</p>

                        <ul class="adjuntos-lista">
                            @foreach ($historia->adjuntos as $adjunto)
                                <li class="adjunto-fila">
                                    <i class="fa-solid {{ $adjunto->icono }} adjunto-fila__icono"></i>

                                    <span class="adjunto-fila__cuerpo">
                                        <a class="adjunto-fila__nombre" target="_blank" rel="noopener"
                                           href="{{ route('panel.historias.adjuntos.ver', $adjunto) }}">
                                            {{ $adjunto->nombre_original }}
                                        </a>
                                        <span class="adjunto-fila__meta">
                                            {{ $adjunto->tipo_legible }} - {{ $adjunto->tamano_legible }}
                                        </span>
                                    </span>

                                    <label class="adjunto-fila__quitar" for="quitar-{{ $adjunto->id }}">
                                        <input type="checkbox" name="eliminar_adjuntos[]" value="{{ $adjunto->id }}"
                                               id="quitar-{{ $adjunto->id }}">
                                        <span>Quitar</span>
                                    </label>
                                </li>
                            @endforeach
                        </ul>

                        <p class="campo__ayuda">Los archivos marcados se borraran definitivamente al guardar.</p>
                    @endif

                    <div class="campo">
                        <label class="campo__etiqueta" for="adjuntos">Anadir archivos</label>

                        <input class="campo__control @error('adjuntos.*') campo__control--error @enderror"
                               type="file" id="adjuntos" name="adjuntos[]" multiple
                               accept="image/jpeg,image/png,image/webp,application/pdf">

                        @error('adjuntos')
                            <p class="campo__error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p>
                        @enderror

                        @error('adjuntos.*')
                            <p class="campo__error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p>
                        @enderror

                        <p class="campo__ayuda">
                            Fotos (JPG, PNG o WEBP) o documentos PDF escaneados.
                            Maximo 10 archivos de 8 MB cada uno.
                        </p>
                    </div>

                    <ul class="adjuntos-lista adjuntos-lista--elegidos" id="adjuntos-elegidos"></ul>

                </x-panel.card>

            </aside>

        </div>
    </form>

@endsection

@include('panel.partials.assets-editor')

@push('estilos')
    <link rel="stylesheet" href="{{ asset('panel/css/citas.css') }}">
    <link rel="stylesheet" href="{{ asset('panel/css/historias.css') }}">
@endpush

@push('scripts')
    @php
        $datosHistoria = ['urlCitas' => route('panel.historias.citas')];
    @endphp

    <script id="datos-historia" type="application/json">@json($datosHistoria)</script>
    <script src="{{ asset('panel/js/historias.js') }}"></script>
@endpush
