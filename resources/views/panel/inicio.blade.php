@extends('panel.layouts.app')

@section('titulo', 'Inicio')
@section('encabezado', 'Hola, ' . $psicologa->nombre)
@section('subtitulo', 'Aqui tienes un resumen de tu actividad para hoy.')

@section('acciones')
    <a class="boton" href="{{ route('panel.citas.index') }}">
        <i class="fa-solid fa-plus"></i> Nueva cita
    </a>
@endsection

@section('contenido')

    @if ($config->modo_vacaciones)
        <x-panel.aviso tono="atencion" titulo="Modo vacaciones activado" class="separado">
            <p>Tus pacientes no pueden reservar cita desde la web.
                <a href="{{ route('panel.disponibilidad') }}">Ir a disponibilidad</a>.</p>
        </x-panel.aviso>
    @endif

    @if ($config->requiere_reconfig_online || $config->requiere_reconfig_presencial)
        <x-panel.aviso tono="atencion" titulo="Revisa tu horario semanal" class="separado">
            <p>Has cambiado la duracion o el descanso de tus sesiones y tus huecos han cambiado.
                <a href="{{ route('panel.disponibilidad') }}">Volver a marcar mi disponibilidad</a>.</p>
        </x-panel.aviso>
    @endif

    <div class="rejilla rejilla--4 separado">
        <x-panel.card class="metrica">
            <span class="metrica__icono metrica__icono--azul"><i class="fa-solid fa-calendar-day"></i></span>
            <p class="metrica__etiqueta">Citas de hoy</p>
            <p class="metrica__valor">{{ $metricas['citas_hoy'] }}</p>
        </x-panel.card>

        <x-panel.card class="metrica">
            <span class="metrica__icono metrica__icono--verde"><i class="fa-solid fa-users"></i></span>
            <p class="metrica__etiqueta">Pacientes activos</p>
            <p class="metrica__valor">{{ $metricas['pacientes'] }}</p>
        </x-panel.card>

        <x-panel.card class="metrica">
            <span class="metrica__icono metrica__icono--naranja"><i class="fa-solid fa-calendar-plus"></i></span>
            <p class="metrica__etiqueta">Proximas citas</p>
            <p class="metrica__valor">{{ $metricas['proximas'] }}</p>
        </x-panel.card>

        <x-panel.card class="metrica">
            <span class="metrica__icono metrica__icono--gris"><i class="fa-solid fa-newspaper"></i></span>
            <p class="metrica__etiqueta">Articulos del blog</p>
            <p class="metrica__valor">{{ $metricas['articulos'] }}</p>
        </x-panel.card>
    </div>

    <div class="rejilla rejilla--principal">

        <x-panel.card titulo="Tus citas de hoy" icono="fa-calendar-day">
            <x-slot:acciones>
                <a class="boton boton--secundario boton--pequeno" href="{{ route('panel.citas.index') }}">
                    Ver todas
                </a>
            </x-slot:acciones>

            @if ($citasHoy->isEmpty())
                <x-panel.empty-state icono="fa-mug-hot" titulo="Hoy no tienes citas"
                                     mensaje="Disfruta del dia. Cuando tengas citas programadas apareceran aqui.">
                    <a class="boton" href="{{ route('panel.citas.index') }}">
                        <i class="fa-solid fa-plus"></i> Anadir una cita
                    </a>
                </x-panel.empty-state>
            @else
                <ul class="agenda">
                    @foreach ($citasHoy as $cita)
                        <li class="agenda__item agenda__item--{{ $cita->modalidad }}">
                            <div class="agenda__hora">
                                <strong>{{ $cita->hora_corta }}</strong>
                                <small>{{ $cita->duracion }} min</small>
                            </div>

                            <div class="agenda__datos">
                                <p class="agenda__paciente">{{ $cita->paciente?->nombre_completo ?? 'Sin paciente' }}</p>
                                <p class="agenda__modalidad">
                                    <i class="fa-solid {{ $cita->modalidad === 'online' ? 'fa-video' : 'fa-location-dot' }}"></i>
                                    {{ $cita->modalidad_legible }}
                                </p>
                            </div>

                            <x-panel.badge :tono="$cita->tono_estado">{{ $cita->estado_legible }}</x-panel.badge>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-panel.card>

        <div class="rejilla">

            @if ($proximaCita)
                <x-panel.card titulo="Tu proxima cita" icono="fa-forward">
                    <p class="proxima__fecha">{{ $proximaCita->fecha->translatedFormat('l, d \d\e F') }}</p>
                    <p class="proxima__hora">{{ $proximaCita->hora_corta }}</p>
                    <p class="proxima__paciente">{{ $proximaCita->paciente?->nombre_completo }}</p>
                    <x-panel.badge :tono="$proximaCita->modalidad === 'online' ? 'info' : 'exito'">
                        {{ $proximaCita->modalidad_legible }}
                    </x-panel.badge>
                </x-panel.card>
            @endif

            <x-panel.card titulo="Disponibilidad semanal" icono="fa-clock">
                <x-slot:acciones>
                    <a class="boton boton--secundario boton--icono" href="{{ route('panel.disponibilidad') }}"
                       aria-label="Editar disponibilidad">
                        <i class="fa-solid fa-pen"></i>
                    </a>
                </x-slot:acciones>

                @php $sinDisponibilidad = collect($resumenOnline)->every(fn ($dia) => empty($dia['horas']))
                    && collect($resumenPresencial)->every(fn ($dia) => empty($dia['horas'])); @endphp

                @if ($sinDisponibilidad)
                    <x-panel.empty-state icono="fa-calendar-xmark" titulo="Sin disponibilidad configurada"
                                         mensaje="Marca los dias y horas en los que atiendes para que tus pacientes puedan reservar.">
                        <a class="boton" href="{{ route('panel.disponibilidad') }}">
                            <i class="fa-solid fa-clock"></i> Configurar ahora
                        </a>
                    </x-panel.empty-state>
                @else
                    <ul class="semana">
                        @foreach ($resumenOnline as $indice => $dia)
                            @php
                                $presencial = $resumenPresencial[$indice];
                                $tieneAlgo = $dia['horas'] || $presencial['horas'];
                            @endphp
                            <li class="semana__dia {{ $tieneAlgo ? '' : 'semana__dia--libre' }}">
                                <span class="semana__nombre">{{ $dia['nombre'] }}</span>

                                @if ($tieneAlgo)
                                    <span class="semana__horas">
                                        @if ($dia['horas'])
                                            <span class="semana__rango semana__rango--online">
                                                <i class="fa-solid fa-video"></i>
                                                {{ $dia['desde'] }} - {{ $dia['hasta'] }}
                                            </span>
                                        @endif
                                        @if ($presencial['horas'])
                                            <span class="semana__rango semana__rango--presencial">
                                                <i class="fa-solid fa-location-dot"></i>
                                                {{ $presencial['desde'] }} - {{ $presencial['hasta'] }}
                                            </span>
                                        @endif
                                    </span>
                                @else
                                    <span class="semana__libre">No disponible</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-panel.card>

            <x-panel.card titulo="Tu web" icono="fa-globe">
                <dl class="datos">
                    <div class="datos__fila">
                        <dt>Tema activo</dt>
                        <dd>{{ $tema['nombre'] ?? 'Sin tema' }}</dd>
                    </div>
                    <div class="datos__fila">
                        <dt>Formato</dt>
                        <dd>{{ $modoWeb === 'landing' ? 'Una sola pagina' : 'Varias paginas' }}</dd>
                    </div>
                    <div class="datos__fila">
                        <dt>Telefono para citas</dt>
                        <dd>{{ $perfil?->telefono_citas ?: 'Sin indicar' }}</dd>
                    </div>
                </dl>

                <a class="boton boton--secundario boton--pequeno" href="{{ route('home') }}"
                   target="_blank" rel="noopener">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i> Ver mi web
                </a>
            </x-panel.card>

        </div>

    </div>

    <x-panel.card titulo="Proximos pasos sugeridos" icono="fa-list-check" class="pasos-sugeridos">
        <x-slot:acciones>
            <span class="pasos-sugeridos__contador">
                {{ $pasos['completados'] }}/{{ $pasos['total'] }} completados
            </span>
        </x-slot:acciones>

        <div class="progreso-pasos">
            <div class="progreso-pasos__barra" role="progressbar"
                 aria-valuenow="{{ $pasos['porcentaje'] }}" aria-valuemin="0" aria-valuemax="100"
                 aria-label="Progreso de la configuracion">
                <span class="progreso-pasos__relleno" style="width: {{ $pasos['porcentaje'] }}%"></span>
            </div>
        </div>

        <ul class="lista-pasos">
            @foreach ($pasos['lista'] as $paso)
                @php $enlazable = $paso['ruta'] && ! $paso['hecho'] && menu_disponible($paso['ruta']); @endphp

                <li class="lista-pasos__item {{ $paso['hecho'] ? 'lista-pasos__item--hecho' : '' }}">
                    @if ($paso['hecho'])
                        <i class="lista-pasos__icono lista-pasos__icono--hecho fa-solid fa-circle-check"></i>
                    @else
                        <i class="lista-pasos__icono fa-regular fa-circle"></i>
                    @endif

                    @if ($enlazable)
                        <a class="lista-pasos__enlace" href="{{ menu_url($paso['ruta']) }}">
                            {{ $paso['titulo'] }}
                            <i class="lista-pasos__flecha fa-solid fa-arrow-right"></i>
                        </a>
                    @else
                        <span class="lista-pasos__texto">{{ $paso['titulo'] }}</span>

                        @if (! $paso['hecho'] && $paso['ruta'])
                            <span class="lista-pasos__pendiente">Proximamente</span>
                        @endif
                    @endif
                </li>
            @endforeach
        </ul>
    </x-panel.card>

@endsection

@push('estilos')
    <link rel="stylesheet" href="{{ asset('panel/css/citas.css') }}">
    <link rel="stylesheet" href="{{ asset('panel/css/inicio.css') }}">
@endpush
