@props([
    'accion',
    'mensaje' => 'Esta accion no se puede deshacer.',
    'titulo' => 'Confirmar borrado',
    'etiqueta' => 'Eliminar',
    'soloIcono' => false,
])

<form method="POST" action="{{ $accion }}" class="confirmar"
      data-confirmar data-confirmar-titulo="{{ $titulo }}" data-confirmar-mensaje="{{ $mensaje }}">
    @csrf
    @method('DELETE')

    <button {{ $attributes->merge(['class' => 'boton boton--peligro' . ($soloIcono ? ' boton--icono' : '')]) }}
            type="submit" @if ($soloIcono) aria-label="{{ $etiqueta }}" @endif>
        <i class="fa-solid fa-trash-can"></i>
        @unless ($soloIcono)
            {{ $etiqueta }}
        @endunless
    </button>
</form>
