@extends('panel.layouts.app')

@section('titulo', 'Frases publicas')
@section('encabezado', 'Frases de tu web')
@section('subtitulo', 'Los textos fijos que trae el tema. Cambia los que quieras; el resto se quedan como estan.')

@section('migas')
    <x-panel.migas :items="[
        ['texto' => 'Inicio', 'url' => route('panel.inicio')],
        ['texto' => 'Frases publicas'],
    ]" />
@endsection

@section('acciones')
    @if ($resumen['personalizadas'] > 0)
        <form method="POST" action="{{ route('panel.frases.restaurar-todas') }}"
              data-confirmar
              data-confirmar-titulo="Restaurar todas las frases"
              data-confirmar-mensaje="Se perderan las {{ $resumen['personalizadas'] }} frases que has escrito y volveran los textos originales del tema."
              data-confirmar-boton="Si, restaurar todas"
              data-confirmar-icono="fa-rotate-left">
            @csrf
            @method('PUT')
            <button class="boton boton--secundario" type="submit">
                <i class="fa-solid fa-rotate-left"></i> Restaurar todas
            </button>
        </form>
    @endif

    <button class="boton" type="submit" form="form-frases">
        <i class="fa-solid fa-floppy-disk"></i> Guardar frases
    </button>
@endsection

@section('contenido')

    <div class="rejilla rejilla--3 separado">
        <x-panel.card class="metrica">
            <span class="metrica__icono metrica__icono--azul"><i class="fa-solid fa-quote-left"></i></span>
            <p class="metrica__etiqueta">Frases en total</p>
            <p class="metrica__valor">{{ $resumen['total'] }}</p>
        </x-panel.card>

        <x-panel.card class="metrica">
            <span class="metrica__icono metrica__icono--verde"><i class="fa-solid fa-pen"></i></span>
            <p class="metrica__etiqueta">Escritas por ti</p>
            <p class="metrica__valor">{{ $resumen['personalizadas'] }}</p>
        </x-panel.card>

        <x-panel.card class="metrica">
            <span class="metrica__icono metrica__icono--gris"><i class="fa-solid fa-palette"></i></span>
            <p class="metrica__etiqueta">Con el texto del tema</p>
            <p class="metrica__valor">{{ $resumen['por_defecto'] }}</p>
        </x-panel.card>
    </div>

    <x-panel.aviso tono="info" titulo="Como funciona" class="separado">
        <p>Cada frase trae el texto que pone el tema. Si escribes uno tuyo, se usa el tuyo. Si borras lo
            que escribiste y guardas, vuelve el original sin tener que hacer nada mas.</p>
    </x-panel.aviso>

    @if (empty($grupos))
        <x-panel.card titulo="Frases" icono="fa-quote-left">
            <x-panel.empty-state icono="fa-quote-left" titulo="No hay frases todavia"
                                 mensaje="Las frases las siembra la instalacion. Si no ves ninguna, vuelve a ejecutar el seeder." />
        </x-panel.card>
    @else
        <form method="POST" action="{{ route('panel.frases.update') }}" id="form-frases">
            @csrf
            @method('PUT')

            @foreach ($grupos as $clave => $grupo)
                <x-panel.card :titulo="$grupo['nombre']" :icono="$grupo['icono']"
                              :descripcion="$grupo['descripcion']" class="separado">

                    <ul class="frases">
                        @foreach ($grupo['frases'] as $frase)
                            @php $personalizada = filled($frase->texto_personalizado); @endphp

                            <li class="frase {{ $personalizada ? 'frase--propia' : '' }}">

                                <div class="frase__campo">
                                    <label class="campo__etiqueta" for="frase-{{ $frase->id }}">
                                        {{ $frase->texto_defecto }}
                                        @if ($personalizada)
                                            <span class="frase__marca">
                                                <i class="fa-solid fa-pen" aria-hidden="true"></i> Cambiada
                                            </span>
                                        @endif
                                    </label>

                                    <input class="campo__control" type="text"
                                           id="frase-{{ $frase->id }}"
                                           name="frases[{{ $frase->clave }}]"
                                           value="{{ old('frases.' . $frase->clave, $frase->texto_personalizado) }}"
                                           placeholder="{{ $frase->texto_defecto }}"
                                           maxlength="500">

                                    <p class="frase__clave">{{ $frase->clave }}</p>
                                </div>

                                @if ($personalizada)
                                    <button class="boton boton--secundario boton--icono" type="submit"
                                            form="restaurar-{{ $frase->id }}"
                                            aria-label="Volver al texto original" title="Volver al texto original">
                                        <i class="fa-solid fa-rotate-left"></i>
                                    </button>
                                @endif

                            </li>
                        @endforeach
                    </ul>

                </x-panel.card>
            @endforeach
        </form>

        {{-- Fuera del formulario grande: un form no puede contener otro. --}}
        @foreach ($grupos as $grupo)
            @foreach ($grupo['frases'] as $frase)
                @if (filled($frase->texto_personalizado))
                    <form method="POST" action="{{ route('panel.frases.restaurar', $frase) }}"
                          id="restaurar-{{ $frase->id }}" class="oculto">
                        @csrf
                        @method('PUT')
                    </form>
                @endif
            @endforeach
        @endforeach
    @endif

@endsection

@push('estilos')
    <link rel="stylesheet" href="{{ recurso('panel/css/citas.css') }}">
    <link rel="stylesheet" href="{{ recurso('panel/css/frases.css') }}">
@endpush
