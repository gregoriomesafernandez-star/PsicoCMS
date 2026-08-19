@extends('panel.layouts.app')

@section('titulo', $paciente->nombre_completo)
@section('encabezado', $paciente->nombre_completo)

@section('migas')
    <x-panel.migas :items="[
        ['texto' => 'Inicio', 'url' => route('panel.inicio')],
        ['texto' => 'Pacientes', 'url' => route('panel.pacientes.index')],
        ['texto' => $paciente->nombre_completo],
    ]" />
@endsection

@section('subtitulo')
    <x-panel.badge :tono="$paciente->tono_estado">{{ $paciente->estado_legible }}</x-panel.badge>
    <span class="pagina__meta">
        <i class="fa-solid fa-phone"></i> {{ $paciente->telefono }}
    </span>
@endsection

@section('acciones')
    <a class="boton boton--secundario" href="{{ route('panel.pacientes.index') }}">
        <i class="fa-solid fa-arrow-left"></i> Volver
    </a>
    <button class="boton boton--secundario" type="button" id="boton-nueva-cita">
        <i class="fa-solid fa-calendar-plus"></i> Nueva cita
    </button>
    <a class="boton boton--secundario" href="{{ route('panel.pacientes.proteccion-datos', $paciente) }}"
       title="Descarga el documento de proteccion de datos con sus datos ya puestos">
        <i class="fa-solid fa-file-shield"></i> Proteccion de datos
    </a>
    <a class="boton" href="{{ route('panel.pacientes.edit', $paciente) }}">
        <i class="fa-solid fa-pen"></i> Editar
    </a>
@endsection

@section('contenido')

    <div class="rejilla rejilla--4 separado">
        <x-panel.card class="metrica">
            <span class="metrica__icono metrica__icono--azul"><i class="fa-solid fa-calendar-check"></i></span>
            <p class="metrica__etiqueta">Citas en total</p>
            <p class="metrica__valor">{{ $estadisticas['total'] }}</p>
        </x-panel.card>

        <x-panel.card class="metrica">
            <span class="metrica__icono metrica__icono--verde"><i class="fa-solid fa-circle-check"></i></span>
            <p class="metrica__etiqueta">Sesiones completadas</p>
            <p class="metrica__valor">{{ $estadisticas['completadas'] }}</p>
        </x-panel.card>

        <x-panel.card class="metrica">
            <span class="metrica__icono metrica__icono--naranja"><i class="fa-solid fa-calendar-xmark"></i></span>
            <p class="metrica__etiqueta">Canceladas o sin asistir</p>
            <p class="metrica__valor">{{ $estadisticas['canceladas'] }}</p>
        </x-panel.card>

        <x-panel.card class="metrica">
            <span class="metrica__icono metrica__icono--gris"><i class="fa-solid fa-notes-medical"></i></span>
            <p class="metrica__etiqueta">Entradas de historia</p>
            <p class="metrica__valor">{{ $estadisticas['historias'] }}</p>
        </x-panel.card>
    </div>

    <x-panel.card titulo="Datos personales" icono="fa-id-card" class="separado">
        <x-slot:acciones>
            <x-panel.badge :tono="$paciente->origen === 'web' ? 'info' : 'neutro'"
                           :icono="$paciente->origen === 'web' ? 'fa-globe' : 'fa-hand'">
                {{ $paciente->origen_legible }}
            </x-panel.badge>
        </x-slot:acciones>

        <div class="datos-personales">

            <div class="dato">
                <span class="dato__icono dato__icono--gris"><i class="fa-solid fa-user"></i></span>
                <span class="dato__cuerpo">
                    <span class="dato__etiqueta">Nombre</span>
                    <span class="dato__valor">{{ $paciente->nombre }}</span>
                </span>
            </div>

            <div class="dato">
                <span class="dato__icono dato__icono--gris"><i class="fa-solid fa-address-card"></i></span>
                <span class="dato__cuerpo">
                    <span class="dato__etiqueta">Apellidos</span>
                    <span class="dato__valor">
                        {{ $paciente->apellidos ?: '' }}
                        @unless ($paciente->apellidos)
                            <span class="dato__valor--vacio">—</span>
                        @endunless
                    </span>
                </span>
            </div>

            <div class="dato">
                <span class="dato__icono dato__icono--gris"><i class="fa-solid fa-id-badge"></i></span>
                <span class="dato__cuerpo">
                    <span class="dato__etiqueta">DNI / NIE</span>
                    <span class="dato__valor">
                        {{ $paciente->dni ?: '' }}
                        @unless ($paciente->dni)
                            <span class="dato__valor--vacio">—</span>
                        @endunless
                    </span>
                </span>
            </div>

            <div class="dato">
                <span class="dato__icono dato__icono--verde"><i class="fa-solid fa-phone"></i></span>
                <span class="dato__cuerpo">
                    <span class="dato__etiqueta">Telefono</span>
                    <a class="dato__valor dato__valor--enlace" href="tel:{{ $paciente->telefono }}">
                        {{ $paciente->telefono }}
                    </a>
                </span>
            </div>

            <div class="dato">
                <span class="dato__icono dato__icono--verde"><i class="fa-brands fa-whatsapp"></i></span>
                <span class="dato__cuerpo">
                    <span class="dato__etiqueta">WhatsApp</span>
                    <a class="dato__valor dato__valor--enlace" target="_blank" rel="noopener"
                       href="https://wa.me/{{ $paciente->telefono_whatsapp }}">
                        Escribir por WhatsApp
                    </a>
                </span>
            </div>

            <div class="dato">
                <span class="dato__icono dato__icono--azul"><i class="fa-solid fa-envelope"></i></span>
                <span class="dato__cuerpo">
                    <span class="dato__etiqueta">Email</span>
                    @if ($paciente->email)
                        <a class="dato__valor dato__valor--enlace" href="mailto:{{ $paciente->email }}">
                            {{ $paciente->email }}
                        </a>
                    @else
                        <span class="dato__valor dato__valor--vacio">—</span>
                    @endif
                </span>
            </div>

            <div class="dato">
                <span class="dato__icono dato__icono--naranja"><i class="fa-solid fa-cake-candles"></i></span>
                <span class="dato__cuerpo">
                    <span class="dato__etiqueta">Fecha de nacimiento</span>
                    <span class="dato__valor">
                        @if ($paciente->fecha_nacimiento)
                            {{ $paciente->fecha_nacimiento->format('d/m/Y') }}
                            <span class="dato__apunte">({{ $paciente->edad }} anos)</span>
                        @else
                            <span class="dato__valor--vacio">—</span>
                        @endif
                    </span>
                </span>
            </div>

            <div class="dato">
                <span class="dato__icono dato__icono--morado"><i class="fa-solid fa-venus-mars"></i></span>
                <span class="dato__cuerpo">
                    <span class="dato__etiqueta">Genero</span>
                    <span class="dato__valor">
                        @if ($paciente->genero_legible)
                            <x-panel.badge tono="neutro">{{ $paciente->genero_legible }}</x-panel.badge>
                        @else
                            <span class="dato__valor--vacio">—</span>
                        @endif
                    </span>
                </span>
            </div>

            <div class="dato">
                <span class="dato__icono dato__icono--gris"><i class="fa-solid fa-location-dot"></i></span>
                <span class="dato__cuerpo">
                    <span class="dato__etiqueta">Direccion</span>
                    <span class="dato__valor">
                        {{ $paciente->direccion ?: '' }}
                        @unless ($paciente->direccion)
                            <span class="dato__valor--vacio">—</span>
                        @endunless
                    </span>
                </span>
            </div>

            <div class="dato">
                <span class="dato__icono dato__icono--gris"><i class="fa-solid fa-calendar-plus"></i></span>
                <span class="dato__cuerpo">
                    <span class="dato__etiqueta">Alta en el sistema</span>
                    <span class="dato__valor">{{ $paciente->created_at->format('d/m/Y') }}</span>
                </span>
            </div>

        </div>
    </x-panel.card>

    @if ($proxima)
        <x-panel.card titulo="Proxima cita" icono="fa-calendar-day" class="separado">
            <div class="proxima-cita">
                <div class="proxima-cita__fecha">
                    <span class="proxima-cita__dia">{{ $proxima->fecha->format('d') }}</span>
                    <span class="proxima-cita__mes">{{ $proxima->fecha->locale('es')->isoFormat('MMM') }}</span>
                </div>

                <div>
                    <p class="celda__principal">{{ $proxima->hora_corta }} - {{ $proxima->hora_fin_corta }}</p>
                    <p class="celda__secundario">{{ $proxima->fecha->locale('es')->isoFormat('dddd D [de] MMMM') }}</p>
                    <p class="proxima-cita__etiquetas">
                        <x-panel.badge :tono="$proxima->modalidad === 'online' ? 'info' : 'exito'"
                                       :icono="$proxima->modalidad === 'online' ? 'fa-video' : 'fa-location-dot'">
                            {{ $proxima->modalidad_legible }}
                        </x-panel.badge>
                        <x-panel.badge :tono="$proxima->tono_estado">{{ $proxima->estado_legible }}</x-panel.badge>
                    </p>
                </div>
            </div>
        </x-panel.card>
    @endif

    <div class="rejilla rejilla--2 separado">
        <x-panel.card titulo="Motivo inicial de consulta" icono="fa-comment-medical">
            @if (filled($paciente->motivo_consulta))
                <div class="contenido-rico contenido-rico--compacto">{!! $paciente->motivo_consulta !!}</div>
            @else
                <p class="celda__secundario">Todavia no has anotado el motivo inicial de consulta.</p>
            @endif

            <p class="celda__secundario separado-arriba">
                Cada cita tiene ademas su propio motivo, independiente de este.
            </p>
        </x-panel.card>

        <x-panel.card titulo="Notas internas" icono="fa-lock">
            <x-slot:acciones>
                <x-panel.badge tono="neutro" icono="fa-eye-slash">Solo tu las ves</x-panel.badge>
            </x-slot:acciones>

            @if (filled($paciente->notas))
                <div class="contenido-rico contenido-rico--compacto">{!! $paciente->notas !!}</div>
            @else
                <p class="celda__secundario">Sin notas.</p>
            @endif
        </x-panel.card>
    </div>

    <div class="rejilla rejilla--historial">

        <x-panel.card titulo="Historial de citas" icono="fa-clock-rotate-left"
                      :descripcion="$citas->isEmpty() ? null : 'De la mas reciente a la mas antigua.'">

            @if ($citas->isEmpty())
                <x-panel.empty-state icono="fa-calendar-plus" titulo="Todavia no tiene citas"
                                     mensaje="Cuando reserve desde tu web o le crees una cita, aparecera aqui.">
                    <button class="boton" type="button" data-nueva-cita>
                        <i class="fa-solid fa-calendar-plus"></i> Crear su primera cita
                    </button>
                </x-panel.empty-state>
            @else
                <x-panel.tabla :cabeceras="['Fecha', 'Horario', 'Modalidad', 'Estado']">
                    @foreach ($citas as $cita)
                        <tr>
                            <td>
                                <p class="celda__principal">{{ $cita->fecha->format('d/m/Y') }}</p>
                                <p class="celda__secundario">{{ $cita->fecha->locale('es')->isoFormat('dddd') }}</p>
                            </td>

                            <td class="celda__secundario">{{ $cita->hora_corta }} - {{ $cita->hora_fin_corta }}</td>

                            <td>
                                <x-panel.badge :tono="$cita->modalidad === 'online' ? 'info' : 'exito'"
                                               :icono="$cita->modalidad === 'online' ? 'fa-video' : 'fa-location-dot'">
                                    {{ $cita->modalidad_legible }}
                                </x-panel.badge>
                            </td>

                            <td>
                                <x-panel.badge :tono="$cita->tono_estado">{{ $cita->estado_legible }}</x-panel.badge>
                            </td>
                        </tr>
                    @endforeach
                </x-panel.tabla>
            @endif
        </x-panel.card>

        <x-panel.card titulo="Historia clinica" icono="fa-notes-medical"
                      :descripcion="$historias->isEmpty() ? null : 'Ultimas sesiones anotadas.'">
            <x-slot:acciones>
                <x-panel.badge tono="neutro" icono="fa-lock">Cifrada</x-panel.badge>
            </x-slot:acciones>

            @if ($historias->isEmpty())
                <x-panel.empty-state icono="fa-notes-medical" titulo="Sin entradas todavia"
                                     mensaje="Despues de cada sesion puedes anotar aqui como ha ido y adjuntar fotos o PDF.">
                    <a class="boton" href="{{ route('panel.historias.create', ['paciente' => $paciente->id]) }}">
                        <i class="fa-solid fa-plus"></i> Escribir la primera
                    </a>
                </x-panel.empty-state>
            @else
                <ul class="historia-resumen">
                    @foreach ($historias as $entrada)
                        <li class="historia-resumen__item">
                            <span class="historia-resumen__fecha">
                                {{ $entrada->fecha_sesion->locale('es')->isoFormat('D MMM YYYY') }}
                            </span>

                            <a class="historia-resumen__titulo" href="{{ route('panel.historias.show', $entrada) }}">
                                {{ $entrada->titulo_legible }}
                            </a>

                            @if (filled($entrada->resumen))
                                <span class="celda__secundario">{{ Str::limit($entrada->resumen, 90) }}</span>
                            @endif
                        </li>
                    @endforeach
                </ul>

                <div class="historia-resumen__acciones">
                    <a class="boton boton--secundario" href="{{ route('panel.historias.index', ['paciente' => $paciente->id]) }}">
                        <i class="fa-solid fa-timeline"></i> Ver las {{ $estadisticas['historias'] }}
                    </a>
                    <a class="boton" href="{{ route('panel.historias.create', ['paciente' => $paciente->id]) }}">
                        <i class="fa-solid fa-plus"></i> Nueva entrada
                    </a>
                </div>
            @endif
        </x-panel.card>

    </div>

    @include('panel.citas.modal-formulario', ['volver' => 'paciente'])

@endsection

@include('panel.partials.assets-editor')

@push('estilos')
    <link rel="stylesheet" href="{{ recurso('panel/css/citas.css') }}">
    <link rel="stylesheet" href="{{ recurso('panel/css/pacientes.css') }}">
    <link rel="stylesheet" href="{{ recurso('panel/css/historias.css') }}">
@endpush

@push('scripts')
    @php
        $datosCitas = [
            'urlHuecos' => route('panel.citas.huecos'),
            'urlCrear' => route('panel.citas.store'),
            'urlBase' => url('/panel-psicologa/citas'),
            'urlPacientes' => route('panel.pacientes.buscar'),
            'errores' => $errors->any(),
            'paciente' => [
                'paciente_nombre' => $paciente->nombre,
                'paciente_apellidos' => $paciente->apellidos,
                'paciente_telefono' => $paciente->telefono,
                'paciente_email' => $paciente->email,
            ],
        ];
    @endphp

    <script id="datos-citas" type="application/json">@json($datosCitas)</script>
    <script src="{{ recurso('panel/js/citas.js') }}"></script>
@endpush
