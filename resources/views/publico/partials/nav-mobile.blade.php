<nav class="layout__nav-mobile" aria-label="Menu movil">
    <div class="nav-mobile__left">

        <header class="nav-mobile__header-mobile">
            <div class="header-mobile__left">
                @if (imagen('logo'))
                    <div class="header-mobile__container-img">
                        <img class="header-mobile__img" src="{{ imagen('logo') }}" alt="{{ imagen_alt('logo') }}">
                    </div>
                @endif

                <div class="header-mobile__content">
                    <p class="header-mobile__title">{{ $sitio->nombre() }}</p>
                    <p class="header-mobile__subtitle">{{ frase('nav_subtitulo', 'Psicologia y acompanamiento') }}</p>
                </div>
            </div>

            <button class="header__btn-close" type="button" aria-label="Cerrar el menu">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </header>

        <ul class="nav-mobile__list-mobile">
            @foreach ($sitio->navegacion() as $enlace)
                <li class="list-mobile__item">
                    <a class="list-mobile__link" href="{{ $enlace['url'] }}">{{ $enlace['texto'] }}</a>
                </li>
            @endforeach
        </ul>

        <div class="contact-mobile__container">
            @if ($sitio->telefonoEnlace())
                <a class="contact-mobile__phone" href="tel:{{ $sitio->telefonoEnlace() }}">
                    <i class="fa-solid fa-phone"></i> {{ $sitio->telefono() }}
                </a>
            @endif

            @if ($sitio->email())
                <a class="contact-mobile__email" href="mailto:{{ $sitio->email() }}">
                    <i class="fa-regular fa-envelope"></i> {{ $sitio->email() }}
                </a>
            @endif
        </div>

    </div>
</nav>
