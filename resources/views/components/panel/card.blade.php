@props([
    'titulo' => null,
    'descripcion' => null,
    'icono' => null,
    'acciones' => null,
])

<section {{ $attributes->merge(['class' => 'card']) }}>

    @if ($titulo || $acciones)
        <header class="card__cabecera">
            <div class="card__titulos">
                <h2 class="card__titulo">
                    @if ($icono)
                        <i class="fa-solid {{ $icono }}"></i>
                    @endif
                    {{ $titulo }}
                </h2>

                @if ($descripcion)
                    <p class="card__descripcion">{{ $descripcion }}</p>
                @endif
            </div>

            @if ($acciones)
                <div class="card__acciones">{{ $acciones }}</div>
            @endif
        </header>
    @endif

    <div class="card__cuerpo">
        {{ $slot }}
    </div>

</section>
