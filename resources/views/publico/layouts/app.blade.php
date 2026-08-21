@php
    $seo = $sitio->seo($pagina ?? 'inicio', $tituloSeo ?? null, $descripcionSeo ?? null);
    $perfil = $sitio->perfil();
    $canonica = $canonica ?? url()->current();
    $imagenSocial = $perfil->url_foto ?: imagen('hero_principal');
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $seo['titulo'] }}</title>

    @if ($seo['descripcion'])
        <meta name="description" content="{{ $seo['descripcion'] }}">
    @endif

    <link rel="canonical" href="{{ $canonica }}">

    <meta property="og:type" content="{{ $tipoOg ?? 'website' }}">
    <meta property="og:site_name" content="{{ $sitio->nombre() }}">
    <meta property="og:title" content="{{ $seo['titulo'] }}">
    <meta property="og:url" content="{{ $canonica }}">
    <meta property="og:locale" content="es_ES">

    @if ($seo['descripcion'])
        <meta property="og:description" content="{{ $seo['descripcion'] }}">
    @endif

    @if ($imagenSocial)
        <meta property="og:image" content="{{ $imagenSocial }}">
        <meta name="twitter:image" content="{{ $imagenSocial }}">
    @endif

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $seo['titulo'] }}">

    @if ($seo['descripcion'])
        <meta name="twitter:description" content="{{ $seo['descripcion'] }}">
    @endif

    @stack('meta')

    <link rel="stylesheet" href="{{ asset('fonts/fontawesome-free-6.1.2-web/css/all.min.css') }}">
    @foreach ($sitio->hojas() as $hoja)
        <link rel="stylesheet" href="{{ $hoja }}">
    @endforeach
    <link rel="stylesheet" href="{{ recurso('publico/css/publico.css') }}">

    <script type="application/ld+json">@json($sitio->datosEstructurados(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)</script>
</head>
<body>

    @isset($previsualizacion)
        @include('panel.temas.barra-previa')
    @endisset

    <a class="salto-contenido" href="#contenido">Saltar al contenido</a>

    <div class="layout">

        <div class="layout__background"></div>

        <div class="layout__container-banner">

            @include('publico.partials.nav')
            @include('publico.partials.nav-mobile')

            @yield('portada')

        </div>

        {{-- Las paginas de seccion no llevan portada, asi que su contenido
             arrancaria por debajo de la cabecera, que flota en absolute. --}}
        <main id="contenido" @unless (View::hasSection('portada')) class="contenido--sin-portada" @endunless>
            @yield('contenido')
        </main>

        @include('publico.partials.footer')

        @include('publico.partials.acciones')

        <div class="layout__container-go-top">
            <button class="container-go-top__btn-go-top" type="button" aria-label="Volver arriba">
                <i class="fa-solid fa-arrow-up"></i>
            </button>
        </div>

    </div>

    <script src="{{ asset('themes/base/js/navMobile.js') }}"></script>
    <script src="{{ asset('themes/base/js/navFixed.js') }}"></script>
    <script src="{{ asset('themes/base/js/scroll-top.js') }}"></script>
    <script src="{{ recurso('publico/js/publico.js') }}"></script>

    @if (modulo_activo('reservas'))
        <script src="{{ recurso('publico/js/reservas.js') }}"></script>
    @endif
    @stack('scripts')

</body>
</html>
