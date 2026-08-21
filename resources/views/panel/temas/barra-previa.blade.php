{{-- Barra que solo aparece al previsualizar un tema desde el panel. --}}
<link rel="stylesheet" href="{{ recurso('panel/css/previsualizacion.css') }}">

<div class="aviso-previa">
    <p class="aviso-previa__texto">
        <i class="fa-solid fa-eye" aria-hidden="true"></i>
        Vista previa del tema <strong>{{ $previsualizacion['tema']['nombre'] }}</strong>
        en formato <strong>{{ $previsualizacion['modo'] === 'landing' ? 'una sola pagina' : 'varias paginas' }}</strong>.
        @if ($previsualizacion['esActivo'])
            Es el tema que tienes activo ahora mismo.
        @else
            Todavia no lo has activado: tu web sigue como estaba.
        @endif
    </p>

    <div class="aviso-previa__acciones">
        @foreach (['landing' => 'Una pagina', 'multipagina' => 'Varias paginas'] as $clave => $texto)
            <a class="aviso-previa__boton {{ $previsualizacion['modo'] === $clave ? 'aviso-previa__boton--activo' : '' }}"
               href="{{ route('panel.temas.previsualizar', ['slug' => $previsualizacion['tema']['slug'], 'modo' => $clave]) }}">
                {{ $texto }}
            </a>
        @endforeach

        <a class="aviso-previa__boton" href="{{ route('panel.temas.index') }}">
            <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Volver a Temas
        </a>
    </div>
</div>
