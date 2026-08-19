@php
    $nombre = $perfil->nombre_publico ?: 'Tu nombre';
    $telefono = $perfil->telefono_citas ?: '600 000 000';
    $telefonoLimpio = preg_replace('/\D/', '', $telefono);
    $email = $perfil->email_citas ?: 'citas@tuconsulta.es';
    $secciones = ['inicio' => 'Inicio', 'sobre-mi' => 'Sobre mi', 'servicios' => 'Servicios', 'precios' => 'Precios', 'faq' => 'Preguntas'];
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Vista previa del tema {{ $tema['nombre'] }} | PsicoCMS</title>

    <link rel="stylesheet" href="{{ asset('fonts/fontawesome-free-6.1.2-web/css/all.min.css') }}">
    @foreach ($hojas as $hoja)
        <link rel="stylesheet" href="{{ $hoja }}">
    @endforeach
    <link rel="stylesheet" href="{{ asset('panel/css/previsualizacion.css') }}">
</head>
<body>

<div class="aviso-previa">
    <p class="aviso-previa__texto">
        <i class="fa-solid fa-eye" aria-hidden="true"></i>
        Vista previa del tema <strong>{{ $tema['nombre'] }}</strong>
        en formato <strong>{{ $modo === 'landing' ? 'una sola pagina' : 'varias paginas' }}</strong>.
        @if ($esActivo)
            Es el tema que tienes activo ahora mismo.
        @else
            Todavia no lo has activado: tu web sigue como estaba.
        @endif
    </p>

    <div class="aviso-previa__acciones">
        @foreach (['landing' => 'Una pagina', 'multipagina' => 'Varias paginas'] as $clave => $texto)
            <a class="aviso-previa__boton {{ $modo === $clave ? 'aviso-previa__boton--activo' : '' }}"
               href="{{ route('panel.temas.previsualizar', ['slug' => $tema['slug'], 'modo' => $clave]) }}">
                {{ $texto }}
            </a>
        @endforeach
    </div>
</div>

<div class="layout">

    <div class="layout__background"></div>

    <div class="layout__container-banner">

        <nav class="layout__nav">
            <div class="nav__left">
                <header class="nav__header">
                    <div class="header__content">
                        <h1 class="header__title">{{ $nombre }}</h1>
                        <h2 class="header__subtitle">Psicologia y asesoramiento</h2>
                    </div>
                </header>

                <ul class="nav__list-nav">
                    @foreach ($secciones as $ancla => $texto)
                        <li class="list-nav__item">
                            <a href="#{{ $ancla }}" class="list-nav__link">{{ $texto }}</a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="nav__contact">
                <a class="contact__container-ico" href="tel:{{ $telefonoLimpio }}" title="Telefono">
                    <i class="fa-solid fa-phone-volume contact__phone-ico"></i>
                </a>

                <div class="contact__phone">
                    <p class="phone__title">¿Tienes alguna pregunta?</p>
                    <h4 class="phone__number">{{ $telefono }}</h4>
                </div>

                <div class="contact__extra">
                    <a class="contact__container-ico" href="https://wa.me/{{ $telefonoLimpio }}"
                       target="_blank" rel="noopener" title="Whatsapp">
                        <i class="fa-brands fa-whatsapp contact__whatsapp-ico"></i>
                    </a>

                    <a class="contact__container-ico" href="mailto:{{ $email }}" title="Correo electronico">
                        <i class="fa-regular fa-envelope contact__email-ico"></i>
                    </a>
                </div>
            </div>
        </nav>

        <div class="layout__banner" id="inicio">
            <div class="banner__content">
                @if ($perfil->num_colegiado)
                    <div class="banner__sandwich">
                        <p class="sandwich__text">Colegiada n.o {{ $perfil->num_colegiado }}</p>
                    </div>
                @endif

                <h1 class="banner__title">{{ $perfil->eslogan ?: 'Un espacio seguro para volver a encontrarte' }}</h1>

                <div class="banner__appointment">
                    <a class="appointment__btn" href="tel:{{ $telefonoLimpio }}">Pide tu cita</a>
                    <p class="appointment__psico-name">{{ $nombre }}</p>
                </div>
            </div>

            <div class="banner__container-img">
                <img class="banner__img" src="{{ $perfil->url_foto ?: $imagenes . '/psicologa.png' }}"
                     alt="Foto de {{ $nombre }}">
            </div>

            <div class="banner__shapes">
                <div class="shapes__shape1"><img class="shape1__img" src="{{ $imagenes }}/shape1.png" alt=""></div>
                <div class="shapes__shape2"><img class="shape2__img" src="{{ $imagenes }}/shape2.png" alt=""></div>
            </div>
        </div>

    </div>

    @if ($especialidades->isNotEmpty())
        <div class="layout__therapies">
            <div class="therapies__container">
                @foreach ($especialidades->take(3) as $especialidad)
                    <div class="therapies__therapy">
                        <div class="therapy__container-ico">
                            <i class="fa-solid {{ $especialidad->icono_visible }} therapy__ico-person"></i>
                        </div>

                        <div class="therapy__content">
                            <p class="therapy__title">{{ $especialidad->nombre }}</p>
                            <p class="therapy__description">{{ Str::limit(strip_tags($especialidad->descripcion ?? ''), 70) }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if ($modo === 'multipagina')
        <div class="layout__services">
            <div class="services__container">
                <div class="services__header">
                    <div class="services__title-box">
                        <h3 class="services__subtitle">Tu web tendra estas paginas</h3>
                        <h2 class="services__title">Cada seccion en su propia direccion</h2>
                    </div>
                </div>

                <ul class="previa-paginas">
                    @foreach ([
                        '/' => 'Inicio',
                        '/sobre-mi' => 'Sobre mi',
                        '/servicios' => 'Servicios',
                        '/blog' => 'Blog',
                        '/preguntas-frecuentes' => 'Preguntas frecuentes',
                        '/pide-cita' => 'Pide cita',
                    ] as $ruta => $texto)
                        <li class="previa-pagina">
                            <span class="previa-pagina__nombre">{{ $texto }}</span>
                            <span class="previa-pagina__ruta">{{ $ruta }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div class="layout__experience" id="sobre-mi">
        <div class="experience__container">
            <div class="experience__container-img">
                <img class="experience__img" src="{{ $perfil->url_foto ?: $imagenes . '/exp1.jpg' }}"
                     alt="Foto de {{ $nombre }}">
            </div>

            <div class="experience__data">
                <header class="experience__header">
                    <h4 class="experience__subtitle">Sobre mi</h4>
                    <h2 class="experience__title">{{ $nombre }}</h2>
                </header>

                <div class="experience__description">
                    {!! $perfil->sobre_mi ?: '<p>Aqui aparecera tu presentacion. La escribes en Contenido de la web.</p>' !!}
                </div>
            </div>
        </div>
    </div>

    @if ($servicios->isNotEmpty())
        <div class="layout__services" id="servicios">
            <div class="services__container">
                <div class="services__header">
                    <div class="services__title-box">
                        <h3 class="services__subtitle">Servicios que ofrezco</h3>
                        <h2 class="services__title">En que puedo acompanarte</h2>
                    </div>
                </div>

                <div class="services__container-services">
                    @foreach ($servicios->take(4) as $servicio)
                        <div class="services__service">
                            <div class="service__container-img">
                                <div class="service__overflow-img">
                                    <img class="service__img"
                                         src="{{ $servicio->url_imagen ?: $imagenes . '/servicio' . ($loop->iteration % 4 + 1) . '.jpg' }}"
                                         alt="{{ $servicio->titulo }}">
                                </div>
                                <i class="fa-solid {{ $servicio->icono_visible }} service__ico"></i>
                            </div>

                            <div class="service__body">
                                <h3 class="body__title">{{ $servicio->titulo }}</h3>
                                <p class="body__description">{{ Str::limit(strip_tags($servicio->descripcion ?? ''), 90) }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    @if ($planes->isNotEmpty())
        <div class="layout__prices" id="precios">
            <div class="prices__container">
                <header class="prices__header">
                    <h4 class="prices__subtitle">Planes y precios</h4>
                    <h2 class="prices__title">Elige la modalidad que mejor te venga</h2>
                </header>

                <div class="prices__list-prices">
                    @foreach ($planes->take(3) as $plan)
                        <div class="list-prices__price">
                            <div class="price__content">
                                <div class="price__container-ico">
                                    <i class="fa-solid {{ $plan->modalidad === 'online' ? 'fa-globe' : 'fa-location-dot' }} container-ico__ico"></i>
                                </div>

                                <h2 class="price__online">{{ $plan->precio_visible }}</h2>
                                <p class="price__text">{{ $plan->nombre }} · {{ $plan->modalidad_visible }}</p>

                                <ul class="price__list-details">
                                    @forelse ($plan->caracteristicas ?? [] as $caracteristica)
                                        <li class="list-details__item">
                                            <i class="fa-solid fa-circle"></i>
                                            <span>{{ $caracteristica }}</span>
                                        </li>
                                    @empty
                                        <li class="list-details__item">
                                            <i class="fa-solid fa-circle"></i>
                                            <span>{{ Str::limit($plan->descripcion ?: 'Sesion completa', 60) }}</span>
                                        </li>
                                    @endforelse
                                </ul>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    @if ($faqs->isNotEmpty())
        <div class="layout__services" id="faq">
            <div class="services__container">
                <div class="services__header">
                    <div class="services__title-box">
                        <h3 class="services__subtitle">Preguntas frecuentes</h3>
                        <h2 class="services__title">Lo que suelen preguntarme</h2>
                    </div>
                </div>

                <div class="previa-faqs">
                    @foreach ($faqs->take(4) as $faq)
                        <article class="previa-faq">
                            <h3 class="previa-faq__pregunta">{{ $faq->pregunta }}</h3>
                            <div class="previa-faq__respuesta">{!! $faq->respuesta !!}</div>
                        </article>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <footer class="layout__footer">
        <div class="footer__footer-top">
            <div class="footer__container">
                <div class="footer-top__left">
                    <div class="footer-top__container-ico">
                        <i class="fa-solid fa-clock"></i>
                    </div>

                    <div class="footer-top__schedule">
                        <h4 class="schedule__text">Horario de consulta:</h4>
                        <p class="schedule__time">{{ $perfil->horarios ?: 'De lunes a viernes, 9:00 - 20:00' }}</p>
                    </div>
                </div>

                <div class="footer-top__social">
                    <p class="social__label">{{ $perfil->direccion ?: $perfil->lugar_consulta ?: 'Consulta online y presencial' }}</p>
                </div>
            </div>
        </div>

        <div class="footer__footer-bottom">
            <div class="footer__container">
                <p class="footer-bottom__copyright">© {{ date('Y') }} {{ $nombre }}</p>
            </div>
        </div>
    </footer>

</div>

</body>
</html>
