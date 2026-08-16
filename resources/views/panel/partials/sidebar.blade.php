@php
    $menu = config('panel.menu');
@endphp

<aside class="sidebar" id="sidebar" aria-label="Menu principal">

    <div class="sidebar__marca">
        <span class="sidebar__logo"><i class="fa-solid fa-spa"></i></span>
        <span class="sidebar__nombre">
            <strong>PsicoCMS</strong>
            <small>Panel de gestion</small>
        </span>

        <button class="sidebar__cerrar" type="button" data-cerrar-menu aria-label="Cerrar el menu">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <nav class="sidebar__navegacion">

        <ul class="menu">
            @foreach ($menu['directos'] as $elemento)
                @include('panel.partials.menu-elemento', ['elemento' => $elemento])
            @endforeach
        </ul>

        @foreach ($menu['grupos'] as $indice => $grupo)
            @php $abierto = menu_grupo_activo($grupo['elementos']); @endphp

            <div class="menu__grupo" data-grupo="grupo-{{ $indice }}">
                <button class="menu__desplegable" type="button"
                        aria-expanded="{{ $abierto ? 'true' : 'false' }}"
                        aria-controls="grupo-{{ $indice }}">
                    <i class="menu__icono fa-solid {{ $grupo['icono'] }}"></i>
                    <span class="menu__texto">{{ $grupo['titulo'] }}</span>
                    <i class="menu__flecha fa-solid fa-chevron-down"></i>
                </button>

                <ul class="menu menu--anidado" id="grupo-{{ $indice }}" @if (! $abierto) hidden @endif>
                    @foreach ($grupo['elementos'] as $elemento)
                        @include('panel.partials.menu-elemento', ['elemento' => $elemento])
                    @endforeach
                </ul>
            </div>
        @endforeach

    </nav>

    <div class="sidebar__pie">

        <a class="menu__enlace menu__enlace--destacado" href="{{ route('home') }}" target="_blank" rel="noopener">
            <i class="menu__icono fa-solid fa-arrow-up-right-from-square"></i>
            <span class="menu__texto">Ver la web</span>
        </a>

        <a class="menu__enlace {{ menu_disponible('panel.ayuda') ? '' : 'menu__enlace--inactivo' }}"
           href="{{ menu_url('panel.ayuda') }}"
           @unless (menu_disponible('panel.ayuda')) aria-disabled="true" title="Disponible proximamente" @endunless>
            <i class="menu__icono fa-solid fa-circle-question"></i>
            <span class="menu__texto">Ayuda</span>
        </a>

        <form method="POST" action="{{ route('panel.salir') }}">
            @csrf
            <button class="menu__enlace menu__enlace--boton" type="submit">
                <i class="menu__icono fa-solid fa-arrow-right-from-bracket"></i>
                <span class="menu__texto">Cerrar sesion</span>
            </button>
        </form>

    </div>

</aside>
