@extends('panel.layouts.app')

@section('titulo', 'Calendario')
@section('encabezado', 'Calendario de citas')
@section('subtitulo', 'Consulta tu agenda, crea citas en los huecos libres y arrastra para reorganizarlas.')

@section('acciones')
    <a class="boton boton--secundario" href="{{ route('panel.citas.index') }}">
        <i class="fa-solid fa-list-ul"></i> Ver listado
    </a>
    <button class="boton" type="button" id="boton-nueva-cita">
        <i class="fa-solid fa-plus"></i> Nueva cita
    </button>
@endsection

@section('contenido')

    @if ($config->modo_vacaciones)
        <x-panel.aviso tono="atencion" titulo="Modo vacaciones activado" class="separado">
            <p>Mientras este activo no puedes mover citas a otro hueco.
                <a href="{{ route('panel.disponibilidad') }}">Ir a disponibilidad</a>.</p>
        </x-panel.aviso>
    @endif

    @unless ($hayFranjas)
        <x-panel.aviso tono="info" titulo="Todavia no has marcado tu disponibilidad" class="separado">
            <p>Marca tus horarios semanales para que el calendario sepa cuando puedes atender.
                <a href="{{ route('panel.disponibilidad') }}">Configurar disponibilidad</a>.</p>
        </x-panel.aviso>
    @endunless

    <div class="calendario">

        <x-panel.card class="calendario__principal">
            <div id="calendario" aria-label="Calendario de citas"></div>

            <ul class="leyenda">
                <li class="leyenda__item"><span class="leyenda__color leyenda__color--presencial"></span> Presencial</li>
                <li class="leyenda__item"><span class="leyenda__color leyenda__color--online"></span> Online</li>
                <li class="leyenda__item"><span class="leyenda__color leyenda__color--cancelada"></span> Cancelada</li>
                <li class="leyenda__item"><span class="leyenda__color leyenda__color--vacaciones"></span> Vacaciones</li>
                <li class="leyenda__item"><span class="leyenda__color leyenda__color--sin-horario"></span> Sin consulta</li>
            </ul>
        </x-panel.card>

        <aside class="calendario__lateral">
            <x-panel.card titulo="Agenda del dia" icono="fa-calendar-day">
                <x-slot:acciones>
                    <button class="boton boton--pequeno" type="button" id="agenda-nueva">
                        <i class="fa-solid fa-plus"></i> Cita
                    </button>
                </x-slot:acciones>

                <p class="agenda__fecha" id="agenda-fecha">Cargando tu agenda...</p>
                <div class="agenda" id="agenda"></div>
            </x-panel.card>
        </aside>

    </div>

    @include('panel.citas.modal-formulario', ['volver' => 'calendario'])
    @include('panel.citas.modal-detalle')

@endsection

@include('panel.partials.assets-editor')

@push('estilos')
    <link rel="stylesheet" href="{{ recurso('panel/css/citas.css') }}">
    <link rel="stylesheet" href="{{ recurso('panel/css/calendario.css') }}">
@endpush

@push('scripts')
    @php
        $datosCitas = [
            'urlHuecos' => route('panel.citas.huecos'),
            'urlCrear' => route('panel.citas.store'),
            'urlBase' => url('/panel-psicologa/citas'),
            'urlPacientes' => route('panel.pacientes.buscar'),
            'errores' => $errors->any(),
        ];

        $datosDetalle = [
            'urlBase' => url('/panel-psicologa/citas'),
            'urlPacientes' => url('/panel-psicologa/pacientes'),
            'fichaDisponible' => menu_disponible('panel.pacientes.show'),
        ];

        $datosCalendario = [
            'urlEventos' => route('panel.citas.eventos'),
            'urlBase' => url('/panel-psicologa/citas'),
            'horarioLaboral' => $horarioLaboral,
            'horaMinima' => $horaMinima,
            'horaMaxima' => $horaMaxima,
            'token' => csrf_token(),
        ];
    @endphp

    <script id="datos-citas" type="application/json">@json($datosCitas)</script>
    <script id="datos-detalle" type="application/json">@json($datosDetalle)</script>
    <script id="datos-calendario" type="application/json">@json($datosCalendario)</script>

    <script src="{{ asset('vendor/fullcalendar/index.global.min.js') }}"></script>
    <script src="{{ asset('vendor/fullcalendar/es.global.min.js') }}"></script>
    <script src="{{ recurso('panel/js/citas.js') }}"></script>
    <script src="{{ recurso('panel/js/cita-detalle.js') }}"></script>
    <script src="{{ recurso('panel/js/calendario.js') }}"></script>
@endpush
