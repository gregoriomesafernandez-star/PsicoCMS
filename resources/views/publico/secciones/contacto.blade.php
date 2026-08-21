@php
    $perfil = $sitio->perfil();
    // Cuando va al lado de la reserva, el ancla "pide-cita" ya la pone esa
    // seccion y aqui sobran los botones que llevan a ella.
    $comoLateral = $comoLateral ?? false;
@endphp

<section class="layout__contacto" id="{{ $comoLateral ? 'contacto' : 'pide-cita' }}"
         aria-labelledby="titulo-contacto">
    <div class="contacto__container">

        <header class="contacto__header">
            <p class="contacto__subtitle">{{ frase('contacto_titulo', 'Donde estamos') }}</p>
            <h2 class="contacto__title" id="titulo-contacto">
                {{ frase('contacto_subtitulo', 'Te espero en consulta o nos vemos online') }}
            </h2>
        </header>

        <div class="contacto__cuerpo">

            <div class="contacto__datos">
                @if ($perfil->lugar_consulta)
                    <p class="contacto__dato">
                        <i class="fa-solid fa-building" aria-hidden="true"></i>
                        <span>{{ $perfil->lugar_consulta }}</span>
                    </p>
                @endif

                @if ($perfil->direccion)
                    <p class="contacto__dato">
                        <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
                        <span>{{ $perfil->direccion }}</span>
                    </p>
                @endif

                @if ($perfil->horarios)
                    <p class="contacto__dato">
                        <i class="fa-solid fa-clock" aria-hidden="true"></i>
                        <span>{{ $perfil->horarios }}</span>
                    </p>
                @endif

                @if ($sitio->telefonoEnlace())
                    <p class="contacto__dato">
                        <i class="fa-solid fa-phone" aria-hidden="true"></i>
                        <a href="tel:{{ $sitio->telefonoEnlace() }}">{{ $sitio->telefono() }}</a>
                    </p>
                @endif

                @if ($sitio->email())
                    <p class="contacto__dato">
                        <i class="fa-regular fa-envelope" aria-hidden="true"></i>
                        <a href="mailto:{{ $sitio->email() }}">{{ $sitio->email() }}</a>
                    </p>
                @endif

                <div class="contacto__botones">
                    @if ($sitio->whatsapp())
                        <a class="appointment__btn" href="{{ $sitio->whatsapp() }}" target="_blank" rel="noopener">
                            <i class="fa-brands fa-whatsapp" aria-hidden="true"></i> WhatsApp
                        </a>
                    @endif

                    @if (modulo_activo('reservas') && ! $comoLateral)
                        <a class="appointment__btn appointment__btn--claro"
                           href="{{ $sitio->esLanding() ? '#pide-cita' : route('publico.pide-cita') }}">
                            {{ frase('hero_boton_cita', 'Pedir cita') }}
                        </a>
                    @endif
                </div>
            </div>

            @if ($sitio->mostrarMapa())
                <div class="contacto__mapa">
                    <iframe src="{{ $perfil->mapa_embed }}"
                            title="Mapa de la consulta de {{ $sitio->nombre() }}"
                            loading="lazy" referrerpolicy="no-referrer-when-downgrade"
                            allowfullscreen></iframe>
                </div>
            @endif

        </div>

    </div>
</section>
