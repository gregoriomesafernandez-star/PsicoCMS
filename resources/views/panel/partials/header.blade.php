@php
    $psicologa = auth()->user();
    $iniciales = strtoupper(mb_substr($psicologa->nombre, 0, 1) . mb_substr($psicologa->apellidos ?? '', 0, 1));
@endphp

<header class="cabecera">

    <button class="cabecera__menu" type="button" data-abrir-menu aria-label="Abrir el menu" aria-controls="sidebar">
        <i class="fa-solid fa-bars"></i>
    </button>

    <form class="buscador" role="search" method="GET" action="{{ menu_url('panel.buscador') }}"
          @unless (menu_disponible('panel.buscador')) data-inactivo="true" @endunless>
        <label class="buscador__etiqueta" for="busqueda">Buscar en el panel</label>
        <i class="buscador__icono fa-solid fa-magnifying-glass"></i>
        <input class="buscador__campo" type="search" id="busqueda" name="q"
               placeholder="Buscar pacientes, citas, articulos..."
               value="{{ request('q') }}"
               @unless (menu_disponible('panel.buscador')) disabled title="Disponible proximamente" @endunless>
    </form>

    <div class="cabecera__acciones">

        <div class="avisos" data-menu-avisos>
            <button class="cabecera__boton" type="button" aria-expanded="false" aria-controls="menu-avisos"
                    title="Novedades">
                <i class="fa-solid fa-bell"></i>
                <span class="cabecera__boton-texto">Novedades</span>
            </button>

            <div class="avisos__menu" id="menu-avisos" hidden>
                <p class="avisos__titulo">Novedades</p>

                <x-panel.empty-state icono="fa-bell-slash" titulo="No tienes novedades"
                                     mensaje="Aqui apareceran las nuevas citas que reserven tus pacientes." />
            </div>
        </div>

        <button class="cabecera__boton" type="button" data-abrir-apariencia
                title="Cambiar el aspecto del panel">
            <i class="fa-solid fa-palette"></i>
            <span class="cabecera__boton-texto">Apariencia</span>
        </button>

        <a class="cabecera__boton {{ menu_disponible('panel.ayuda') ? '' : 'cabecera__boton--inactivo' }}"
           href="{{ menu_url('panel.ayuda') }}"
           title="{{ menu_disponible('panel.ayuda') ? 'Ayuda y tutorial' : 'Ayuda (disponible proximamente)' }}">
            <i class="fa-solid fa-circle-question"></i>
            <span class="cabecera__boton-texto">Ayuda</span>
        </a>

        <div class="perfil" data-menu-perfil>
            <button class="perfil__boton" type="button" aria-expanded="false" aria-controls="menu-perfil">
                @if ($psicologa->avatar)
                    <img class="perfil__avatar" src="{{ asset('storage/' . $psicologa->avatar) }}"
                         alt="Avatar de {{ $psicologa->nombre }}">
                @else
                    <span class="perfil__avatar perfil__avatar--iniciales">{{ $iniciales ?: 'P' }}</span>
                @endif

                <span class="perfil__nombre">{{ $psicologa->nombre }}</span>
                <i class="perfil__flecha fa-solid fa-chevron-down"></i>
            </button>

            <div class="perfil__menu" id="menu-perfil" hidden>
                <p class="perfil__cabecera">
                    <strong>{{ $psicologa->nombre_completo }}</strong>
                    <small>{{ $psicologa->email }}</small>
                </p>

                <a class="perfil__opcion {{ menu_disponible('panel.perfil') ? '' : 'perfil__opcion--inactiva' }}"
                   href="{{ menu_url('panel.perfil') }}"
                   @unless (menu_disponible('panel.perfil')) aria-disabled="true" title="Disponible proximamente" @endunless>
                    <i class="fa-solid fa-user-gear"></i> Mi perfil
                </a>

                <a class="perfil__opcion" href="{{ route('home') }}" target="_blank" rel="noopener">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i> Ver la web
                </a>

                <form method="POST" action="{{ route('panel.salir') }}">
                    @csrf
                    <button class="perfil__opcion perfil__opcion--boton" type="submit">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i> Cerrar sesion
                    </button>
                </form>
            </div>
        </div>

    </div>

</header>
