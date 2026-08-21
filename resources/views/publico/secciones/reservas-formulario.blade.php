@php $elegida = old('modalidad', $unica); @endphp

<form class="reserva" method="POST" action="{{ route('publico.reservas.store') }}"
      data-reserva
      data-url-dias="{{ route('publico.reservas.disponibilidad') }}"
      data-url-huecos="{{ route('publico.reservas.huecos') }}"
      data-mes-limite="{{ $reservas->mesLimite() }}">
    @csrf

    <ol class="reserva__pasos" aria-label="Pasos de la reserva">
        <li class="reserva__paso-guia" data-guia="1"><span>1</span> Tipo de sesion</li>
        <li class="reserva__paso-guia" data-guia="2"><span>2</span> Dia</li>
        <li class="reserva__paso-guia" data-guia="3"><span>3</span> Hora</li>
        <li class="reserva__paso-guia" data-guia="4"><span>4</span> Tus datos</li>
    </ol>

    @if ($errors->any())
        <div class="aviso-web aviso-web--atencion" role="alert">
            <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
            <div>
                <p class="aviso-web__titulo">Revisa estos datos</p>
                <ul class="reserva__errores">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    {{-- Paso 1: modalidad --}}
    <fieldset class="reserva__paso" data-paso="1">
        <legend class="reserva__leyenda">¿Como prefieres la sesion?</legend>

        <div class="reserva__modalidades">
            @foreach ($modalidades as $clave => $datos)
                <label class="reserva__modalidad">
                    <input type="radio" name="modalidad" value="{{ $clave }}"
                           @checked($elegida === $clave) required>

                    <span class="reserva__modalidad-cuerpo">
                        <i class="fa-solid {{ $clave === 'online' ? 'fa-laptop' : 'fa-user-group' }}"
                           aria-hidden="true"></i>
                        <span class="reserva__modalidad-nombre">{{ $datos['nombre'] }}</span>
                        <span class="reserva__modalidad-detalle">{{ $datos['duracion'] }} minutos</span>

                        @if ($datos['dias'])
                            <span class="reserva__modalidad-dias">{{ implode(' · ', $datos['dias']) }}</span>
                        @endif
                    </span>
                </label>
            @endforeach
        </div>
    </fieldset>

    {{-- Paso 2: dia --}}
    <fieldset class="reserva__paso" data-paso="2" hidden>
        <legend class="reserva__leyenda">Elige el dia</legend>

        <div class="reserva__calendario" data-calendario>
            <div class="calendario__cabecera">
                <button class="calendario__mover" type="button" data-mover="anterior"
                        aria-label="Mes anterior">
                    <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
                </button>

                <p class="calendario__titulo" data-titulo aria-live="polite">Cargando…</p>

                <button class="calendario__mover" type="button" data-mover="siguiente"
                        aria-label="Mes siguiente">
                    <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
                </button>
            </div>

            <div class="calendario__semana" aria-hidden="true">
                <span>L</span><span>M</span><span>X</span><span>J</span><span>V</span><span>S</span><span>D</span>
            </div>

            <div class="calendario__dias" data-dias role="group" aria-label="Dias disponibles"></div>

            <p class="calendario__leyenda">
                <span class="calendario__marca calendario__marca--libre"></span> Con huecos libres
            </p>
        </div>

        <input type="hidden" name="fecha" value="{{ old('fecha') }}" data-campo-fecha>
    </fieldset>

    {{-- Paso 3: hora --}}
    <fieldset class="reserva__paso" data-paso="3" hidden>
        <legend class="reserva__leyenda" data-leyenda-horas>Elige la hora</legend>

        <div class="reserva__horas" data-horas role="group" aria-label="Horas disponibles"></div>

        <input type="hidden" name="hora" value="{{ old('hora') }}" data-campo-hora>
    </fieldset>

    {{-- Paso 4: datos --}}
    <fieldset class="reserva__paso" data-paso="4" hidden>
        <legend class="reserva__leyenda">Solo me falta saber quien eres</legend>

        <p class="reserva__resumen" data-resumen aria-live="polite"></p>

        <div class="reserva__campo">
            <label for="reserva-nombre">Tu nombre <span aria-hidden="true">*</span></label>
            <input id="reserva-nombre" type="text" name="nombre" value="{{ old('nombre') }}"
                   maxlength="100" required autocomplete="name">
        </div>

        <div class="reserva__campo">
            <label for="reserva-telefono">Tu telefono <span aria-hidden="true">*</span></label>
            <input id="reserva-telefono" type="tel" name="telefono" value="{{ old('telefono') }}"
                   inputmode="numeric" maxlength="15" required autocomplete="tel"
                   data-solo-numeros>
            <span class="reserva__ayuda">Solo numeros. Lo uso para confirmarte la cita.</span>
        </div>

        <div class="reserva__campo">
            <label for="reserva-motivo">¿Que te trae a consulta? <span class="reserva__opcional">(opcional)</span></label>
            <textarea id="reserva-motivo" name="motivo" rows="3" maxlength="500">{{ old('motivo') }}</textarea>
        </div>

        <label class="reserva__consentimiento">
            <input type="checkbox" name="consentimiento" value="1" @checked(old('consentimiento')) required>
            <span>Autorizo a guardar mis datos para gestionar esta cita.</span>
        </label>

        <button class="appointment__btn reserva__enviar" type="submit">
            <i class="fa-solid fa-calendar-check" aria-hidden="true"></i>
            Confirmar mi cita
        </button>
    </fieldset>
</form>
