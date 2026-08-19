@extends('panel.layouts.app')

@section('titulo', 'Disponibilidad')
@section('encabezado', 'Configuracion de disponibilidad')
@section('subtitulo', 'Define cuanto duran tus sesiones y en que dias y horas atiendes. Esto es lo que veran tus pacientes al pedir cita.')

@section('contenido')

    @if ($config->requiere_reconfig_online || $config->requiere_reconfig_presencial)
        <x-panel.aviso tono="atencion" titulo="Vuelve a marcar tus horarios" class="separado">
            <p>Has cambiado la duracion de las sesiones o el descanso entre ellas, asi que los huecos
                disponibles son distintos. Revisa y guarda de nuevo tu horario semanal
                @if ($config->requiere_reconfig_online && $config->requiere_reconfig_presencial)
                    <strong>online y presencial</strong>.
                @elseif ($config->requiere_reconfig_online)
                    <strong>online</strong>.
                @else
                    <strong>presencial</strong>.
                @endif
            </p>
        </x-panel.aviso>
    @endif

    <x-panel.card titulo="Duracion y descansos" icono="fa-stopwatch"
                  descripcion="Con estos valores se calculan los huecos que se pueden reservar."
                  class="separado">

        <form method="POST" action="{{ route('panel.disponibilidad.configuracion') }}">
            @csrf
            @method('PUT')

            <div class="rejilla rejilla--2">
                <x-panel.campo nombre="duracion_presencial" etiqueta="Duracion de las sesiones presenciales"
                               tipo="number" :valor="$config->duracion_presencial" min="5" max="480" step="5"
                               ayuda="En minutos." obligatorio />

                <x-panel.campo nombre="duracion_online" etiqueta="Duracion de las sesiones online"
                               tipo="number" :valor="$config->duracion_online" min="5" max="480" step="5"
                               ayuda="En minutos." obligatorio />
            </div>

            <section class="descanso">
                <header class="descanso__cabecera">
                    <h3 class="descanso__titulo">Descanso entre sesiones</h3>
                    <p class="descanso__ayuda">Pausa automatica tras cada sesion. Configurala por
                        separado para presencial y online.</p>
                </header>

                <div class="descanso__tarjetas">

                    <article class="descanso__tarjeta" data-descanso="presencial">
                        <header class="descanso__fila">
                            <p class="descanso__modalidad">
                                <i class="fa-solid fa-location-dot"></i> Presencial
                            </p>

                            <x-panel.toggle nombre="descanso_presencial_activo"
                                            :activo="$config->descanso_presencial_activo"
                                            id="descanso-presencial-activo" />
                        </header>

                        <x-panel.campo nombre="descanso_presencial" etiqueta="Minutos de descanso"
                                       tipo="number" :valor="$config->descanso_presencial"
                                       min="0" max="120" step="5" obligatorio />

                        <p class="descanso__resultado" data-resultado="presencial"></p>
                    </article>

                    <article class="descanso__tarjeta" data-descanso="online">
                        <header class="descanso__fila">
                            <p class="descanso__modalidad">
                                <i class="fa-solid fa-video"></i> Online
                            </p>

                            <x-panel.toggle nombre="descanso_online_activo"
                                            :activo="$config->descanso_online_activo"
                                            id="descanso-online-activo" />
                        </header>

                        <x-panel.campo nombre="descanso_online" etiqueta="Minutos de descanso"
                                       tipo="number" :valor="$config->descanso_online"
                                       min="0" max="120" step="5" obligatorio />

                        <p class="descanso__resultado" data-resultado="online"></p>
                    </article>

                </div>
            </section>

            <div class="rejilla rejilla--2">
                <x-panel.campo nombre="hora_inicio" etiqueta="Hora de entrada" tipo="time"
                               :valor="substr($config->hora_inicio, 0, 5)" obligatorio />

                <x-panel.campo nombre="hora_fin" etiqueta="Hora de salida (maxima)" tipo="time"
                               :valor="substr($config->hora_fin, 0, 5)" obligatorio />
            </div>

            <div class="acciones-formulario">
                <button class="boton" type="submit">
                    <i class="fa-solid fa-floppy-disk"></i> Guardar configuracion
                </button>
            </div>
        </form>

    </x-panel.card>

    <div class="rejilla rejilla--2 separado">

        <x-panel.card titulo="Modo vacaciones" icono="fa-plane-departure"
                      descripcion="Pausa temporalmente todas las nuevas reservas. Las citas que ya tengas no se cancelan.">

            <form method="POST" action="{{ route('panel.disponibilidad.modo-vacaciones') }}" id="form-modo-vacaciones">
                @csrf
                @method('PUT')

                <div class="modo-vacaciones">
                    <x-panel.toggle nombre="modo_vacaciones" :activo="$config->modo_vacaciones"
                                    id="modo-vacaciones" />

                    <div>
                        <p class="modo-vacaciones__estado">
                            @if ($config->modo_vacaciones)
                                <x-panel.badge tono="error" icono="fa-circle-pause">Reservas pausadas</x-panel.badge>
                            @else
                                <x-panel.badge tono="exito" icono="fa-circle-play">Reservas abiertas</x-panel.badge>
                            @endif
                        </p>
                        <p class="campo__ayuda">Se guarda al cambiar el interruptor.</p>
                    </div>
                </div>

                <div class="mensaje-vacaciones">
                    <h3 class="mensaje-vacaciones__titulo">Mensaje para tus pacientes</h3>
                    <p class="mensaje-vacaciones__ayuda">Es lo que veran en tu web cuando intenten pedir
                        cita mientras el modo vacaciones este activado.</p>

                    <div class="campo">
                        <label class="campo__etiqueta" for="mensaje_vacaciones">Tu mensaje</label>
                        <textarea class="campo__control" id="mensaje_vacaciones" name="mensaje_vacaciones"
                                  rows="4" maxlength="500"
                                  placeholder="{{ \App\Http\Requests\Panel\ModoVacacionesRequest::MENSAJE_POR_DEFECTO }}">{{ old('mensaje_vacaciones', $config->mensaje_vacaciones) }}</textarea>

                        <p class="campo__ayuda">
                            <span data-contador-mensaje>0</span>/500 caracteres.
                            Si lo dejas vacio se mostrara un texto por defecto.
                        </p>
                    </div>

                    <x-panel.campo nombre="vuelvo_el" etiqueta="Vuelvo el (opcional)" tipo="date"
                                   :valor="$config->vuelvo_el?->toDateString()"
                                   ayuda="Si la indicas, se anadira al mensaje." />

                    <button class="boton boton--secundario" type="submit">
                        <i class="fa-solid fa-floppy-disk"></i> Guardar mensaje
                    </button>
                </div>
            </form>

        </x-panel.card>

        <x-panel.card titulo="Periodos de vacaciones" icono="fa-umbrella-beach"
                      descripcion="Bloquea rangos de fechas concretos. Puedes anadir todos los que quieras.">

            <form method="POST" action="{{ route('panel.vacaciones.store') }}" class="periodo-formulario">
                @csrf

                <div class="rejilla rejilla--2">
                    <x-panel.campo nombre="fecha_inicio" etiqueta="Desde" tipo="date" obligatorio />
                    <x-panel.campo nombre="fecha_fin" etiqueta="Hasta" tipo="date" obligatorio />
                </div>

                <x-panel.campo nombre="motivo" etiqueta="Motivo (opcional)" placeholder="Vacaciones de verano" />

                <button class="boton boton--secundario" type="submit">
                    <i class="fa-solid fa-plus"></i> Anadir periodo
                </button>
            </form>

            @if ($periodos->isEmpty())
                <x-panel.empty-state icono="fa-calendar-xmark" titulo="Sin periodos de vacaciones"
                                     mensaje="Cuando anadas un periodo, esos dias apareceran bloqueados en tu calendario de reservas." />
            @else
                <ul class="periodos">
                    @foreach ($periodos as $periodo)
                        <li class="periodos__item">
                            <div>
                                <p class="periodos__fechas">
                                    {{ $periodo->fecha_inicio->format('d/m/Y') }} &rarr; {{ $periodo->fecha_fin->format('d/m/Y') }}
                                </p>
                                <p class="periodos__detalle">
                                    {{ $periodo->dias }} {{ $periodo->dias === 1 ? 'dia' : 'dias' }}
                                    @if ($periodo->motivo)
                                        &middot; {{ $periodo->motivo }}
                                    @endif
                                </p>
                            </div>

                            <x-panel.confirmar-borrado :accion="route('panel.vacaciones.destroy', $periodo)"
                                                       titulo="Eliminar periodo"
                                                       mensaje="Se desbloquearan esas fechas y tus pacientes podran volver a reservar en ellas."
                                                       etiqueta="Eliminar periodo" solo-icono />
                        </li>
                    @endforeach
                </ul>
            @endif

        </x-panel.card>

    </div>

    <x-panel.card titulo="Horario semanal" icono="fa-calendar-week"
                  descripcion="Haz click en las horas en las que atiendes. Puedes arrastrar para marcar varias seguidas.">

        <div class="pestanas" role="tablist">
            <button class="pestanas__boton pestanas__boton--activo" type="button" role="tab"
                    data-pestana="presencial" aria-selected="true">
                <i class="fa-solid fa-location-dot"></i> Presencial
            </button>
            <button class="pestanas__boton" type="button" role="tab"
                    data-pestana="online" aria-selected="false">
                <i class="fa-solid fa-video"></i> Online
            </button>
        </div>

        @foreach (['presencial' => $huecosPresencial, 'online' => $huecosOnline] as $modalidad => $huecos)
            @php
                $franjas = $modalidad === 'online' ? $franjasOnline : $franjasPresencial;
                $paso = $modalidad === 'online' ? $pasoOnline : $pasoPresencial;
            @endphp

            <section class="panel-modalidad" data-panel="{{ $modalidad }}"
                     @if ($modalidad === 'online') hidden @endif>

                @if (empty($huecos))
                    <x-panel.empty-state icono="fa-triangle-exclamation" titulo="No hay huecos posibles"
                                         mensaje="Con la duracion y el horario que has configurado no cabe ninguna sesion. Revisa la hora de entrada y salida." />
                @else
                    <p class="rejilla-info">
                        <i class="fa-solid fa-circle-info"></i>
                        Cada hueco dura <strong>{{ $paso }} minutos</strong>
                        (sesion de {{ $modalidad === 'online' ? $config->duracion_online : $config->duracion_presencial }} min
                        @if ($config->{'descanso_' . $modalidad . '_activo'})
                            + {{ $modalidad === 'online' ? $config->descanso_online : $config->descanso_presencial }} min de descanso
                        @endif).
                    </p>

                    <form method="POST" action="{{ route('panel.disponibilidad.franjas') }}"
                          class="form-franjas" data-modalidad="{{ $modalidad }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="modalidad" value="{{ $modalidad }}">

                        <div class="rejilla-horaria__contenedor">
                            <table class="rejilla-horaria">
                                <thead>
                                    <tr>
                                        <th scope="col">Hora</th>
                                        @foreach (\App\Services\DisponibilidadService::DIAS as $indice => $nombre)
                                            <th scope="col">
                                                <span class="rejilla-horaria__dia">{{ $nombre }}</span>
                                                <button class="rejilla-horaria__todos" type="button"
                                                        data-columna="{{ $indice }}"
                                                        title="Marcar o desmarcar todo el dia">
                                                    <i class="fa-solid fa-check-double"></i>
                                                </button>
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($huecos as $hora)
                                        <tr>
                                            <th scope="row" class="rejilla-horaria__hora">{{ $hora }}</th>
                                            @foreach (array_keys(\App\Services\DisponibilidadService::DIAS) as $dia)
                                                @php $marcado = in_array($hora, $franjas[$dia] ?? [], true); @endphp
                                                <td>
                                                    <label class="hueco {{ $marcado ? 'hueco--activo' : '' }}">
                                                        <input class="hueco__entrada" type="checkbox"
                                                               name="franjas[]" value="{{ $dia }}|{{ $hora }}"
                                                               data-columna="{{ $dia }}" @checked($marcado)>
                                                        <span class="hueco__marca">{{ $hora }}</span>
                                                    </label>
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="acciones-formulario">
                            <p class="contador" data-contador>
                                <strong>0</strong> huecos marcados
                            </p>

                            <div class="acciones-formulario__botones">
                                <button class="boton boton--secundario" type="button" data-limpiar>
                                    <i class="fa-solid fa-eraser"></i> Desmarcar todo
                                </button>
                                <button class="boton" type="submit">
                                    <i class="fa-solid fa-floppy-disk"></i>
                                    Guardar disponibilidad {{ $modalidad }}
                                </button>
                            </div>
                        </div>
                    </form>
                @endif

            </section>
        @endforeach

    </x-panel.card>

@endsection

@push('estilos')
    <link rel="stylesheet" href="{{ recurso('panel/css/disponibilidad.css') }}">
@endpush

@push('scripts')
    <script src="{{ recurso('panel/js/disponibilidad.js') }}"></script>
@endpush
