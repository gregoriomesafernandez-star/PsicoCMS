@props([
    'nombre',
    'etiqueta' => null,
    'activo' => false,
    'valor' => 1,
])

<label {{ $attributes->merge(['class' => 'toggle']) }}>
    <input class="toggle__entrada" type="checkbox" name="{{ $nombre }}" value="{{ $valor }}" @checked($activo)>
    <span class="toggle__control" aria-hidden="true"></span>

    @if ($etiqueta || trim($slot))
        <span class="toggle__texto">{{ $etiqueta }}{{ $slot }}</span>
    @endif
</label>
