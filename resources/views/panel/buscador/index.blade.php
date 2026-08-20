@extends('panel.layouts.app')

@section('titulo', 'Buscar')
@section('encabezado', $texto ? 'Resultados de "' . $texto . '"' : 'Buscar en tu panel')
@section('subtitulo', $texto && $valida
    ? ($total === 1 ? '1 coincidencia encontrada.' : $total . ' coincidencias encontradas.')
    : 'Encuentra pacientes, citas, sesiones, articulos y mucho mas desde un solo sitio.')

@section('migas')
    <x-panel.migas :items="[
        ['texto' => 'Inicio', 'url' => route('panel.inicio')],
        ['texto' => 'Buscar'],
    ]" />
@endsection

@section('contenido')

    <x-panel.card titulo="Buscar" icono="fa-magnifying-glass" class="separado">
        <form method="GET" action="{{ route('panel.buscador') }}" class="filtros">
            <div class="filtros__busqueda">
                <x-panel.campo nombre="q" etiqueta="Que estas buscando" :valor="$texto"
                               placeholder="Un nombre, un telefono, un titulo..."
                               autofocus />

                <div class="filtros__botones">
                    <button class="boton" type="submit">
                        <i class="fa-solid fa-magnifying-glass"></i> Buscar
                    </button>

                    @if ($texto)
                        <a class="boton boton--secundario" href="{{ route('panel.buscador') }}">
                            <i class="fa-solid fa-rotate-left"></i> Limpiar
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </x-panel.card>

    @if (! $texto)
        <x-panel.card titulo="Se busca en todo tu panel" icono="fa-compass">
            <div class="buscador-guia">
                @foreach ($tipos as $clave => [$nombre, $icono])
                    <div class="buscador-guia__tipo">
                        <span class="buscador-guia__icono"><i class="fa-solid {{ $icono }}"></i></span>
                        <p class="buscador-guia__nombre">{{ $nombre }}</p>
                    </div>
                @endforeach
            </div>

            <x-panel.aviso tono="info" titulo="Un truco">
                <p>Puedes buscar por telefono de un paciente y te saldran tambien sus citas y sus
                    sesiones. Las citas se encuentran ademas por <strong>fecha</strong> (24/09/2026,
                    24-09-2026 o solo 24/09) y por el <strong>motivo</strong> que anotaste.</p>

                <p>El contenido de las historias clinicas no se puede buscar porque esta cifrado, pero
                    si su titulo.</p>
            </x-panel.aviso>
        </x-panel.card>

    @elseif (! $valida)
        <x-panel.card titulo="Resultados" icono="fa-magnifying-glass">
            <x-panel.empty-state icono="fa-keyboard" titulo="Escribe un poco mas"
                                 :mensaje="'Necesito al menos ' . $minimo . ' letras para buscar algo util.'" />
        </x-panel.card>

    @elseif ($total === 0)
        <x-panel.card titulo="Resultados" icono="fa-magnifying-glass">
            <x-panel.empty-state icono="fa-magnifying-glass" titulo="No he encontrado nada"
                                 :mensaje="'Nada coincide con \'' . $texto . '\'. Prueba con menos palabras o revisa si hay alguna errata.'">
                <a class="boton boton--secundario" href="{{ route('panel.buscador') }}">
                    <i class="fa-solid fa-rotate-left"></i> Empezar de nuevo
                </a>
            </x-panel.empty-state>
        </x-panel.card>

    @else
        <div class="filtros-tipo separado">
            <a class="filtro-tipo {{ $tipo ? '' : 'filtro-tipo--activo' }}"
               href="{{ route('panel.buscador', ['q' => $texto]) }}">
                Todo <span class="filtro-tipo__cuenta">{{ array_sum($contadores) }}</span>
            </a>

            @foreach ($tipos as $clave => [$nombre, $icono])
                @if (($contadores[$clave] ?? 0) > 0)
                    <a class="filtro-tipo {{ $tipo === $clave ? 'filtro-tipo--activo' : '' }}"
                       href="{{ route('panel.buscador', ['q' => $texto, 'tipo' => $clave]) }}">
                        <i class="fa-solid {{ $icono }}"></i> {{ $nombre }}
                        <span class="filtro-tipo__cuenta">{{ $contadores[$clave] }}</span>
                    </a>
                @endif
            @endforeach
        </div>

        @foreach ($resultados as $clave => $lista)
            @php [$nombre, $icono] = $tipos[$clave]; @endphp

            <x-panel.card :titulo="$nombre" :icono="$icono"
                          :descripcion="$lista->count() === 1 ? '1 resultado' : $lista->count() . ' resultados'"
                          class="separado">

                <ul class="resultados">
                    @foreach ($lista as $resultado)
                        @php $cita = $resultado['cita'] ?? null; @endphp

                        <li class="resultado">
                            {{-- Una cita abre su modal de detalle; el resto lleva a su pantalla. --}}
                            <{{ $cita ? 'button' : 'a' }} class="resultado__enlace"
                                @if ($cita)
                                    type="button" data-ver-cita data-cita="{{ json_encode($cita) }}"
                                @else
                                    href="{{ $resultado['url'] }}"
                                @endif>

                                <span class="resultado__icono"><i class="fa-solid {{ $icono }}"></i></span>

                                <span class="resultado__cuerpo">
                                    <span class="resultado__titulo">
                                        {!! App\Support\Resaltado::marcar($resultado['titulo'], $texto) !!}
                                    </span>
                                    <span class="resultado__detalle">
                                        {!! App\Support\Resaltado::marcar(Str::limit($resultado['detalle'], 120), $texto) !!}
                                    </span>
                                </span>

                                @if ($resultado['badge'])
                                    <x-panel.badge tono="neutro">{{ $resultado['badge'] }}</x-panel.badge>
                                @endif

                                <i class="resultado__flecha fa-solid {{ $cita ? 'fa-eye' : 'fa-chevron-right' }}"
                                   aria-hidden="true"></i>
                            </{{ $cita ? 'button' : 'a' }}>
                        </li>
                    @endforeach
                </ul>

            </x-panel.card>
        @endforeach
    @endif

    {{-- Los dos modales de cita, igual que en el listado y el calendario, para
         que desde aqui se pueda ver, cambiar de estado, editar y eliminar. --}}
    @if (isset($resultados['citas']))
        @include('panel.citas.modal-formulario', ['volver' => 'buscador', 'busqueda' => $texto])
        @include('panel.citas.modal-detalle')
    @endif

@endsection

@if (isset($resultados['citas']))
    @include('panel.partials.assets-editor')
@endif

@push('estilos')
    <link rel="stylesheet" href="{{ recurso('panel/css/citas.css') }}">
    <link rel="stylesheet" href="{{ recurso('panel/css/buscador.css') }}">
@endpush

@push('scripts')
    @if (isset($resultados['citas']))
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
        @endphp

        <script id="datos-citas" type="application/json">@json($datosCitas)</script>
        <script id="datos-detalle" type="application/json">@json($datosDetalle)</script>
        <script src="{{ recurso('panel/js/citas.js') }}"></script>
        <script src="{{ recurso('panel/js/cita-detalle.js') }}"></script>
    @endif
@endpush
