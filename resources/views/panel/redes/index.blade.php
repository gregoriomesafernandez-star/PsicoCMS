@extends('panel.layouts.app')

@section('titulo', 'Redes sociales')
@section('encabezado', 'Tus redes sociales')
@section('subtitulo', 'Los iconos que apareceran en el pie de tu web. Solo se ven las que tengan direccion.')

@section('migas')
    <x-panel.migas :items="[
        ['texto' => 'Inicio', 'url' => route('panel.inicio')],
        ['texto' => 'Redes sociales'],
    ]" />
@endsection

@section('acciones')
    <button class="boton" type="submit" form="form-redes">
        <i class="fa-solid fa-floppy-disk"></i> Guardar redes
    </button>
@endsection

@section('contenido')

    @unless (modulo_activo('redes'))
        <x-panel.aviso tono="atencion" titulo="Las redes estan apagadas" class="separado">
            <p>Puedes configurarlas, pero no se veran en tu web hasta que enciendas el modulo
                <a href="{{ route('panel.modulos') }}">Redes sociales</a>.</p>
        </x-panel.aviso>
    @endunless

    <div class="rejilla rejilla--3 separado">
        <x-panel.card class="metrica">
            <span class="metrica__icono metrica__icono--azul"><i class="fa-solid fa-share-nodes"></i></span>
            <p class="metrica__etiqueta">Redes disponibles</p>
            <p class="metrica__valor">{{ $resumen['total'] }}</p>
        </x-panel.card>

        <x-panel.card class="metrica">
            <span class="metrica__icono metrica__icono--verde"><i class="fa-solid fa-eye"></i></span>
            <p class="metrica__etiqueta">Se ven en tu web</p>
            <p class="metrica__valor">{{ $resumen['visibles'] }}</p>
        </x-panel.card>

        <x-panel.card class="metrica">
            <span class="metrica__icono metrica__icono--gris"><i class="fa-solid fa-link-slash"></i></span>
            <p class="metrica__etiqueta">Sin configurar</p>
            <p class="metrica__valor">{{ $resumen['sin_configurar'] }}</p>
        </x-panel.card>
    </div>

    <form method="POST" action="{{ route('panel.redes.update') }}" id="form-redes">
        @csrf
        @method('PUT')

        <x-panel.card titulo="Direcciones de tus perfiles" icono="fa-share-nodes"
                      descripcion="Pega la direccion completa de cada perfil. Deja vacias las que no uses."
                      class="separado">

            <ul class="redes">
                @foreach ($redes as $red)
                    @php [$nombre, $icono, $ejemplo] = $catalogo[$red->red]; @endphp

                    <li class="red {{ $red->activa && filled($red->url) ? 'red--visible' : '' }}">

                        <span class="red__icono red__icono--{{ $red->red }}">
                            <i class="fa-brands {{ $icono }}" aria-hidden="true"></i>
                        </span>

                        <div class="red__campo">
                            <label class="campo__etiqueta" for="red-{{ $red->red }}">{{ $nombre }}</label>

                            <input class="campo__control @error('urls.' . $red->red) campo__control--error @enderror"
                                   type="url" id="red-{{ $red->red }}" name="urls[{{ $red->red }}]"
                                   value="{{ old('urls.' . $red->red, $red->url) }}"
                                   placeholder="{{ $ejemplo }}" maxlength="255">

                            @error('urls.' . $red->red)
                                <p class="campo__error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p>
                            @enderror
                        </div>

                        <div class="red__visibilidad">
                            <x-panel.toggle nombre="activas[]" :valor="$red->red" :id="'activa-' . $red->red"
                                            :activo="$red->activa" etiqueta="Mostrar" />
                        </div>

                    </li>
                @endforeach
            </ul>

        </x-panel.card>
    </form>

    <x-panel.card titulo="Asi se veran en tu web" icono="fa-desktop"
                  descripcion="Vista previa del pie de pagina.">

        @if ($visibles->isEmpty())
            <x-panel.empty-state icono="fa-share-nodes" titulo="Todavia no se ve ninguna"
                                 mensaje="Pega la direccion de tus perfiles y marca 'Mostrar' para que aparezcan aqui." />
        @else
            <div class="redes-previa">
                <p class="redes-previa__texto">Sigueme en:</p>

                <ul class="redes-previa__lista">
                    @foreach ($visibles as $red)
                        @php [$nombre, $icono] = $catalogo[$red->red]; @endphp

                        <li>
                            <a class="redes-previa__enlace" href="{{ $red->url }}"
                               target="_blank" rel="noopener" title="{{ $nombre }}">
                                <i class="fa-brands {{ $icono }}" aria-hidden="true"></i>
                                <span class="visualmente-oculto">{{ $nombre }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

    </x-panel.card>

@endsection

@push('estilos')
    <link rel="stylesheet" href="{{ recurso('panel/css/citas.css') }}">
    <link rel="stylesheet" href="{{ recurso('panel/css/frases.css') }}">
@endpush
