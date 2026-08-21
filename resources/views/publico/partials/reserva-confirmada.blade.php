@if (session('reserva'))
    @php $reserva = session('reserva'); @endphp

    {{-- Se pinta ya abierto: la reserva acaba de guardarse en el servidor, asi
         que el paciente ve la confirmacion aunque el JS falle. --}}
    <div class="modal-web" data-modal-reserva role="dialog" aria-modal="true"
         aria-labelledby="titulo-reserva-ok">

        <div class="modal-web__caja">
            <div class="modal-web__icono">
                <i class="fa-solid fa-check" aria-hidden="true"></i>
            </div>

            <h2 class="modal-web__titulo" id="titulo-reserva-ok">Tu cita esta reservada</h2>

            <p class="modal-web__texto">
                Te espero el <strong>{{ $reserva['dia'] }}</strong> a las
                <strong>{{ $reserva['hora'] }}</strong>.
            </p>

            <ul class="modal-web__datos">
                <li>
                    <i class="fa-solid {{ $reserva['modalidad'] === 'Online' ? 'fa-laptop' : 'fa-user-group' }}"
                       aria-hidden="true"></i>
                    Sesion {{ strtolower($reserva['modalidad']) }} de {{ $reserva['duracion'] }} minutos
                </li>
                <li>
                    <i class="fa-solid fa-phone" aria-hidden="true"></i>
                    Te llamare para confirmarla
                </li>
            </ul>

            <div class="modal-web__acciones">
                <a class="appointment__btn" href="{{ $reserva['google'] }}"
                   target="_blank" rel="noopener">
                    <i class="fa-regular fa-calendar-plus" aria-hidden="true"></i>
                    Anadir a Google Calendar
                </a>

                <button class="appointment__btn appointment__btn--claro" type="button" data-cerrar-modal>
                    Cerrar
                </button>
            </div>
        </div>
    </div>
@endif
