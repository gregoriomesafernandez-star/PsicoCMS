@extends('panel.layouts.app')

@section('titulo', 'Citas')
@section('encabezado', 'Gestion de citas')
@section('subtitulo', 'Consulta, crea, edita y elimina las citas de tu agenda.')

@section('acciones')
    <button class="boton" type="button" id="boton-nueva-cita">
        <i class="fa-solid fa-plus"></i> Nueva cita
    </button>
@endsection

@section('contenido')

    @if ($config->modo_vacaciones)
        <x-panel.aviso tono="atencion" titulo="Modo vacaciones activado" class="separado">
            <p>Tus pacientes no pueden reservar desde la web. Tu si puedes seguir creando citas manualmente.
                <a href="{{ route('panel.disponibilidad') }}">Ir a disponibilidad</a>.</p>
        </x-panel.aviso>
    @endif

    <div class="rejilla rejilla--4 separado">
        <x-panel.card class="metrica">
            <span class="metrica__icono metrica__icono--azul"><i class="fa-solid fa-calendar-day"></i></span>
            <p class="metrica__etiqueta">Citas de hoy</p>
            <p class="metrica__valor">{{ $resumen['hoy'] }}</p>
        </x-panel.card>

        <x-panel.card class="metrica">
            <span class="metrica__icono metrica__icono--verde"><i class="fa-solid fa-calendar-week"></i></span>
            <p class="metrica__etiqueta">Esta semana</p>
            <p class="metrica__valor">{{ $resumen['semana'] }}</p>
        </x-panel.card>

        <x-panel.card class="metrica">
            <span class="metrica__icono metrica__icono--naranja"><i class="fa-solid fa-hourglass-half"></i></span>
            <p class="metrica__etiqueta">Por confirmar</p>
            <p class="metrica__valor">{{ $resumen['pendientes'] }}</p>
        </x-panel.card>

        <x-panel.card class="metrica">
            <span class="metrica__icono metrica__icono--gris"><i class="fa-solid fa-list-check"></i></span>
            <p class="metrica__etiqueta">Total historico</p>
            <p class="metrica__valor">{{ $resumen['total'] }}</p>
        </x-panel.card>
    </div>

    <x-panel.card titulo="Filtrar citas" icono="fa-filter" class="separado">
        <form method="GET" action="{{ route('panel.citas.index') }}" class="filtros">
            <div class="rejilla rejilla--4">
                <x-panel.campo nombre="desde" etiqueta="Desde" tipo="date" :valor="$filtros['desde'] ?? null" />
                <x-panel.campo nombre="hasta" etiqueta="Hasta" tipo="date" :valor="$filtros['hasta'] ?? null" />

                <x-panel.campo nombre="modalidad" etiqueta="Modalidad" tipo="select"
                               :valor="$filtros['modalidad'] ?? null"
                               :opciones="['' => 'Todas'] + $modalidades" />

                <x-panel.campo nombre="estado" etiqueta="Estado" tipo="select"
                               :valor="$filtros['estado'] ?? null"
                               :opciones="['' => 'Todos'] + $estados" />
            </div>

            <div class="filtros__busqueda">
                <x-panel.campo nombre="q" etiqueta="Buscar" :valor="$filtros['q'] ?? null"
                               placeholder="Nombre, telefono o motivo de la consulta" />

                <div class="filtros__botones">
                    <button class="boton" type="submit">
                        <i class="fa-solid fa-magnifying-glass"></i> Filtrar
                    </button>
                    <a class="boton boton--secundario" href="{{ route('panel.citas.index') }}">
                        <i class="fa-solid fa-rotate-left"></i> Limpiar
                    </a>
                </div>
            </div>
        </form>
    </x-panel.card>

    <x-panel.card titulo="Listado de citas" icono="fa-calendar-check">

        @if ($citas->isEmpty())
            <x-panel.empty-state icono="fa-calendar-plus" titulo="No hay citas que mostrar"
                                 mensaje="Cuando tus pacientes reserven o crees una cita manualmente, apareceran aqui.">
                <button class="boton" type="button" data-nueva-cita>
                    <i class="fa-solid fa-plus"></i> Crear la primera cita
                </button>
            </x-panel.empty-state>
        @else
            <x-panel.tabla :cabeceras="['Fecha y hora', 'Paciente', 'Modalidad', 'Estado', 'Acciones']">
                @foreach ($citas as $cita)
                    <tr>
                        <td>
                            <p class="celda__principal">{{ $cita->fecha->format('d/m/Y') }}</p>
                            <p class="celda__secundario">{{ $cita->hora_corta }} - {{ $cita->hora_fin_corta }}</p>
                        </td>

                        <td>
                            <div class="paciente">
                                <span class="paciente__inicial">{{ $cita->paciente?->iniciales ?? '?' }}</span>
                                <div>
                                    <p class="celda__principal">{{ $cita->paciente?->nombre_completo ?? 'Sin paciente' }}</p>
                                    <p class="celda__secundario">{{ $cita->paciente?->telefono }}</p>
                                </div>
                            </div>
                        </td>

                        <td>
                            <x-panel.badge :tono="$cita->modalidad === 'online' ? 'info' : 'exito'"
                                           :icono="$cita->modalidad === 'online' ? 'fa-video' : 'fa-location-dot'">
                                {{ $cita->modalidad_legible }}
                            </x-panel.badge>
                        </td>

                        <td>
                            <form method="POST" action="{{ route('panel.citas.estado', $cita) }}" class="estado-rapido">
                                @csrf
                                @method('PATCH')
                                <select class="estado-rapido__select estado-rapido__select--{{ $cita->tono_estado }}"
                                        name="estado" aria-label="Cambiar estado de la cita">
                                    @foreach ($estados as $clave => $texto)
                                        <option value="{{ $clave }}" @selected($cita->estado === $clave)>{{ $texto }}</option>
                                    @endforeach
                                </select>
                            </form>
                        </td>

                        <td>
                            @php
                                $datosCita = json_encode([
                                    'id' => $cita->id,
                                    'paciente_id' => $cita->paciente_id,
                                    'paciente_nombre' => $cita->paciente?->nombre,
                                    'paciente_apellidos' => $cita->paciente?->apellidos,
                                    'paciente_completo' => $cita->paciente?->nombre_completo,
                                    'paciente_telefono' => $cita->paciente?->telefono,
                                    'paciente_email' => $cita->paciente?->email,
                                    'paciente_iniciales' => $cita->paciente?->iniciales,
                                    'modalidad' => $cita->modalidad,
                                    'modalidad_legible' => $cita->modalidad_legible,
                                    'fecha' => $cita->fecha->toDateString(),
                                    'hora_inicio' => $cita->hora_corta,
                                    'hora_fin' => $cita->hora_fin_corta,
                                    'duracion' => $cita->duracion,
                                    'estado' => $cita->estado,
                                    'estado_legible' => $cita->estado_legible,
                                    'tono' => $cita->tono_estado,
                                    'motivo' => $cita->motivo,
                                    'notas' => $cita->notas,
                                ]);
                            @endphp

                            <div class="acciones-fila">
                                <button class="boton boton--secundario boton--icono" type="button"
                                        data-ver-cita data-cita="{{ $datosCita }}"
                                        aria-label="Ver los datos de la cita" title="Ver los datos de la cita">
                                    <i class="fa-solid fa-eye"></i>
                                </button>

                                <button class="boton boton--secundario boton--icono" type="button"
                                        data-editar-cita="{{ $cita->id }}"
                                        data-cita="{{ $datosCita }}"
                                        aria-label="Editar cita" title="Editar cita">
                                    <i class="fa-solid fa-pen"></i>
                                </button>

                                <x-panel.confirmar-borrado :accion="route('panel.citas.destroy', $cita)"
                                                           titulo="Eliminar cita"
                                                           mensaje="La cita se borrara definitivamente y no se podra recuperar. El paciente y su ficha no se borran."
                                                           etiqueta="Eliminar cita" solo-icono />
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-panel.tabla>

            <x-panel.paginacion :coleccion="$citas" nombre="citas" />
        @endif

    </x-panel.card>

    @include('panel.citas.modal-formulario')
    @include('panel.citas.modal-detalle')

@endsection

@include('panel.partials.assets-editor')

@push('estilos')
    <link rel="stylesheet" href="{{ asset('panel/css/citas.css') }}">
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
    @endphp

    <script id="datos-citas" type="application/json">@json($datosCitas)</script>
    <script id="datos-detalle" type="application/json">@json($datosDetalle)</script>
    <script src="{{ asset('panel/js/citas.js') }}"></script>
    <script src="{{ asset('panel/js/cita-detalle.js') }}"></script>
@endpush
