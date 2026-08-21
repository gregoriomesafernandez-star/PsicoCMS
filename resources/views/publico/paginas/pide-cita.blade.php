@extends('publico.layouts.app')

@section('contenido')

    <section class="layout__reservas" id="pide-cita" aria-labelledby="titulo-reservas">
        <div class="reservas__container">

            <header class="reservas__header">
                <p class="reservas__subtitle">{{ frase('reservas_titulo', 'Pide tu cita') }}</p>
                <h1 class="reservas__title" id="titulo-reservas">
                    {{ frase('reservas_subtitulo', 'Elige el dia y la hora que mejor te venga') }}
                </h1>
            </header>

            <div class="reservas__cuerpo">

                <div class="reservas__formulario">
                    @if ($modoVacaciones)
                        <div class="aviso-web aviso-web--atencion">
                            <i class="fa-solid fa-umbrella-beach" aria-hidden="true"></i>
                            <div>
                                <p class="aviso-web__titulo">Ahora mismo no estoy cogiendo citas</p>
                                <div class="contenido-web">{!! nl2br(e($mensajeVacaciones)) !!}</div>
                            </div>
                        </div>
                    @else
                        {{-- El formulario de reserva llega en la FASE 17. --}}
                        <div class="aviso-web aviso-web--info">
                            <i class="fa-solid fa-calendar-check" aria-hidden="true"></i>
                            <div>
                                <p class="aviso-web__titulo">Reserva en un minuto</p>
                                <p>Estamos terminando de preparar el calendario de reservas online.
                                    Mientras tanto, escribeme o llamame y te doy cita al momento.</p>
                            </div>
                        </div>
                    @endif

                    <div class="reservas__contacto-directo">
                        @if ($sitio->telefonoEnlace())
                            <a class="appointment__btn" href="tel:{{ $sitio->telefonoEnlace() }}">
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

                {{-- El bloque de "donde estamos" va aqui, con menos peso visual
                     que la reserva, como pide la especificacion. --}}
                <aside class="reservas__lateral">
                    @include('publico.secciones.contacto')
                </aside>

            </div>

        </div>
    </section>

@endsection
