@php $planes = $sitio->planes(); @endphp

@if ($planes->isNotEmpty())
    <section class="layout__prices" id="precios" aria-labelledby="titulo-precios">
        <div class="prices__container">

            <header class="prices__header">
                <p class="prices__subtitle">{{ frase('planes_titulo', 'Planes y precios') }}</p>
                <h2 class="prices__title" id="titulo-precios">
                    {{ frase('planes_subtitulo', 'Sesiones presenciales y online') }}
                </h2>
            </header>

            <div class="prices__list-prices">
                @foreach ($planes->take(3) as $plan)
                    <article class="list-prices__price {{ $plan->destacado ? 'price--destacado' : '' }}">
                        <div class="price__content">
                            <div class="price__container-ico">
                                <i class="fa-solid {{ $plan->modalidad === 'online' ? 'fa-laptop' : 'fa-user-group' }} container-ico__ico"
                                   aria-hidden="true"></i>
                            </div>

                            <p class="price__online">
                                {{ $plan->precio_corto }}

                                @if ($plan->duracion)
                                    <span class="price__duracion">/ {{ $plan->duracion }} min</span>
                                @endif
                            </p>
                            <h3 class="price__text">{{ $plan->nombre }}</h3>

                            <ul class="price__list-details">
                                @forelse ($plan->caracteristicas ?? [] as $caracteristica)
                                    <li class="list-details__item">
                                        <i class="fa-solid fa-circle" aria-hidden="true"></i>
                                        <span>{{ $caracteristica }}</span>
                                    </li>
                                @empty
                                    @if ($plan->descripcion)
                                        <li class="list-details__item">
                                            <i class="fa-solid fa-circle" aria-hidden="true"></i>
                                            <span>{{ $plan->descripcion }}</span>
                                        </li>
                                    @endif
                                @endforelse
                            </ul>

                            @if (modulo_activo('reservas'))
                                <a class="appointment__btn"
                                   href="{{ $sitio->esLanding() ? '#pide-cita' : route('publico.pide-cita') }}">
                                    {{ frase('hero_boton_cita', 'Pedir cita') }}
                                </a>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>

        </div>
    </section>
@endif
