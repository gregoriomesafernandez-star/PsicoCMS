@php $articulos = $sitio->articulos($limite ?? 3); @endphp

@if ($articulos->isNotEmpty())
    <section class="layout__blog" id="blog" aria-labelledby="titulo-blog">
        <div class="blog__container">

            <header class="blog__header">
                <p class="blog__subtitle">{{ frase('blog_titulo', 'Ultimos articulos') }}</p>
                <h2 class="blog__title" id="titulo-blog">
                    {{ frase('blog_subtitulo', 'Recursos y reflexiones sobre salud mental') }}
                </h2>
            </header>

            <div class="blog__articles">
                @foreach ($articulos as $articulo)
                    <article class="articles__article">
                        <div class="article__container-img">
                            @if ($articulo->publicado_at)
                                <div class="article__container-date">
                                    <p class="article__date">
                                        {{ $articulo->publicado_at->format('d') }} <br>
                                        {{ ucfirst($articulo->publicado_at->translatedFormat('M')) }}
                                    </p>
                                </div>
                            @endif

                            <img class="article__img"
                                 src="{{ $articulo->url_imagen ?: imagen('blog_defecto') }}"
                                 alt="{{ $articulo->titulo }}" loading="lazy">
                        </div>

                        <div class="article__cuerpo">
                            <h3 class="article__title">{{ $articulo->titulo }}</h3>

                            @php $resumen = $articulo->extracto ?: strip_tags((string) $articulo->contenido); @endphp

                            @if (filled($resumen))
                                <p class="article__extracto">{{ Str::limit($resumen, 160) }}</p>
                            @endif

                            <div class="article__bottom-content">
                                @if ($articulo->categoria)
                                    <div class="article__comments">
                                        <i class="fa-solid fa-bookmark comments__ico" aria-hidden="true"></i>
                                        <span class="comments__text">{{ $articulo->categoria->nombre }}</span>
                                    </div>
                                @endif

                                <div class="article__container-btn">
                                    <a class="article__btn-more" href="{{ route('publico.blog') }}">
                                        <span class="btn-more__text">Leer</span>
                                        <i class="fa-solid fa-arrow-right btn-more__arrow" aria-hidden="true"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            @if (! $sitio->esLanding() && ($pagina ?? '') !== 'blog')
                <div class="seccion__mas">
                    <a class="appointment__btn" href="{{ route('publico.blog') }}">Ver todo el blog</a>
                </div>
            @endif

        </div>
    </section>
@endif
