@php $enlace = route('publico.blog.articulo', $articulo->slug); @endphp

<article class="tarjeta-articulo">
    <a class="tarjeta-articulo__foto" href="{{ $enlace }}" tabindex="-1" aria-hidden="true">
        <img src="{{ $articulo->url_imagen ?: imagen('blog_defecto') }}"
             alt="" loading="lazy">

        @if ($articulo->publicado_at)
            <span class="tarjeta-articulo__fecha">
                {{ $articulo->publicado_at->format('d') }}
                <small>{{ ucfirst($articulo->publicado_at->translatedFormat('M')) }}</small>
            </span>
        @endif
    </a>

    <div class="tarjeta-articulo__cuerpo">
        @if ($articulo->categoria)
            <a class="tarjeta-articulo__categoria"
               href="{{ route('publico.blog.categoria', $articulo->categoria->slug) }}">
                {{ $articulo->categoria->nombre }}
            </a>
        @endif

        <h2 class="tarjeta-articulo__titulo">
            <a href="{{ $enlace }}">{{ $articulo->titulo }}</a>
        </h2>

        <p class="tarjeta-articulo__extracto">{{ $articulo->resumen }}</p>

        <a class="tarjeta-articulo__leer" href="{{ $enlace }}">
            Leer el articulo
            <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
        </a>
    </div>
</article>
