@php $perfil = $sitio->perfil(); @endphp

@if (filled($perfil->sobre_mi))
    <section class="layout__experience" id="sobre-mi" aria-labelledby="titulo-sobre-mi">
        <div class="experience__container">

            @php $foto = $sitio->fotoSobreMi(); @endphp

            <div class="experience__container-img">
                <img class="experience__img" src="{{ $foto['url'] }}" alt="{{ $foto['alt'] }}" loading="lazy">
            </div>

            <div class="experience__data">
                <header class="experience__header">
                    <p class="experience__subtitle">{{ frase('sobre_mi_titulo', 'Sobre mi') }}</p>
                    <h2 class="experience__title" id="titulo-sobre-mi">
                        {{ frase('sobre_mi_subtitulo', 'Un acompanamiento cercano y profesional') }}
                    </h2>
                </header>

                <div class="experience__description contenido-web">{!! $perfil->sobre_mi !!}</div>

                @if (! $sitio->esLanding() && ($pagina ?? '') !== 'sobre-mi')
                    <a class="appointment__btn" href="{{ route('publico.sobre-mi') }}">Conoceme mejor</a>
                @endif
            </div>

        </div>
    </section>
@endif
