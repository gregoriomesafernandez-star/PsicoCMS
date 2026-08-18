@extends('panel.layouts.app')

@section('titulo', 'Pacientes')
@section('encabezado', 'Gestion de pacientes')
@section('subtitulo', 'Consulta y administra las fichas de las personas que atiendes.')

@section('acciones')
    <a class="boton" href="{{ route('panel.pacientes.create') }}">
        <i class="fa-solid fa-user-plus"></i> Nuevo paciente
    </a>
@endsection

@section('contenido')

    <div class="rejilla rejilla--4 separado">
        <x-panel.card class="metrica">
            <span class="metrica__icono metrica__icono--azul"><i class="fa-solid fa-users"></i></span>
            <p class="metrica__etiqueta">Total de pacientes</p>
            <p class="metrica__valor">{{ $resumen['total'] }}</p>
        </x-panel.card>

        <x-panel.card class="metrica">
            <span class="metrica__icono metrica__icono--verde"><i class="fa-solid fa-user-check"></i></span>
            <p class="metrica__etiqueta">En seguimiento</p>
            <p class="metrica__valor">{{ $resumen['activos'] }}</p>
        </x-panel.card>

        <x-panel.card class="metrica">
            <span class="metrica__icono metrica__icono--naranja"><i class="fa-solid fa-user-plus"></i></span>
            <p class="metrica__etiqueta">Nuevos este mes</p>
            <p class="metrica__valor">{{ $resumen['nuevos'] }}</p>
        </x-panel.card>

        <x-panel.card class="metrica">
            <span class="metrica__icono metrica__icono--gris"><i class="fa-solid fa-calendar-day"></i></span>
            <p class="metrica__etiqueta">Consultas de hoy</p>
            <p class="metrica__valor">{{ $resumen['citas_hoy'] }}</p>
        </x-panel.card>
    </div>

    <x-panel.card titulo="Filtrar pacientes" icono="fa-filter" class="separado">
        <form method="GET" action="{{ route('panel.pacientes.index') }}" class="filtros">
            <div class="rejilla rejilla--4">
                <x-panel.campo nombre="q" etiqueta="Buscar" :valor="$filtros['q'] ?? null"
                               placeholder="Nombre, telefono, email o DNI" />

                <x-panel.campo nombre="estado" etiqueta="Estado" tipo="select"
                               :valor="$filtros['estado'] ?? null"
                               :opciones="['' => 'Todos'] + $estados" />

                <x-panel.campo nombre="modalidad" etiqueta="Modalidad" tipo="select"
                               :valor="$filtros['modalidad'] ?? null"
                               :opciones="['' => 'Todas'] + $modalidades" />

                <x-panel.campo nombre="origen" etiqueta="Origen" tipo="select"
                               :valor="$filtros['origen'] ?? null"
                               :opciones="['' => 'Todos'] + $origenes" />
            </div>

            <div class="filtros__botones">
                <button class="boton" type="submit">
                    <i class="fa-solid fa-magnifying-glass"></i> Filtrar
                </button>
                <a class="boton boton--secundario" href="{{ route('panel.pacientes.index') }}">
                    <i class="fa-solid fa-rotate-left"></i> Limpiar
                </a>
            </div>
        </form>
    </x-panel.card>

    <x-panel.card titulo="Listado de pacientes" icono="fa-users">

        @if ($pacientes->isEmpty())
            <x-panel.empty-state icono="fa-user-plus" titulo="Todavia no hay pacientes"
                                 mensaje="Se crearan solos cuando alguien reserve desde tu web, o puedes anadirlos tu misma.">
                <a class="boton" href="{{ route('panel.pacientes.create') }}">
                    <i class="fa-solid fa-user-plus"></i> Anadir el primer paciente
                </a>
            </x-panel.empty-state>
        @else
            <x-panel.tabla :cabeceras="['Paciente', 'Ultima cita', 'Proxima cita', 'Modalidad', 'Estado', 'Acciones']">
                @foreach ($pacientes as $paciente)
                    @php
                        $datos = $agenda[$paciente->id] ?? ['ultima' => null, 'proxima' => null, 'modalidad' => null, 'total' => 0];

                        $suHistorial = array_filter([
                            $paciente->citas_count > 0 ? $paciente->citas_count . ' cita(s)' : null,
                            $paciente->historias_count > 0 ? $paciente->historias_count . ' entrada(s) de historia clinica' : null,
                        ]);

                        $mensajeBorrado = $suHistorial
                            ? sprintf(
                                'Se borrara para siempre a %s y TODO su historial: %s. No se podra recuperar.',
                                $paciente->nombre_completo,
                                implode(' y ', $suHistorial)
                            )
                            : sprintf(
                                'Se borrara para siempre la ficha de %s. No tiene citas ni historia clinica, asi que no se pierde nada mas.',
                                $paciente->nombre_completo
                            );
                    @endphp

                    <tr>
                        <td>
                            <a class="paciente paciente--enlace" href="{{ route('panel.pacientes.show', $paciente) }}">
                                <span class="paciente__inicial">{{ $paciente->iniciales }}</span>
                                <span>
                                    <span class="celda__principal">{{ $paciente->nombre_completo }}</span>
                                    <span class="celda__secundario">{{ $paciente->telefono }}</span>
                                </span>
                            </a>
                        </td>

                        <td>
                            @if ($datos['ultima'])
                                <p class="celda__principal">{{ $datos['ultima']->fecha->format('d/m/Y') }}</p>
                                <p class="celda__secundario">{{ $datos['total'] }} cita(s) en total</p>
                            @else
                                <span class="celda__secundario">Sin citas previas</span>
                            @endif
                        </td>

                        <td>
                            @if ($datos['proxima'])
                                <p class="celda__principal">{{ $datos['proxima']->fecha->format('d/m/Y') }}</p>
                                <p class="celda__secundario">{{ $datos['proxima']->hora_corta }}</p>
                            @else
                                <span class="celda__secundario celda__secundario--cursiva">Sin programar</span>
                            @endif
                        </td>

                        <td>
                            @if ($datos['modalidad'])
                                <x-panel.badge :tono="$datos['modalidad'] === 'online' ? 'info' : 'exito'"
                                               :icono="$datos['modalidad'] === 'online' ? 'fa-video' : 'fa-location-dot'">
                                    {{ $modalidades[$datos['modalidad']] }}
                                </x-panel.badge>
                            @else
                                <span class="celda__secundario">—</span>
                            @endif
                        </td>

                        <td>
                            <x-panel.badge :tono="$paciente->tono_estado">{{ $paciente->estado_legible }}</x-panel.badge>
                        </td>

                        <td>
                            <div class="acciones-fila">
                                <a class="boton boton--secundario boton--icono"
                                   href="{{ route('panel.pacientes.show', $paciente) }}"
                                   aria-label="Ver ficha de {{ $paciente->nombre_completo }}" title="Ver ficha">
                                    <i class="fa-solid fa-address-card"></i>
                                </a>

                                <a class="boton boton--secundario boton--icono"
                                   href="{{ route('panel.pacientes.edit', $paciente) }}"
                                   aria-label="Editar {{ $paciente->nombre_completo }}" title="Editar ficha">
                                    <i class="fa-solid fa-pen"></i>
                                </a>

                                <x-panel.confirmar-borrado :accion="route('panel.pacientes.destroy', $paciente)"
                                                           :titulo="$suHistorial ? '¿Seguro que quieres eliminarlo?' : 'Eliminar paciente'"
                                                           :mensaje="$mensajeBorrado"
                                                           etiqueta="Eliminar paciente" solo-icono />
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
    <link rel="stylesheet" href="{{ asset('panel/css/citas.css') }}">
    <link rel="stylesheet" href="{{ asset('panel/css/pacientes.css') }}">
@endpush
