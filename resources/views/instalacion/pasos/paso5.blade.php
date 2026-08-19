@extends('instalacion.layout')

@section('titulo', 'Tu foto y el aspecto de tu web')
@section('descripcion', 'Ya casi esta. Elige como quieres que se vea tu web y sube tu foto.')

@section('contenido')

    <form method="POST" action="{{ route('instalacion.finalizar') }}" enctype="multipart/form-data">
        @csrf

        <div class="bloque">
            <h2 class="bloque__titulo">Tu foto</h2>
            <p class="bloque__descripcion">Preferiblemente una foto en PNG y <strong>sin fondo</strong>: queda mucho mejor integrada en el diseno.</p>

            <div class="foto">
                <div class="foto__vista" id="foto-vista">
                    <i class="fa-solid fa-user"></i>
                </div>
                <div class="foto__datos">
                    <div class="campo">
                        <label class="campo__etiqueta" for="foto">Selecciona tu foto</label>
                        <input class="campo__control" type="file" id="foto" name="foto"
                               accept="image/png,image/jpeg,image/webp">
                        <p class="campo__ayuda">PNG, JPG o WEBP. Maximo 4 MB. Puedes subirla mas tarde desde el panel.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bloque">
            <h2 class="bloque__titulo">Elige tu tema visual</h2>
            <p class="bloque__descripcion">Podras cambiarlo cuando quieras desde el panel, sin perder ningun dato.</p>

            <div class="temas">
                @foreach ($temas as $slug => $tema)
                    <div>
                        <input class="tema__opcion" type="radio" id="tema-{{ $slug }}" name="tema"
                               value="{{ $slug }}" @checked(old('tema', 'base') === $slug) required>
                        <label class="tema__tarjeta" for="tema-{{ $slug }}">
                            <span class="tema__muestra">
                                @foreach ($tema['paleta'] as $color)
                                    <span class="tema__color" style="background-color: {{ $color }}"></span>
                                @endforeach
                            </span>
                            <span class="tema__info">
                                <span class="tema__nombre">{{ $tema['nombre'] }}</span>
                                <span class="tema__descripcion">{{ $tema['descripcion'] }}</span>
                            </span>
                        </label>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="bloque">
            <h2 class="bloque__titulo">Formato de la web</h2>
            <p class="bloque__descripcion">Puedes tener toda la informacion en una sola pagina o repartida en varias secciones.</p>

            <div class="modos">
                <div>
                    <input class="modo__opcion" type="radio" id="modo-landing" name="modo"
                           value="landing" @checked(old('modo', 'landing') === 'landing') required>
                    <label class="modo__tarjeta" for="modo-landing">
                        <span class="modo__icono"><i class="fa-solid fa-scroll"></i></span>
                        <span>
                            <span class="modo__nombre">Una sola pagina</span>
                            <span class="modo__descripcion">Todo el contenido seguido, con scroll suave. Directo y muy visual.</span>
                        </span>
                    </label>
                </div>

                <div>
                    <input class="modo__opcion" type="radio" id="modo-multipagina" name="modo"
                           value="multipagina" @checked(old('modo') === 'multipagina')>
                    <label class="modo__tarjeta" for="modo-multipagina">
                        <span class="modo__icono"><i class="fa-solid fa-copy"></i></span>
                        <span>
                            <span class="modo__nombre">Varias paginas</span>
                            <span class="modo__descripcion">Una url para cada seccion. Mejor si tienes mucho contenido.</span>
                        </span>
                    </label>
                </div>
            </div>
        </div>

        <div class="acciones">
            <a class="boton boton--secundario" href="{{ route('instalacion.paso', ['paso' => 4]) }}">
                <i class="fa-solid fa-arrow-left"></i> Atras
            </a>
            <button class="boton" type="submit">
                <i class="fa-solid fa-check"></i> Finalizar e ir a mi panel
            </button>
        </div>
    </form>

@endsection

@push('scripts')
    <script src="{{ recurso('panel/js/instalacion-foto.js') }}"></script>
@endpush
