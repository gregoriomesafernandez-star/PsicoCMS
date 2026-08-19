@extends('instalacion.layout')

@section('titulo', 'Tus servicios, especialidades y precios')
@section('descripcion', 'Anade lo basico para arrancar. Despues podras crear, editar y ordenar todo lo que quieras desde el panel.')

@section('contenido')

    <form method="POST" action="{{ route('instalacion.guardar.servicios') }}" id="formulario-catalogo">
        @csrf

        <div class="bloque">
            <h2 class="bloque__titulo">Servicios principales</h2>
            <p class="bloque__descripcion">Los tipos de terapia o acompanamiento que ofreces.</p>

            <div class="repetible" id="lista-servicios"></div>
            <button class="boton boton--secundario boton--pequeno" type="button" data-anadir="servicios">
                <i class="fa-solid fa-plus"></i> Anadir servicio
            </button>
        </div>

        <div class="bloque">
            <h2 class="bloque__titulo">Especialidades</h2>
            <p class="bloque__descripcion">Las areas en las que trabajas: ansiedad, autoestima, duelo...</p>

            <div class="repetible" id="lista-especialidades"></div>
            <button class="boton boton--secundario boton--pequeno" type="button" data-anadir="especialidades">
                <i class="fa-solid fa-plus"></i> Anadir especialidad
            </button>
        </div>

        <div class="bloque">
            <h2 class="bloque__titulo">Planes y precios</h2>
            <p class="bloque__descripcion">Indica el precio de tus sesiones online y presenciales.</p>

            <div class="repetible" id="lista-planes"></div>
            <button class="boton boton--secundario boton--pequeno" type="button" data-anadir="planes">
                <i class="fa-solid fa-plus"></i> Anadir plan
            </button>
        </div>

        <div class="bloque">
            <h2 class="bloque__titulo">Horarios y disponibilidad</h2>
            <p class="bloque__descripcion">Un texto orientativo para tu web. La disponibilidad real para reservas la configuraras en el panel.</p>

            <div class="campo">
                <label class="campo__etiqueta" for="horarios">Horario de atencion</label>
                <input class="campo__control" type="text" id="horarios" name="horarios"
                       value="{{ old('horarios', $datos['catalogo']['horarios'] ?? '') }}"
                       placeholder="De lunes a viernes, de 9:00 a 20:00">
            </div>
        </div>

        <div class="acciones">
            <a class="boton boton--secundario" href="{{ route('instalacion.paso', ['paso' => 3]) }}">
                <i class="fa-solid fa-arrow-left"></i> Atras
            </a>
            <button class="boton" type="submit">
                Continuar <i class="fa-solid fa-arrow-right"></i>
            </button>
        </div>
    </form>

@endsection

@push('scripts')
    <script id="datos-catalogo" type="application/json">
        @json($datos['catalogo'] ?? null)
    </script>
    <script src="{{ recurso('panel/js/instalacion-catalogo.js') }}"></script>
@endpush
