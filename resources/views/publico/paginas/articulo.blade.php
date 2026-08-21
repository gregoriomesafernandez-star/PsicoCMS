@extends('publico.layouts.app')

@push('meta')
    @php $jsonArticulo = json_encode($estructurados, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); @endphp
    <script type="application/ld+json">{!! $jsonArticulo !!}</script>
@endpush

@section('contenido')

    <article class="layout__articulo">
        <div class="articulo__container">

            <nav class="articulo__migas" aria-label="Estas aqui">
                <a href="{{ route('publico.blog') }}">Blog</a>

                @if ($articulo->categoria)
                    <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
                    <a href="{{ route('publico.blog.categoria', $articulo->categoria->slug) }}">
                        {{ $articulo->categoria->nombre }}
                    </a>
                @endif
            </nav>

            <header class="articulo__cabecera">
                <h1 class="articulo__titulo">{{ $articulo->titulo }}</h1>

                <div class="articulo__meta">
                    @if ($articulo->publicado_at)
                        <span>
                            <i class="fa-regular fa-calendar" aria-hidden="true"></i>
                            <time datetime="{{ $articulo->publicado_at->toDateString() }}">
                                {{ ucfirst($articulo->publicado_at->translatedFormat('j \d\e F \d\e Y')) }}
                            </time>
                        </span>
                    @endif

                    <span>
                        <i class="fa-regular fa-user" aria-hidden="true"></i>
                        {{ $sitio->nombre() }}
                    </span>

                    @if ($articulo->vistas)
                        <span>
                            <i class="fa-regular fa-eye" aria-hidden="true"></i>
                            {{ $articulo->vistas }} {{ $articulo->vistas === 1 ? 'lectura' : 'lecturas' }}
                        </span>
                    @endif
                </div>
            </header>

            {{-- Si el articulo no trae foto propia llega la del hueco "blog",
                 la misma que ya se ve en su tarjeta del listado. --}}
            @if ($portada)
                <img class="articulo__portada" src="{{ $portada }}"
                     alt="{{ $articulo->url_imagen ? $articulo->titulo : imagen_alt('blog_defecto') }}">
            @endif

            @if ($articulo->extracto)
                <p class="articulo__entradilla">{{ $articulo->extracto }}</p>
            @endif

            {{-- Ya viene saneado por SanitizadorHtml al guardarse en el panel. --}}
            <div class="articulo__cuerpo contenido-web">{!! $articulo->contenido !!}</div>

            <footer class="articulo__pie">
                <p class="articulo__compartir-texto">¿Te ha servido? Compartelo</p>

                <ul class="articulo__compartir">
                    @foreach ($compartir as $red)
                        <li>
                            <a class="articulo__compartir-enlace" href="{{ $red['url'] }}"
                               target="_blank" rel="noopener" title="Compartir en {{ $red['nombre'] }}">
                                <i class="{{ $red['icono'] }}" aria-hidden="true"></i>
                                <span class="visualmente-oculto">Compartir en {{ $red['nombre'] }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </footer>

        </div>
    </article>

    @if ($relacionados->isNotEmpty())
        <section class="layout__blog layout__blog--relacionados" aria-labelledby="titulo-relacionados">
            <div class="blog__container">

                <header class="blog__header">
                    <p class="blog__subtitle">Sigue leyendo</p>
                    <h2 class="blog__title" id="titulo-relacionados">Otros articulos que te pueden interesar</h2>
                </header>

                <div class="blog__rejilla">
                    @foreach ($relacionados as $otro)
                        @include('publico.partials.tarjeta-articulo', ['articulo' => $otro])
                    @endforeach
                </div>

                <div class="seccion__mas">
                    <a class="appointment__btn appointment__btn--claro" href="{{ route('publico.blog') }}">
                        Ver todo el blog
                    </a>
                </div>

            </div>
        </section>
    @endif

@endsection
