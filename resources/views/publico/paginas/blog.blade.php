@extends('publico.layouts.app')

@section('contenido')

    <section class="layout__blog layout__blog--listado" aria-labelledby="titulo-blog">
        <div class="blog__container">

            <header class="blog__header">
                <p class="blog__subtitle">{{ frase('blog_titulo', 'Ultimos articulos') }}</p>
                <h1 class="blog__title" id="titulo-blog">
                    {{ $categoriaActiva
                        ? $categoriaActiva->nombre
                        : frase('blog_subtitulo', 'Recursos y reflexiones sobre salud mental') }}
                </h1>

                @if ($categoriaActiva && $categoriaActiva->descripcion)
                    <p class="blog__descripcion">{{ $categoriaActiva->descripcion }}</p>
                @endif
            </header>

            @if ($categorias->isNotEmpty())
                <nav class="blog__categorias" aria-label="Filtrar por categoria">
                    <a class="blog__categoria {{ $categoriaActiva ? '' : 'es-activa' }}"
                       href="{{ route('publico.blog') }}"
                       @unless ($categoriaActiva) aria-current="page" @endunless>
                        Todos <span class="blog__cuenta">{{ $total }}</span>
                    </a>

                    @foreach ($categorias as $categoria)
                        <a class="blog__categoria {{ $categoriaActiva?->is($categoria) ? 'es-activa' : '' }}"
                           href="{{ route('publico.blog.categoria', $categoria->slug) }}"
                           @if ($categoriaActiva?->is($categoria)) aria-current="page" @endif>
                            {{ $categoria->nombre }}
                            <span class="blog__cuenta">{{ $categoria->articulos_count }}</span>
                        </a>
                    @endforeach
                </nav>
            @endif

            @if ($articulos->isEmpty())
                <div class="aviso-web aviso-web--info">
                    <i class="fa-regular fa-newspaper" aria-hidden="true"></i>
                    <div>
                        <p class="aviso-web__titulo">
                            {{ $categoriaActiva ? 'Aun no hay articulos en esta categoria' : 'Aun no hay articulos publicados' }}
                        </p>
                        <p>
                            @if ($categoriaActiva)
                                Echa un vistazo al <a href="{{ route('publico.blog') }}">resto del blog</a>.
                            @else
                                Vuelve pronto: estoy preparando los primeros.
                            @endif
                        </p>
                    </div>
                </div>
            @else
                <div class="blog__rejilla">
                    @foreach ($articulos as $articulo)
                        @include('publico.partials.tarjeta-articulo', ['articulo' => $articulo])
                    @endforeach
                </div>

                {{ $articulos->onEachSide(1)->links('publico.partials.paginacion') }}
            @endif

        </div>
    </section>

@endsection
