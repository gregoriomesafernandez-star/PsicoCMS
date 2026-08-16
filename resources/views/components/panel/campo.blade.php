@props([
    'nombre',
    'etiqueta' => null,
    'tipo' => 'text',
    'valor' => null,
    'ayuda' => null,
    'obligatorio' => false,
    'placeholder' => null,
    'opciones' => [],
])

@php
    $id = $attributes->get('id', $nombre);
    $actual = old($nombre, $valor);
    $error = $errors->first($nombre);
@endphp

<div class="campo">

    @if ($etiqueta)
        <label class="campo__etiqueta" for="{{ $id }}">
            {{ $etiqueta }}
            @if ($obligatorio)
                <span class="campo__obligatorio" aria-hidden="true">*</span>
            @endif
        </label>
    @endif

    @if ($tipo === 'textarea')
        <textarea {{ $attributes->merge(['class' => 'campo__control' . ($error ? ' campo__control--error' : '')]) }}
                  id="{{ $id }}" name="{{ $nombre }}"
                  placeholder="{{ $placeholder }}" @required($obligatorio)>{{ $actual }}</textarea>

    @elseif ($tipo === 'select')
        <select {{ $attributes->merge(['class' => 'campo__control' . ($error ? ' campo__control--error' : '')]) }}
                id="{{ $id }}" name="{{ $nombre }}" @required($obligatorio)>
            @foreach ($opciones as $clave => $texto)
                <option value="{{ $clave }}" @selected((string) $actual === (string) $clave)>{{ $texto }}</option>
            @endforeach
        </select>

    @else
        <input {{ $attributes->merge(['class' => 'campo__control' . ($error ? ' campo__control--error' : '')]) }}
               type="{{ $tipo }}" id="{{ $id }}" name="{{ $nombre }}"
               value="{{ $actual }}" placeholder="{{ $placeholder }}" @required($obligatorio)>
    @endif

    @if ($ayuda && ! $error)
        <p class="campo__ayuda">{{ $ayuda }}</p>
    @endif

    @if ($error)
        <p class="campo__error"><i class="fa-solid fa-circle-exclamation"></i> {{ $error }}</p>
    @endif

</div>
