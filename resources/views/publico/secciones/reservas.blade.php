@php
    $reservas = app(\App\Services\ReservaService::class);
    $modalidades = $reservas->modalidades();
    $unica = count($modalidades) === 1 ? array_key_first($modalidades) : null;
@endphp

@if (modulo_activo('reservas'))
    <section class="layout__reservas" id="pide-cita" aria-labelledby="titulo-reservas">
        <div class="reservas__container">

            <header class="reservas__header">
                <p class="reservas__subtitle">{{ frase('reservas_titulo', 'Pide tu cita') }}</p>
                <h2 class="reservas__title" id="titulo-reservas">
                    {{ frase('reservas_subtitulo', 'Elige el dia y la hora que mejor te venga') }}
                </h2>
            </header>

            <div class="reservas__cuerpo">

                <div class="reservas__formulario">

                    @if ($reservas->enModoVacaciones())
                        <div class="aviso-web aviso-web--atencion">
                            <i class="fa-solid fa-umbrella-beach" aria-hidden="true"></i>
                            <div>
                                <p class="aviso-web__titulo">Ahora mismo no estoy cogiendo citas</p>
                                <div class="contenido-web">{!! nl2br(e($reservas->mensajeVacaciones())) !!}</div>
                            </div>
                        </div>
                    @elseif (empty($modalidades))
                        <div class="aviso-web aviso-web--info">
                            <i class="fa-solid fa-calendar-xmark" aria-hidden="true"></i>
                            <div>
                                <p class="aviso-web__titulo">Todavia no hay horario publicado</p>
                                <p>Escribeme o llamame y te doy cita al momento.</p>
                            </div>
                        </div>
                    @else
                        @include('publico.secciones.reservas-formulario', [
                            'modalidades' => $modalidades,
                            'unica' => $unica,
                        ])
                    @endif

                    <div class="reservas__contacto-directo">
                        <p class="reservas__contacto-texto">Si lo prefieres, hablamos directamente:</p>

                        <div class="reservas__contacto-botones">
                            @if ($sitio->telefonoEnlace())
                                <a class="appointment__btn appointment__btn--claro" href="tel:{{ $sitio->telefonoEnlace() }}">
                                    <i class="fa-solid fa-phone" aria-hidden="true"></i> {{ $sitio->telefono() }}
                                </a>
                            @endif

                            @if ($sitio->whatsapp())
                                <a class="appointment__btn appointment__btn--claro"
                                   href="{{ $sitio->whatsapp() }}" target="_blank" rel="noopener">
                                    <i class="fa-brands fa-whatsapp" aria-hidden="true"></i> WhatsApp
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- "Donde estamos" va al lado, con menos peso visual que la
                     reserva, como pide la especificacion. --}}
                <aside class="reservas__lateral">
                    @include('publico.secciones.contacto', ['comoLateral' => true])
                </aside>

            </div>

        </div>
    </section>

    @include('publico.partials.reserva-confirmada')
@endif
