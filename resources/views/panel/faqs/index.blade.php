@extends('panel.layouts.app')

@section('titulo', 'Preguntas frecuentes')
@section('encabezado', 'Preguntas frecuentes')
@section('subtitulo', 'Responde de una vez las dudas que te repiten tus pacientes.')

@section('migas')
    <x-panel.migas :items="[
        ['texto' => 'Inicio', 'url' => route('panel.inicio')],
        ['texto' => 'Preguntas frecuentes'],
    ]" />
@endsection

@section('acciones')
    <button class="boton" type="button" id="boton-nueva-faq">
        <i class="fa-solid fa-plus"></i> Nueva pregunta
    </button>
@endsection

@section('contenido')

    <div class="rejilla rejilla--3 separado">
        <x-panel.card class="metrica">
            <span class="metrica__icono metrica__icono--azul"><i class="fa-solid fa-circle-question"></i></span>
            <p class="metrica__etiqueta">Preguntas en total</p>
            <p class="metrica__valor">{{ $resumen['total'] }}</p>
        </x-panel.card>

        <x-panel.card class="metrica">
            <span class="metrica__icono metrica__icono--verde"><i class="fa-solid fa-eye"></i></span>
            <p class="metrica__etiqueta">Visibles en la web</p>
            <p class="metrica__valor">{{ $resumen['activas'] }}</p>
        </x-panel.card>

        <x-panel.card class="metrica">
            <span class="metrica__icono metrica__icono--gris"><i class="fa-solid fa-eye-slash"></i></span>
            <p class="metrica__etiqueta">Ocultas</p>
            <p class="metrica__valor">{{ $resumen['ocultas'] }}</p>
        </x-panel.card>
    </div>

    @if ($resumen['total'] > 0)
        <x-panel.card titulo="Buscar" icono="fa-filter" class="separado">
            <form method="GET" action="{{ route('panel.faqs.index') }}" class="filtros">
                <div class="filtros__busqueda">
                    <x-panel.campo nombre="q" etiqueta="Buscar" :valor="$filtros['q'] ?? null"
                                   placeholder="Texto de la pregunta o de la respuesta" />

                    <div class="filtros__botones">
                        <button class="boton" type="submit">
                            <i class="fa-solid fa-magnifying-glass"></i> Buscar
                        </button>
                        <a class="boton boton--secundario" href="{{ route('panel.faqs.index') }}">
                            <i class="fa-solid fa-rotate-left"></i> Limpiar
                        </a>
                    </div>
                </div>
            </form>
        </x-panel.card>
    @endif

    <x-panel.card titulo="Tus preguntas" icono="fa-circle-question"
                  :descripcion="$faqs->isEmpty() ? null : 'Con las flechas cambias el orden en el que se ven en tu web.'"
                  class="separado">

        @if ($faqs->isEmpty())
            @if ($resumen['total'] > 0)
                <x-panel.empty-state icono="fa-magnifying-glass" titulo="Ninguna pregunta coincide"
                                     mensaje="Prueba con otras palabras o limpia la busqueda para verlas todas.">
                    <a class="boton boton--secundario" href="{{ route('panel.faqs.index') }}">
                        <i class="fa-solid fa-rotate-left"></i> Limpiar busqueda
                    </a>
                </x-panel.empty-state>
            @else
                <x-panel.empty-state icono="fa-circle-question" titulo="Todavia no hay preguntas"
                                     mensaje="Anade las dudas que te repiten tus pacientes y ahorrate contestarlas una y otra vez.">
                    <button class="boton" type="button" data-nueva-faq>
                        <i class="fa-solid fa-plus"></i> Crear la primera
                    </button>

                    <form method="POST" action="{{ route('panel.faqs.ejemplos') }}" class="vacio__formulario">
                        @csrf
                        <button class="boton boton--secundario" type="submit">
                            <i class="fa-solid fa-wand-magic-sparkles"></i> Anadir preguntas de ejemplo
                        </button>
                    </form>
                </x-panel.empty-state>
            @endif
        @else
            <ol class="faqs">
                @foreach ($faqs as $faq)
                    @php
                        $datosFaq = json_encode([
                            'id' => $faq->id,
                            'pregunta' => $faq->pregunta,
                            'respuesta' => $faq->respuesta,
                            'activa' => $faq->activa,
                        ]);
                    @endphp

                    <li class="faq {{ $faq->activa ? '' : 'faq--oculta' }}">

                        <div class="faq__orden">
                            <form method="POST" action="{{ route('panel.faqs.mover', [$faq, 'arriba']) }}">
                                @csrf
                                @method('PATCH')
                                <button class="faq__flecha" type="submit" @disabled($loop->first)
                                        aria-label="Subir esta pregunta" title="Subir">
                                    <i class="fa-solid fa-chevron-up"></i>
                                </button>
                            </form>

                            <span class="faq__posicion">{{ $loop->iteration }}</span>

                            <form method="POST" action="{{ route('panel.faqs.mover', [$faq, 'abajo']) }}">
                                @csrf
                                @method('PATCH')
                                <button class="faq__flecha" type="submit" @disabled($loop->last)
                                        aria-label="Bajar esta pregunta" title="Bajar">
                                    <i class="fa-solid fa-chevron-down"></i>
                                </button>
                            </form>
                        </div>

                        <div class="faq__cuerpo">
                            <p class="faq__pregunta">{{ $faq->pregunta }}</p>

                            @if (filled($faq->respuesta))
                                <div class="contenido-rico contenido-rico--compacto">{!! $faq->respuesta !!}</div>
                            @else
                                <p class="celda__secundario celda__secundario--cursiva">
                                    Esta pregunta todavia no tiene respuesta.
                                </p>
                            @endif
                        </div>

                        <div class="faq__acciones">
                            <form method="POST" action="{{ route('panel.faqs.activa', $faq) }}" class="faq__visibilidad">
                                @csrf
                                @method('PATCH')
                                <button class="boton boton--secundario boton--icono" type="submit"
                                        aria-label="{{ $faq->activa ? 'Ocultar de la web' : 'Mostrar en la web' }}"
                                        title="{{ $faq->activa ? 'Se ve en tu web. Pulsa para ocultarla' : 'Oculta. Pulsa para mostrarla' }}">
                                    <i class="fa-solid {{ $faq->activa ? 'fa-eye' : 'fa-eye-slash' }}"></i>
                                </button>
                            </form>

                            <button class="boton boton--secundario boton--icono" type="button"
                                    data-editar-faq data-faq="{{ $datosFaq }}"
                                    aria-label="Editar la pregunta" title="Editar">
                                <i class="fa-solid fa-pen"></i>
                            </button>

                            <x-panel.confirmar-borrado :accion="route('panel.faqs.destroy', $faq)"
                                                       titulo="Eliminar pregunta"
                                                       :mensaje="'Se borrara para siempre la pregunta: ' . $faq->pregunta"
                                                       etiqueta="Eliminar pregunta" solo-icono />
                        </div>

                    </li>
                @endforeach
            </ol>
        @endif

    </x-panel.card>

    <x-panel.card titulo="Asi lo veran tus pacientes" icono="fa-desktop"
                  descripcion="Vista previa de la seccion de preguntas frecuentes de tu web.">

        @if ($previsualizacion->isEmpty())
            <x-panel.empty-state icono="fa-eye-slash" titulo="No hay nada que mostrar todavia"
                                 mensaje="Cuando tengas preguntas visibles, apareceran aqui tal y como se veran en tu web." />
        @else
            <div class="acordeon">
                @foreach ($previsualizacion as $faq)
                    <article class="acordeon__pieza">
                        <button class="acordeon__cabecera" type="button"
                                aria-expanded="{{ $loop->first ? 'true' : 'false' }}"
                                aria-controls="respuesta-{{ $faq->id }}">
                            <span class="acordeon__pregunta">{{ $faq->pregunta }}</span>
                            <i class="fa-solid fa-chevron-down acordeon__flecha" aria-hidden="true"></i>
                        </button>

                        <div class="acordeon__respuesta" id="respuesta-{{ $faq->id }}" @if (! $loop->first) hidden @endif>
                            @if (filled($faq->respuesta))
                                <div class="contenido-rico contenido-rico--compacto">{!! $faq->respuesta !!}</div>
                            @else
                                <p class="celda__secundario celda__secundario--cursiva">Sin respuesta.</p>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        @endif

    </x-panel.card>

    <x-panel.modal id="modal-faq" titulo="Nueva pregunta" icono="fa-circle-question" ancho="ancho">
        <form method="POST" action="{{ route('panel.faqs.store') }}" id="form-faq">
            @csrf
            <input type="hidden" name="_method" value="POST" id="faq-metodo">

            <x-panel.campo nombre="pregunta" id="faq-pregunta" etiqueta="Pregunta" maxlength="255"
                           placeholder="Por ejemplo: ¿Cuanto dura una sesion?" obligatorio />

            <div class="campo">
                <label class="campo__etiqueta" for="faq-respuesta">Respuesta</label>

                <textarea class="campo__control editor-rico @error('respuesta') campo__control--error @enderror"
                          id="faq-respuesta" name="respuesta" data-editor="compacto"
                          placeholder="Contesta con tus palabras, como se lo explicarias a un paciente">{{ old('respuesta') }}</textarea>

                @error('respuesta')
                    <p class="campo__error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p>
                @enderror
            </div>

            <x-panel.toggle nombre="activa" id="faq-activa" activo
                            etiqueta="Mostrar esta pregunta en mi web" />
        </form>

        <x-slot:pie>
            <button class="boton boton--secundario" type="button" data-cerrar-modal>Cancelar</button>
            <button class="boton" type="submit" form="form-faq">
                <i class="fa-solid fa-floppy-disk"></i> Guardar pregunta
            </button>
        </x-slot:pie>
    </x-panel.modal>

@endsection

@include('panel.partials.assets-editor')

@push('estilos')
    <link rel="stylesheet" href="{{ asset('panel/css/citas.css') }}">
    <link rel="stylesheet" href="{{ asset('panel/css/pacientes.css') }}">
    <link rel="stylesheet" href="{{ asset('panel/css/faqs.css') }}">
@endpush

@push('scripts')
    @php
        $datosFaqs = [
            'urlCrear' => route('panel.faqs.store'),
            'urlBase' => url('/panel-psicologa/faqs'),
            'errores' => $errors->any(),
        ];
    @endphp

    <script id="datos-faqs" type="application/json">@json($datosFaqs)</script>
    <script src="{{ asset('panel/js/faqs.js') }}"></script>
@endpush
