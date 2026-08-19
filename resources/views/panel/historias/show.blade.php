@extends('panel.layouts.app')

@php
    $paciente = $historia->paciente;
    $imagenes = $historia->adjuntos->filter(fn ($adjunto) => $adjunto->esImagen());
    $documentos = $historia->adjuntos->reject(fn ($adjunto) => $adjunto->esImagen());
@endphp

@section('titulo', $historia->titulo_legible)
@section('encabezado', $historia->titulo_legible)

@section('migas')
    <x-panel.migas :items="array_values(array_filter([
        ['texto' => 'Inicio', 'url' => route('panel.inicio')],
        ['texto' => 'Historias', 'url' => route('panel.historias.index')],
        $paciente ? ['texto' => $paciente->nombre_completo, 'url' => route('panel.historias.index', ['paciente' => $paciente->id])] : null,
        ['texto' => $historia->fecha_sesion->format('d/m/Y')],
    ]))" />
@endsection

@section('subtitulo')
    <span class="pagina__meta">
        <i class="fa-solid fa-calendar-day"></i>
        {{ $historia->fecha_sesion->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY') }}
    </span>
@endsection

@section('acciones')
    <a class="boton boton--secundario"
       href="{{ $paciente ? route('panel.historias.index', ['paciente' => $paciente->id]) : route('panel.historias.index') }}">
        <i class="fa-solid fa-arrow-left"></i> Volver
    </a>

    <a class="boton" href="{{ route('panel.historias.edit', $historia) }}">
        <i class="fa-solid fa-pen"></i> Editar
    </a>
@endsection

@section('contenido')

    <div class="entrada-detalle">

        <div class="entrada-detalle__principal">

            <x-panel.card titulo="Anotaciones de la sesion" icono="fa-file-lines" class="separado">
                <x-slot:acciones>
                    <x-panel.badge tono="neutro" icono="fa-lock">Guardado cifrado</x-panel.badge>
                </x-slot:acciones>

                @if (filled($historia->contenido))
                    <div class="contenido-rico">{!! $historia->contenido !!}</div>
                @else
                    <x-panel.empty-state icono="fa-pen" titulo="Esta entrada no tiene texto"
                                         mensaje="Puedes escribir aqui lo trabajado en la sesion cuando quieras.">
                        <a class="boton boton--secundario" href="{{ route('panel.historias.edit', $historia) }}">
                            <i class="fa-solid fa-pen"></i> Escribir ahora
                        </a>
                    </x-panel.empty-state>
                @endif
            </x-panel.card>

            <x-panel.card titulo="Archivos adjuntos" icono="fa-paperclip"
                          :descripcion="$historia->adjuntos->isEmpty() ? null : $historia->adjuntos->count() . ' archivo(s) en esta sesion.'">

                @if ($historia->adjuntos->isEmpty())
                    <x-panel.empty-state icono="fa-paperclip" titulo="Sin archivos adjuntos"
                                         mensaje="Puedes adjuntar fotos de tus anotaciones o PDF escaneados.">
                        <a class="boton boton--secundario" href="{{ route('panel.historias.edit', $historia) }}">
                            <i class="fa-solid fa-plus"></i> Anadir archivos
                        </a>
                    </x-panel.empty-state>
                @else

                    @if ($imagenes->isNotEmpty())
                        <div class="galeria">
                            @foreach ($imagenes as $adjunto)
                                <figure class="galeria__pieza">
                                    <button class="galeria__lupa" type="button"
                                            data-ver-imagen="{{ route('panel.historias.adjuntos.ver', $adjunto) }}"
                                            data-nombre="{{ $adjunto->nombre_original }}"
                                            aria-label="Ampliar {{ $adjunto->nombre_original }}">
                                        <img class="galeria__miniatura" loading="lazy"
                                             src="{{ route('panel.historias.adjuntos.ver', $adjunto) }}"
                                             alt="{{ $adjunto->nombre_original }}">
                                    </button>

                                    <figcaption class="galeria__pie">
                                        <span class="galeria__nombre">{{ $adjunto->nombre_original }}</span>
                                        <span class="galeria__meta">{{ $adjunto->tamano_legible }}</span>

                                        <span class="galeria__acciones">
                                            <a class="boton boton--secundario boton--icono"
                                               href="{{ route('panel.historias.adjuntos.descargar', $adjunto) }}"
                                               aria-label="Descargar {{ $adjunto->nombre_original }}" title="Descargar">
                                                <i class="fa-solid fa-download"></i>
                                            </a>

                                            <x-panel.confirmar-borrado :accion="route('panel.historias.adjuntos.destroy', $adjunto)"
                                                                       titulo="Eliminar archivo"
                                                                       :mensaje="'Se borrara para siempre el archivo ' . $adjunto->nombre_original . '. No se podra recuperar.'"
                                                                       etiqueta="Eliminar archivo" solo-icono />
                                        </span>
                                    </figcaption>
                                </figure>
                            @endforeach
                        </div>
                    @endif

                    @if ($documentos->isNotEmpty())
                        <ul class="adjuntos-lista {{ $imagenes->isNotEmpty() ? 'adjuntos-lista--tras-galeria' : '' }}">
                            @foreach ($documentos as $adjunto)
                                <li class="adjunto-fila">
                                    <i class="fa-solid {{ $adjunto->icono }} adjunto-fila__icono adjunto-fila__icono--pdf"></i>

                                    <span class="adjunto-fila__cuerpo">
                                        <a class="adjunto-fila__nombre" target="_blank" rel="noopener"
                                           href="{{ route('panel.historias.adjuntos.ver', $adjunto) }}">
                                            {{ $adjunto->nombre_original }}
                                        </a>
                                        <span class="adjunto-fila__meta">
                                            {{ $adjunto->tipo_legible }} - {{ $adjunto->tamano_legible }}
                                        </span>
                                    </span>

                                    <span class="acciones-fila">
                                        <a class="boton boton--secundario boton--icono" target="_blank" rel="noopener"
                                           href="{{ route('panel.historias.adjuntos.ver', $adjunto) }}"
                                           aria-label="Abrir {{ $adjunto->nombre_original }}" title="Abrir">
                                            <i class="fa-solid fa-up-right-from-square"></i>
                                        </a>

                                        <a class="boton boton--secundario boton--icono"
                                           href="{{ route('panel.historias.adjuntos.descargar', $adjunto) }}"
                                           aria-label="Descargar {{ $adjunto->nombre_original }}" title="Descargar">
                                            <i class="fa-solid fa-download"></i>
                                        </a>

                                        <x-panel.confirmar-borrado :accion="route('panel.historias.adjuntos.destroy', $adjunto)"
                                                                   titulo="Eliminar archivo"
                                                                   :mensaje="'Se borrara para siempre el archivo ' . $adjunto->nombre_original . '. No se podra recuperar.'"
                                                                   etiqueta="Eliminar archivo" solo-icono />
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @endif

                @endif
            </x-panel.card>

        </div>

        <aside class="entrada-detalle__lateral">

            <x-panel.card titulo="Paciente" icono="fa-user" class="separado">
                @if ($paciente)
                    <a class="paciente paciente--enlace" href="{{ route('panel.pacientes.show', $paciente) }}">
                        <span class="paciente__inicial paciente__inicial--grande">{{ $paciente->iniciales }}</span>
                        <span>
                            <span class="celda__principal">{{ $paciente->nombre_completo }}</span>
                            <span class="celda__secundario">{{ $paciente->telefono }}</span>
                        </span>
                    </a>

                    <div class="ficha-paciente__acciones">
                        <a class="boton boton--secundario" href="{{ route('panel.historias.index', ['paciente' => $paciente->id]) }}">
                            <i class="fa-solid fa-timeline"></i> Ver toda su historia
                        </a>
                    </div>
                @else
                    <p class="celda__secundario">El paciente de esta entrada ya no existe.</p>
                @endif
            </x-panel.card>

            <x-panel.card titulo="Cita vinculada" icono="fa-calendar-check" class="separado">
                @if ($historia->cita)
                    <p class="celda__principal">{{ $historia->cita->fecha->format('d/m/Y') }}</p>
                    <p class="celda__secundario">
                        {{ $historia->cita->hora_corta }} - {{ $historia->cita->hora_fin_corta }}
                    </p>

                    <p class="proxima-cita__etiquetas">
                        <x-panel.badge :tono="$historia->cita->modalidad === 'online' ? 'info' : 'exito'"
                                       :icono="$historia->cita->modalidad === 'online' ? 'fa-video' : 'fa-location-dot'">
                            {{ $historia->cita->modalidad_legible }}
                        </x-panel.badge>
                        <x-panel.badge :tono="$historia->cita->tono_estado">
                            {{ $historia->cita->estado_legible }}
                        </x-panel.badge>
                    </p>
                @else
                    <p class="celda__secundario">Esta entrada no esta vinculada a ninguna cita.</p>
                @endif
            </x-panel.card>

            <x-panel.card titulo="Eliminar entrada" icono="fa-triangle-exclamation">
                <p class="celda__secundario separado">
                    Se borrara el texto y todos sus archivos adjuntos. No se puede recuperar.
                </p>

                <x-panel.confirmar-borrado :accion="route('panel.historias.destroy', $historia)"
                                           titulo="Eliminar entrada"
                                           :mensaje="'Se borrara para siempre la entrada del ' . $historia->fecha_sesion->format('d/m/Y') . ($historia->adjuntos->isNotEmpty() ? ' y sus ' . $historia->adjuntos->count() . ' archivo(s) adjunto(s)' : '') . '. No se podra recuperar.'"
                                           etiqueta="Eliminar entrada" />
            </x-panel.card>

        </aside>

    </div>

    <x-panel.modal id="modal-imagen" titulo="Archivo adjunto" icono="fa-image" ancho="ancho">
        <img class="visor__imagen" id="visor-imagen" src="" alt="">
    </x-panel.modal>

@endsection

@push('estilos')
    <link rel="stylesheet" href="{{ recurso('panel/css/citas.css') }}">
    <link rel="stylesheet" href="{{ recurso('panel/css/pacientes.css') }}">
    <link rel="stylesheet" href="{{ recurso('panel/css/historias.css') }}">
@endpush

@push('scripts')
    <script src="{{ recurso('panel/js/historias.js') }}"></script>
@endpush
