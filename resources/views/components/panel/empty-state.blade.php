@props([
    'icono' => 'fa-inbox',
    'titulo' => 'Todavia no hay nada por aqui',
    'mensaje' => null,
])

<div {{ $attributes->merge(['class' => 'vacio']) }}>
    <span class="vacio__icono"><i class="fa-solid {{ $icono }}"></i></span>
    <p class="vacio__titulo">{{ $titulo }}</p>

    @if ($mensaje)
        <p class="vacio__mensaje">{{ $mensaje }}</p>
    @endif

    @if (trim($slot))
        <div class="vacio__acciones">{{ $slot }}</div>
    @endif
</div>
