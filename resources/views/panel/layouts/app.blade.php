@php
    $apariencia = app(\App\Services\AparienciaService::class)->actual();
@endphp
<!DOCTYPE html>
<html lang="es" data-tema="{{ $apariencia['tema'] }}" data-color="{{ $apariencia['color'] }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('titulo', 'Panel') | PsicoCMS</title>
    <link rel="stylesheet" href="{{ asset('fonts/fontawesome-free-6.1.2-web/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ recurso('panel/css/variables.css') }}">
    <link rel="stylesheet" href="{{ recurso('panel/css/layout.css') }}">
    <link rel="stylesheet" href="{{ recurso('panel/css/componentes.css') }}">
    <link rel="stylesheet" href="{{ recurso('panel/css/responsive.css') }}">
    @stack('estilos')
</head>
<body class="panel">

    <a class="salto-contenido" href="#contenido">Saltar al contenido</a>

    @include('panel.partials.sidebar')

    <div class="panel__principal">

        @include('panel.partials.header')

        <main class="panel__contenido" id="contenido">

            <header class="pagina__cabecera">
                <div>
                    @hasSection('migas')
                        @yield('migas')
                    @endif

                    <h1 class="pagina__titulo">@yield('encabezado', View::getSection('titulo'))</h1>

                    @hasSection('subtitulo')
                        <p class="pagina__subtitulo">@yield('subtitulo')</p>
                    @endif
                </div>

                @hasSection('acciones')
                    <div class="pagina__acciones">@yield('acciones')</div>
                @endif
            </header>

            @yield('contenido')

        </main>

    </div>

    <div class="panel__velo" data-cerrar-menu hidden></div>

    <div class="toasts" id="toasts" aria-live="polite" aria-atomic="true"></div>

    <x-panel.modal id="modal-confirmar" titulo="Confirmar" icono="fa-triangle-exclamation" ancho="estrecho">
        <p id="modal-confirmar-mensaje"></p>

        <x-slot:pie>
            <button class="boton boton--secundario" type="button" data-cerrar-modal>Cancelar</button>
            <button class="boton boton--peligro" type="button" id="modal-confirmar-aceptar">
                <i class="fa-solid fa-trash-can"></i> Si, eliminar
            </button>
        </x-slot:pie>
    </x-panel.modal>

    @include('panel.partials.modal-apariencia')

    @if (session('exito'))
        <script type="application/json" id="mensaje-flash">@json(['tono' => 'exito', 'texto' => session('exito')])</script>
    @elseif (session('error'))
        <script type="application/json" id="mensaje-flash">@json(['tono' => 'error', 'texto' => session('error')])</script>
    @endif

    <script src="{{ recurso('panel/js/sidebar.js') }}"></script>
    <script src="{{ recurso('panel/js/modal.js') }}"></script>
    <script src="{{ recurso('panel/js/toast.js') }}"></script>
    <script src="{{ recurso('panel/js/confirmar.js') }}"></script>
    <script src="{{ recurso('panel/js/contador.js') }}"></script>
    <script src="{{ recurso('panel/js/apariencia.js') }}"></script>
    @stack('scripts')

</body>
</html>
