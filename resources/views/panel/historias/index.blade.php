@extends('panel.layouts.app')

@section('titulo', 'Historias')
@section('encabezado', 'Historias clinicas')
@section('subtitulo', 'Cada paciente tiene su historia. Entra en la suya para ver la evolucion de sus sesiones.')

@section('migas')
    <x-panel.migas :items="[
        ['texto' => 'Inicio', 'url' => route('panel.inicio')],
        ['texto' => 'Historias clinicas'],
    ]" />
@endsection

@section('acciones')
    <a class="boton" href="{{ route('panel.historias.create') }}">
        <i class="fa-solid fa-plus"></i> Nueva entrada
    </a>
@endsection

@section('contenido')

    <x-panel.aviso tono="info" titulo="Esta informacion es confidencial" class="separado">
        <p>El contenido de cada sesion se guarda cifrado y los archivos adjuntos solo se pueden abrir
            desde aqui, con tu sesion iniciada. Nadie mas tiene acceso.</p>
    </x-panel.aviso>

    <div class="rejilla rejilla--4 separado">
        <x-panel.card class="metrica">
            <span class="metrica__icono metrica__icono--azul"><i class="fa-solid fa-notes-medical"></i></span>
            <p class="metrica__etiqueta">Entradas en total</p>
            <p class="metrica__valor">{{ $resumen['total'] }}</p>
        </x-panel.card>

        <x-panel.card class="metrica">
            <span class="metrica__icono metrica__icono--verde"><i class="fa-solid fa-calendar-day"></i></span>
            <p class="metrica__etiqueta">Escritas este mes</p>
            <p class="metrica__valor">{{ $resumen['mes'] }}</p>
        </x-panel.card>

        <x-panel.card class="metrica">
            <span class="metrica__icono metrica__icono--naranja"><i class="fa-solid fa-paperclip"></i></span>
            <p class="metrica__etiqueta">Archivos adjuntos</p>
            <p class="metrica__valor">{{ $resumen['adjuntos'] }}</p>
        </x-panel.card>

        <x-panel.card class="metrica">
            <span class="metrica__icono metrica__icono--gris"><i class="fa-solid fa-users"></i></span>
            <p class="metrica__etiqueta">Pacientes con historia</p>
            <p class="metrica__valor">{{ $resumen['pacientes'] }}</p>
        </x-panel.card>
    </div>

    <x-panel.card titulo="Buscar paciente" icono="fa-filter" class="separado">
        <form method="GET" action="{{ route('panel.historias.index') }}" class="filtros">
            <div class="filtros__busqueda">
                <x-panel.campo nombre="q" etiqueta="Buscar" :valor="$filtros['q'] ?? null"
                               placeholder="Nombre, telefono, email o DNI del paciente" />

                <div class="filtros__botones">
                    <button class="boton" type="submit">
                        <i class="fa-solid fa-magnifying-glass"></i> Buscar
                    </button>
                    <a class="boton boton--secundario" href="{{ route('panel.historias.index') }}">
                        <i class="fa-solid fa-rotate-left"></i> Limpiar
                    </a>
                </div>
            </div>
        </form>
    </x-panel.card>

    <x-panel.card titulo="Pacientes con historia clinica" icono="fa-folder-open"
                  :descripcion="$pacientes->isEmpty() ? null : 'De la sesion mas reciente a la mas antigua.'">

        @if ($pacientes->isEmpty())
            @if ($hayEntradas)
                <x-panel.empty-state icono="fa-magnifying-glass" titulo="Ningun paciente coincide"
                                     mensaje="Prueba con otro nombre o limpia la busqueda para verlos todos.">
                    <a class="boton boton--secundario" href="{{ route('panel.historias.index') }}">
                        <i class="fa-solid fa-rotate-left"></i> Limpiar busqueda
                    </a>
                </x-panel.empty-state>
            @else
                <x-panel.empty-state icono="fa-notes-medical" titulo="Todavia no has escrito ninguna sesion"
                                     mensaje="Despues de cada sesion puedes anotar aqui como ha ido y adjuntar fotos o PDF escaneados.">
                    <a class="boton" href="{{ route('panel.historias.create') }}">
                        <i class="fa-solid fa-plus"></i> Escribir la primera entrada
                    </a>
                </x-panel.empty-state>
            @endif
        @else
            <x-panel.tabla :cabeceras="['Paciente', 'Sesiones', 'Ultima sesion', 'Acciones']">
                @foreach ($pacientes as $paciente)
                    @php
                        $ultima = $paciente->ultima_sesion
                            ? \Illuminate\Support\Carbon::parse($paciente->ultima_sesion)
                            : null;
                    @endphp

                    <tr>
                        <td>
                            <a class="paciente paciente--enlace"
                               href="{{ route('panel.historias.index', ['paciente' => $paciente->id]) }}">
                                <span class="paciente__inicial">{{ $paciente->iniciales }}</span>
                                <span>
                                    <span class="celda__principal">{{ $paciente->nombre_completo }}</span>
                                    <span class="celda__secundario">{{ $paciente->telefono }}</span>
                                </span>
                            </a>
                        </td>

                        <td>
                            <x-panel.badge tono="info" icono="fa-notes-medical">
                                {{ $paciente->historias_count }}
                            </x-panel.badge>
                        </td>

                        <td>
                            @if ($ultima)
                                <p class="celda__principal">{{ $ultima->format('d/m/Y') }}</p>
                                <p class="celda__secundario">{{ $ultima->locale('es')->diffForHumans() }}</p>
                            @else
                                <span class="celda__secundario">—</span>
                            @endif
                        </td>

                        <td>
                            <div class="acciones-fila">
                                <a class="boton boton--secundario"
                                   href="{{ route('panel.historias.index', ['paciente' => $paciente->id]) }}">
                                    <i class="fa-solid fa-timeline"></i> Ver historia
                                </a>

                                <a class="boton boton--secundario"
                                   href="{{ route('panel.historias.create', ['paciente' => $paciente->id]) }}"
                                   title="Anota una sesion de {{ $paciente->nombre_completo }}">
                                    <i class="fa-solid fa-plus"></i> Nueva entrada
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-panel.tabla>

            <x-panel.paginacion :coleccion="$pacientes" nombre="pacientes" />
        @endif

    </x-panel.card>

@endsection

@push('estilos')
    <link rel="stylesheet" href="{{ recurso('panel/css/citas.css') }}">
    <link rel="stylesheet" href="{{ recurso('panel/css/pacientes.css') }}">
    <link rel="stylesheet" href="{{ recurso('panel/css/historias.css') }}">
@endpush
