<nav class="layout__nav" aria-label="Menu principal">

    <div class="nav__left">
        <header class="nav__header">
            @if (imagen('logo'))
                <div class="header__container-img">
                    <img class="header__img" src="{{ imagen('logo') }}" alt="{{ imagen_alt('logo') }}">
                </div>
            @endif

            <div class="header__content">
                <p class="header__title">{{ $sitio->nombre() }}</p>
                <p class="header__subtitle">{{ frase('nav_subtitulo', 'Psicologia y acompanamiento') }}</p>
            </div>
        </header>

        <ul class="nav__list-nav">
            @foreach ($sitio->navegacion() as $enlace)
                <li class="list-nav__item">
                    <a class="list-nav__link {{ ($activo ?? '') === $enlace['ancla'] ? 'active' : '' }}"
                       href="{{ $enlace['url'] }}"
                       @if (($activo ?? '') === $enlace['ancla']) aria-current="page" @endif>
                        {{ $enlace['texto'] }}
                    </a>
                </li>
            @endforeach
        </ul>
    </div>

    <button class="nav__btn-menu-mobile" type="button" aria-label="Abrir el menu">
        <i class="fa-solid fa-bars"></i>
    </button>

    <div class="nav__contact">
        @if ($sitio->telefonoEnlace())
            <a class="contact__container-ico" href="tel:{{ $sitio->telefonoEnlace() }}" title="Llamar por telefono">
                <i class="fa-solid fa-phone-volume contact__phone-ico"></i>
                <span class="visualmente-oculto">Llamar</span>
            </a>

            <div class="contact__phone">
                <p class="phone__title">{{ frase('nav_pregunta', '¿Tienes alguna pregunta?') }}</p>
                <p class="phone__number">{{ $sitio->telefono() }}</p>
            </div>
        @endif

        <div class="contact__extra">
            @if ($sitio->whatsapp())
                <a class="contact__container-ico" href="{{ $sitio->whatsapp() }}"
                   target="_blank" rel="noopener" title="Escribir por WhatsApp">
                    <i class="fa-brands fa-whatsapp contact__whatsapp-ico"></i>
                    <span class="visualmente-oculto">WhatsApp</span>
                </a>
            @endif

            @if ($sitio->email())
                <a class="contact__container-ico" href="mailto:{{ $sitio->email() }}" title="Enviar un email">
                    <i class="fa-regular fa-envelope contact__email-ico"></i>
                    <span class="visualmente-oculto">Email</span>
                </a>
            @endif
        </div>
    </div>

</nav>
