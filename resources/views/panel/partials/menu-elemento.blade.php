@php
    $disponible = menu_disponible($elemento['ruta']);
    $activo = $disponible && menu_activo($elemento['ruta']);
@endphp

<li class="menu__item">
    <a class="menu__enlace {{ $activo ? 'menu__enlace--activo' : '' }} {{ $disponible ? '' : 'menu__enlace--inactivo' }}"
       href="{{ menu_url($elemento['ruta']) }}"
       @if ($activo) aria-current="page" @endif
       @unless ($disponible) aria-disabled="true" title="Disponible proximamente" @endunless>
        <i class="menu__icono fa-solid {{ $elemento['icono'] }}"></i>
        <span class="menu__texto">{{ $elemento['titulo'] }}</span>

        @unless ($disponible)
            <i class="menu__candado fa-solid fa-lock" aria-hidden="true"></i>
        @endunless
    </a>
</li>
