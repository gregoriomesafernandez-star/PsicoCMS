@extends('panel.layouts.app')

@section('titulo', 'Imagenes')
@section('encabezado', 'Imagenes de tu web')
@section('subtitulo', 'Cambia las fotos que se ven en tu web publica. Si no subes ninguna, se usa la del tema.')

@section('migas')
    <x-panel.migas :items="[
        ['texto' => 'Inicio', 'url' => route('panel.inicio')],
        ['texto' => 'Imagenes'],
    ]" />
@endsection

@section('contenido')

    <div class="rejilla rejilla--3 separado">
        <x-panel.card class="metrica">
            <span class="metrica__icono metrica__icono--azul"><i class="fa-solid fa-images"></i></span>
            <p class="metrica__etiqueta">Huecos de imagen</p>
            <p class="metrica__valor">{{ $resumen['total'] }}</p>
        </x-panel.card>

        <x-panel.card class="metrica">
            <span class="metrica__icono metrica__icono--verde"><i class="fa-solid fa-camera"></i></span>
            <p class="metrica__etiqueta">Con foto tuya</p>
            <p class="metrica__valor">{{ $resumen['personalizadas'] }}</p>
        </x-panel.card>

        <x-panel.card class="metrica">
            <span class="metrica__icono metrica__icono--gris"><i class="fa-solid fa-palette"></i></span>
            <p class="metrica__etiqueta">Con la del tema</p>
            <p class="metrica__valor">{{ $resumen['de_stock'] }}</p>
        </x-panel.card>
    </div>

    <x-panel.aviso tono="info" titulo="Como funciona" class="separado">
        <p>Cada hueco tiene una imagen de ejemplo del tema <strong>{{ $tema['nombre'] }}</strong>. En cuanto
            subes la tuya, tiene prioridad y es la que se ve en la web. Puedes volver a la original cuando
            quieras sin perder nada.</p>
    </x-panel.aviso>

    @foreach ($grupos as $seccion => $imagenes)
        <x-panel.card :titulo="$seccion" icono="fa-images" class="separado">

            <div class="huecos">
                @foreach ($imagenes as $hueco)
                    <article class="hueco {{ $hueco['personalizada'] ? 'hueco--propia' : '' }}">

                        <div class="hueco__vista {{ $hueco['transparente'] ? '' : 'hueco__vista--foto' }}">
                            <img class="hueco__imagen" src="{{ $hueco['url'] }}"
                                 alt="{{ $hueco['modelo']->alt ?: $hueco['nombre'] }}" loading="lazy">

                            <span class="hueco__etiqueta">
                                @if ($hueco['personalizada'])
                                    <i class="fa-solid fa-circle-check" aria-hidden="true"></i> Tu imagen
                                @else
                                    <i class="fa-solid fa-palette" aria-hidden="true"></i> Del tema
                                @endif
                            </span>

                            <button class="hueco__lupa" type="button"
                                    data-ver-imagen="{{ $hueco['url'] }}"
                                    data-titulo="{{ $hueco['nombre'] }}"
                                    aria-label="Ver la imagen a tamano completo"
                                    title="Ver a tamano completo">
                                <i class="fa-solid fa-magnifying-glass-plus" aria-hidden="true"></i>
                            </button>
                        </div>

                        <div class="hueco__cuerpo">
                            <h3 class="hueco__nombre">{{ $hueco['nombre'] }}</h3>
                            <p class="hueco__descripcion">{{ $hueco['descripcion'] }}</p>

                            {{-- El formulario de restaurar no puede anidarse dentro de este,
                                 asi que el boton de guardar lo referencia con el atributo form. --}}
                            <form method="POST" action="{{ route('panel.imagenes.update', $hueco['modelo']) }}"
                                  enctype="multipart/form-data" id="form-{{ $hueco['clave'] }}">
                                @csrf

                                <x-panel.campo nombre="imagen" :id="'imagen-' . $hueco['clave']"
                                               etiqueta="Cambiar imagen" tipo="file"
                                               accept="image/jpeg,image/png,image/webp"
                                               ayuda="JPG, PNG o WEBP. Maximo 4 MB."
                                               :data-vista-previa="$hueco['clave']" />

                                <x-panel.campo nombre="alt" :id="'alt-' . $hueco['clave']"
                                               etiqueta="Que se ve en la imagen"
                                               :valor="$hueco['modelo']->alt" maxlength="255"
                                               ayuda="Lo leen Google y las personas con lectores de pantalla." />
                            </form>

                            <div class="hueco__acciones">
                                <button class="boton boton--pequeno" type="submit"
                                        form="form-{{ $hueco['clave'] }}">
                                    <i class="fa-solid fa-floppy-disk"></i> Guardar
                                </button>

                                @if ($hueco['personalizada'])
                                    <form method="POST" action="{{ route('panel.imagenes.restaurar', $hueco['modelo']) }}"
                                          data-confirmar
                                          data-confirmar-titulo="Volver a la imagen original"
                                          data-confirmar-mensaje="Se borrara la imagen que subiste para '{{ $hueco['nombre'] }}' y se volvera a la del tema {{ $tema['nombre'] }}."
                                          data-confirmar-boton="Si, volver a la original"
                                          data-confirmar-icono="fa-rotate-left">
                                        @csrf
                                        @method('DELETE')
                                        <button class="boton boton--secundario boton--pequeno" type="submit">
                                            <i class="fa-solid fa-rotate-left"></i> Volver a la original
                                        </button>
                                    </form>
                                @else
                                    <p class="hueco__estado">
                                        <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                                        Estas usando la imagen del tema. Si subes la tuya, aqui podras volver a esta.
                                    </p>
                                @endif
                            </div>
                        </div>

                    </article>
                @endforeach
            </div>

        </x-panel.card>
    @endforeach

    <x-panel.modal id="modal-imagen" titulo="Vista de la imagen" icono="fa-image" ancho="ancho">
        <figure class="visor">
            <img class="visor__imagen" id="visor-imagen" src="" alt="">
        </figure>

        <x-slot:pie>
            <button class="boton boton--secundario" type="button" data-cerrar-modal>Cerrar</button>
            <a class="boton" id="visor-abrir" href="#" target="_blank" rel="noopener">
                <i class="fa-solid fa-arrow-up-right-from-square"></i> Abrir en otra pestana
            </a>
        </x-slot:pie>
    </x-panel.modal>

@endsection

@push('estilos')
    <link rel="stylesheet" href="{{ recurso('panel/css/citas.css') }}">
    <link rel="stylesheet" href="{{ recurso('panel/css/imagenes.css') }}">
@endpush

@push('scripts')
    <script src="{{ recurso('panel/js/imagenes.js') }}"></script>
@endpush
