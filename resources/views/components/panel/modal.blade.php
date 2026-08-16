@props([
    'id',
    'titulo' => null,
    'icono' => null,
    'pie' => null,
    'ancho' => 'medio',
])

<div class="modal" id="{{ $id }}" role="dialog" aria-modal="true"
     aria-labelledby="{{ $id }}-titulo" hidden>

    <div class="modal__fondo" data-cerrar-modal></div>

    <div class="modal__caja modal__caja--{{ $ancho }}">

        <header class="modal__cabecera">
            <h2 class="modal__titulo" id="{{ $id }}-titulo">
                @if ($icono)
                    <i class="fa-solid {{ $icono }}"></i>
                @endif
                {{ $titulo }}
            </h2>

            <button class="modal__cerrar" type="button" data-cerrar-modal aria-label="Cerrar">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </header>

        <div class="modal__cuerpo">
            {{ $slot }}
        </div>

        @if ($pie)
            <footer class="modal__pie">{{ $pie }}</footer>
        @endif

    </div>

</div>
