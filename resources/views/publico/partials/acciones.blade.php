{{-- Barra de acciones fija en movil: lo que un paciente necesita a mano. --}}
<div class="acciones-movil">

    @if (modulo_activo('reservas'))
        <a class="acciones-movil__accion acciones-movil__accion--principal"
           href="{{ $sitio->esLanding() ? route('home') . '#pide-cita' : route('publico.pide-cita') }}">
            <i class="fa-solid fa-calendar-check" aria-hidden="true"></i>
            <span>Pedir cita</span>
        </a>
    @endif

    @if ($sitio->telefonoEnlace())
        <a class="acciones-movil__accion" href="tel:{{ $sitio->telefonoEnlace() }}">
            <i class="fa-solid fa-phone" aria-hidden="true"></i>
            <span>Llamar</span>
        </a>
    @endif

    @if ($sitio->whatsapp())
        <a class="acciones-movil__accion" href="{{ $sitio->whatsapp() }}" target="_blank" rel="noopener">
            <i class="fa-brands fa-whatsapp" aria-hidden="true"></i>
            <span>WhatsApp</span>
        </a>
    @endif

    @if ($sitio->email())
        <a class="acciones-movil__accion" href="mailto:{{ $sitio->email() }}">
            <i class="fa-regular fa-envelope" aria-hidden="true"></i>
            <span>Email</span>
        </a>
    @endif

</div>
