@props([
    'tono' => 'info',
    'titulo' => null,
    'icono' => null,
])

@php
    $iconos = [
        'info' => 'fa-circle-info',
        'exito' => 'fa-circle-check',
        'error' => 'fa-circle-exclamation',
        'atencion' => 'fa-triangle-exclamation',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'aviso aviso--' . $tono]) }} role="alert">
    <i class="fa-solid {{ $icono ?? ($iconos[$tono] ?? 'fa-circle-info') }}"></i>
    <div class="aviso__contenido">
        @if ($titulo)
            <p class="aviso__titulo">{{ $titulo }}</p>
        @endif
        {{ $slot }}
    </div>
</div>
