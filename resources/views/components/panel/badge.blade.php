@props([
    'tono' => 'neutro',
    'icono' => null,
])

<span {{ $attributes->merge(['class' => 'badge badge--' . $tono]) }}>
    @if ($icono)
        <i class="fa-solid {{ $icono }}"></i>
    @endif
    {{ $slot }}
</span>
