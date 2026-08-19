@extends('panel.layouts.app')

@section('titulo', 'Documentos')
@section('encabezado', 'Documentos de proteccion de datos')
@section('subtitulo', 'Escribe una vez tu plantilla y descargala rellena para cada paciente.')

@section('migas')
    <x-panel.migas :items="[
        ['texto' => 'Inicio', 'url' => route('panel.inicio')],
        ['texto' => 'Documentos'],
    ]" />
@endsection

@section('acciones')
    <form method="POST" action="{{ route('panel.documentos.restaurar') }}"
          data-confirmar
          data-confirmar-titulo="Restaurar la plantilla original"
          data-confirmar-mensaje="Se sustituira tu plantilla por la que trae PsicoCMS. Lo que hayas escrito se perdera."
          data-confirmar-boton="Si, restaurar"
          data-confirmar-icono="fa-rotate-left">
        @csrf
        @method('PUT')

        <button class="boton boton--secundario" type="submit"
                title="Vuelve al texto original de PsicoCMS. Perderas los cambios que hayas hecho">
            <i class="fa-solid fa-rotate-left"></i> Restaurar plantilla
        </button>
    </form>

    <a class="boton boton--secundario" href="{{ route('panel.documentos.plantilla') }}"
       title="Descarga el documento con una linea en blanco en cada campo, para rellenarlo a mano">
        <i class="fa-solid fa-file-pdf"></i> Descargar plantilla vacia
    </a>

    <button class="boton" type="submit" form="form-plantilla">
        <i class="fa-solid fa-floppy-disk"></i> Guardar plantilla
    </button>
@endsection

@section('contenido')

    @if ($errors->any())
        <x-panel.aviso tono="error" titulo="Revisa la plantilla" class="separado">
            <p>{{ $errors->first('contenido') }}</p>
        </x-panel.aviso>
    @endif

    <x-panel.aviso tono="info" titulo="Como funciona" class="separado">
        <p>Escribe el documento una sola vez usando los <strong>campos automaticos</strong> de abajo.
            Cuando lo descargues desde la ficha de un paciente, cada campo se sustituira por sus datos
            reales. La plantilla vacia deja una linea en blanco en su lugar, para rellenarla a mano.</p>
    </x-panel.aviso>

    <x-panel.card titulo="Campos automaticos" icono="fa-wand-magic-sparkles"
                  descripcion="Haz click en uno para insertarlo donde tengas el cursor."
                  class="separado">

        @foreach ($grupos as $grupo => $campos)
            <div class="campos__grupo">
                <p class="campos__titulo">{{ $grupo }}</p>

                <ul class="campos__lista">
                    @foreach ($campos as $campo)
                        <li>
                            <button class="campo-auto" type="button" data-insertar="{{ $campo['token'] }}">
                                <code class="campo-auto__clave">{{ $campo['token'] }}</code>
                                <span class="campo-auto__texto">{{ $campo['texto'] }}</span>
                            </button>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach

    </x-panel.card>

    <x-panel.card titulo="Plantilla de proteccion de datos" icono="fa-file-shield">
        <x-slot:acciones>
            <x-panel.badge tono="neutro" icono="fa-clock">
                Guardada el {{ $plantilla->updated_at->format('d/m/Y') }}
            </x-panel.badge>
        </x-slot:acciones>

        <form method="POST" action="{{ route('panel.documentos.update') }}" id="form-plantilla">
            @csrf
            @method('PUT')

            <div class="campo">
                <label class="campo__etiqueta" for="contenido">Contenido del documento</label>

                <textarea class="campo__control editor-rico @error('contenido') campo__control--error @enderror"
                          id="contenido" name="contenido"
                          placeholder="Escribe aqui el texto de tu documento de proteccion de datos">{{ old('contenido', $plantilla->contenido) }}</textarea>

                @error('contenido')
                    <p class="campo__error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p>
                @enderror
            </div>
        </form>
    </x-panel.card>

@endsection

@include('panel.partials.assets-editor')

@push('estilos')
    <link rel="stylesheet" href="{{ asset('panel/css/citas.css') }}">
    <link rel="stylesheet" href="{{ asset('panel/css/documentos.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('panel/js/documentos.js') }}"></script>
@endpush
