@php $perfil = $sitio->perfil(); @endphp

<div class="layout__banner" id="inicio">

    <div class="banner__content">
        @if ($perfil->num_colegiado)
            <div class="banner__sandwich">
                <p class="sandwich__text">Colegiada n.o {{ $perfil->num_colegiado }}</p>
            </div>
        @endif

        <h1 class="banner__title">
            {{ $perfil->eslogan ?: frase('hero_titulo', 'Un espacio seguro para volver a encontrarte') }}
        </h1>

        <div class="banner__appointment">
            @if (modulo_activo('reservas'))
                <a class="appointment__btn"
                   href="{{ $sitio->esLanding() ? '#pide-cita' : route('publico.pide-cita') }}">
                    {{ frase('hero_boton_cita', 'Pedir cita') }}
                </a>
            @elseif ($sitio->telefonoEnlace())
                <a class="appointment__btn" href="tel:{{ $sitio->telefonoEnlace() }}">
                    {{ frase('hero_boton_contacto', 'Hablamos') }}
                </a>
            @endif

            @if ($sitio->tieneContenido('sobre-mi'))
                <a class="appointment__btn appointment__btn--claro"
                   href="{{ $sitio->esLanding() ? '#sobre-mi' : route('publico.sobre-mi') }}">
                    {{ frase('hero_boton_conoceme', 'Conoceme') }}
                </a>
            @endif
        </div>

        <p class="appointment__psico-name">{{ $sitio->nombre() }}</p>

        @php
            $puntos = collect([
                frase('hero_punto_1', 'Sesiones presenciales y online'),
                frase('hero_punto_2', 'Confidencialidad total'),
                frase('hero_punto_3', 'Enfoque basado en evidencia'),
            ])->filter();
        @endphp

        @if ($puntos->isNotEmpty())
            <ul class="banner__puntos">
                @foreach ($puntos as $punto)
                    <li class="banner__punto">
                        <i class="fa-solid fa-check banner__punto-ico" aria-hidden="true"></i>
                        <span>{{ $punto }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    @php $foto = $sitio->fotoPortada(); @endphp

    <div class="banner__container-img">
        <img class="banner__img" src="{{ $foto['url'] }}" alt="{{ $foto['alt'] }}" width="520" height="620">
    </div>

    <div class="banner__shapes" aria-hidden="true">
        <div class="shapes__shape1"><img class="shape1__img" src="{{ imagen('hero_forma_1') }}" alt="" loading="lazy"></div>
        <div class="shapes__shape2"><img class="shape2__img" src="{{ imagen('hero_forma_2') }}" alt="" loading="lazy"></div>
    </div>

</div>
