@php $especialidades = $sitio->especialidades(); @endphp

@if ($especialidades->isNotEmpty())
    <section class="layout__therapies" id="especialidades" aria-labelledby="titulo-especialidades">

        <header class="therapies__cabecera">
            <p class="services__subtitle">{{ frase('especialidades_titulo', 'Especialidades') }}</p>
            <h2 class="services__title" id="titulo-especialidades">
                {{ frase('especialidades_subtitulo', 'Areas en las que trabajo habitualmente') }}
            </h2>
        </header>

        <div class="therapies__container">
            @foreach ($especialidades->take(3) as $especialidad)
                <article class="therapies__therapy {{ $loop->index === 1 ? 'therapy-2' : '' }}">
                    <div class="therapy__container-ico">
                        <i class="fa-solid {{ $especialidad->icono_visible }} therapy__ico-person" aria-hidden="true"></i>
                    </div>

                    <div class="therapy__content">
                        <h3 class="therapy__title">{{ $especialidad->nombre }}</h3>
                        <p class="therapy__description">
                            {{ Str::limit(strip_tags((string) $especialidad->descripcion), 80) }}
                        </p>
                    </div>
                </article>
            @endforeach
        </div>
    </section>
@endif
