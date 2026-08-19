@extends('panel.layouts.app')

@section('titulo', 'Temas')
@section('encabezado', 'Temas de tu web')
@section('subtitulo', 'Elige el aspecto de tu web publica. Puedes cambiarlo cuando quieras, sin perder nada.')

@section('migas')
    <x-panel.migas :items="[
        ['texto' => 'Inicio', 'url' => route('panel.inicio')],
        ['texto' => 'Temas'],
    ]" />
@endsection

@section('acciones')
    <a class="boton boton--secundario" href="{{ $urlDisenoPersonalizado }}" target="_blank" rel="noopener">
        <i class="fa-solid fa-wand-magic-sparkles"></i> ¿Quieres un diseno personalizado? Pidemelo aqui
    </a>
@endsection

@section('contenido')

    <x-panel.card titulo="Formato de tu web" icono="fa-table-columns"
                  descripcion="Decide si toda la informacion va en una sola pagina o repartida en varias."
                  class="separado">

        <form method="POST" action="{{ route('panel.temas.modo') }}" class="modos">
            @csrf
            @method('PUT')

            @foreach ([
                'landing' => ['Una sola pagina', 'fa-scroll', 'Todo en la portada, con scroll suave y menu de anclas. Es lo mas directo para pedir cita.'],
                'multipagina' => ['Varias paginas', 'fa-copy', 'Una direccion por seccion: sobre mi, servicios, blog... Mejor si tienes mucho contenido.'],
            ] as $clave => [$titulo, $icono, $explicacion])
                <label class="modo {{ $modo === $clave ? 'modo--activo' : '' }}">
                    <input class="modo__entrada" type="radio" name="modo" value="{{ $clave }}"
                           @checked($modo === $clave)>

                    <span class="modo__cara">
                        <span class="modo__icono"><i class="fa-solid {{ $icono }}" aria-hidden="true"></i></span>

                        <span class="modo__texto">
                            <span class="modo__titulo">{{ $titulo }}</span>
                            <span class="modo__explicacion">{{ $explicacion }}</span>
                        </span>

                        @if ($modo === $clave)
                            <span class="modo__marca"><i class="fa-solid fa-circle-check" aria-hidden="true"></i></span>
                        @endif
                    </span>
                </label>
            @endforeach

            <div class="modos__accion">
                <button class="boton" type="submit">
                    <i class="fa-solid fa-floppy-disk"></i> Guardar formato
                </button>
            </div>
        </form>

        @error('modo')
            <p class="campo__error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p>
        @enderror

    </x-panel.card>

    <x-panel.card titulo="Elige tu tema" icono="fa-palette"
                  :descripcion="count($temas) . ' temas disponibles. Pulsa Ver como queda para abrirlo en otra pestana con tus datos reales.'">

        @if (empty($temas))
            <x-panel.empty-state icono="fa-palette" titulo="No hay temas instalados"
                                 mensaje="Cada tema es una carpeta dentro de public/themes con su theme.json." />
        @else
            <div class="temas">
                @foreach ($temas as $slug => $tema)
                    @php $esActivo = $slug === $activo; @endphp

                    <article class="tema {{ $esActivo ? 'tema--activo' : '' }}">

                        <div class="tema__muestra" style="background-color: {{ $tema['paleta']['fondo'] }}">
                            <span class="tema__barra" style="background-color: {{ $tema['paleta']['primario'] }}"></span>

                            <span class="tema__titulo-falso" style="color: {{ $tema['paleta']['texto'] }}">
                                {{ $tema['nombre'] }}
                            </span>

                            <span class="tema__boton-falso"
                                  style="background-color: {{ $tema['paleta']['primario'] }}">Pide tu cita</span>

                            <span class="tema__lineas">
                                <span style="background-color: {{ $tema['paleta']['acento'] }}"></span>
                                <span style="background-color: {{ $tema['paleta']['acento'] }}"></span>
                            </span>

                            @if ($esActivo)
                                <span class="tema__insignia">
                                    <i class="fa-solid fa-circle-check" aria-hidden="true"></i> Activo
                                </span>
                            @endif
                        </div>

                        <div class="tema__cuerpo">
                            <h3 class="tema__nombre">{{ $tema['nombre'] }}</h3>
                            <p class="tema__descripcion">{{ $tema['descripcion'] }}</p>

                            <ul class="tema__paleta" aria-label="Colores del tema {{ $tema['nombre'] }}">
                                @foreach ($tema['paleta'] as $rol => $color)
                                    <li class="tema__color" style="background-color: {{ $color }}"
                                        title="{{ ucfirst($rol) }}: {{ $color }}">
                                        <span class="visualmente-oculto">{{ ucfirst($rol) }}: {{ $color }}</span>
                                    </li>
                                @endforeach
                            </ul>

                            <p class="tema__tipografia">
                                <i class="fa-solid fa-font" aria-hidden="true"></i> {{ $tema['tipografia'] }}
                            </p>
                        </div>

                        <div class="tema__acciones">
                            <a class="boton boton--secundario"
                               href="{{ route('panel.temas.previsualizar', ['slug' => $slug, 'modo' => $modo]) }}"
                               target="_blank" rel="noopener">
                                <i class="fa-solid fa-eye"></i> Ver como queda
                            </a>

                            @if ($esActivo)
                                <span class="boton boton--inactivo" aria-disabled="true">
                                    <i class="fa-solid fa-check"></i> Tema activo
                                </span>
                            @else
                                <form method="POST" action="{{ route('panel.temas.activar', $slug) }}">
                                    @csrf
                                    @method('PUT')
                                    <button class="boton" type="submit">
                                        <i class="fa-solid fa-wand-magic-sparkles"></i> Usar este tema
                                    </button>
                                </form>
                            @endif
                        </div>

                    </article>
                @endforeach
            </div>
        @endif

    </x-panel.card>

@endsection

@push('estilos')
    <link rel="stylesheet" href="{{ recurso('panel/css/temas.css') }}">
@endpush
