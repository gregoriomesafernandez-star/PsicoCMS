@php $servicios = $sitio->servicios(); @endphp

@if ($servicios->isNotEmpty())
    <section class="layout__services" id="servicios" aria-labelledby="titulo-servicios">
        <div class="services__container">

            <div class="services__header">
                <div class="services__title-box">
                    <p class="services__subtitle">{{ frase('servicios_titulo', 'Como puedo ayudarte') }}</p>
                    <h2 class="services__title" id="titulo-servicios">
                        {{ frase('servicios_subtitulo', 'Terapia adaptada a lo que necesitas en cada momento') }}
                    </h2>
                </div>
            </div>

            <div class="services__container-services">
                @foreach ($servicios->take($limite ?? 4) as $servicio)
                    <article class="services__service">
                        <div class="service__container-img">
                            <div class="service__overflow-img">
                                <img class="service__img"
                                     src="{{ $servicio->url_imagen ?: imagen('servicio_' . ($loop->iteration % 4 + 1)) }}"
                                     alt="{{ $servicio->titulo }}" loading="lazy">
                            </div>
                            <i class="fa-solid {{ $servicio->icono_visible }} service__ico" aria-hidden="true"></i>
                        </div>

                        <div class="service__body">
                            <h3 class="body__title">{{ $servicio->titulo }}</h3>
                            <p class="body__description">
                                {{ Str::limit(strip_tags((string) $servicio->descripcion), 95) }}
                            </p>
                        </div>
                    </article>
                @endforeach
            </div>

            @if (! $sitio->esLanding() && ($pagina ?? '') !== 'servicios' && $servicios->count() > ($limite ?? 4))
                <div class="services__mas">
                    <a class="appointment__btn" href="{{ route('publico.servicios') }}">Ver todos los servicios</a>
                </div>
            @endif

        </div>
    </section>
@endif
