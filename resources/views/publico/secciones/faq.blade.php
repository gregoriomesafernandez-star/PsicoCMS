@php $faqs = $sitio->faqs(); @endphp

@if ($faqs->isNotEmpty())
    <section class="layout__services" id="preguntas-frecuentes" aria-labelledby="titulo-faq">
        <div class="services__container">

            <div class="services__header">
                <div class="services__title-box">
                    <p class="services__subtitle">{{ frase('faq_titulo', 'Preguntas frecuentes') }}</p>
                    <h2 class="services__title" id="titulo-faq">
                        {{ frase('faq_subtitulo', 'Resolvemos las dudas mas habituales') }}
                    </h2>
                </div>
            </div>

            <div class="acordeon-web">
                @foreach ($faqs->take($limite ?? 6) as $faq)
                    <article class="acordeon-web__pieza {{ $loop->first ? 'acordeon-web__pieza--abierta' : '' }}">
                        <h3>
                            <button class="acordeon-web__cabecera" type="button"
                                    aria-expanded="{{ $loop->first ? 'true' : 'false' }}"
                                    aria-controls="faq-{{ $faq->id }}">
                                <span class="acordeon-web__pregunta">{{ $faq->pregunta }}</span>
                                <i class="fa-solid fa-chevron-down acordeon-web__flecha" aria-hidden="true"></i>
                            </button>
                        </h3>

                        <div class="acordeon-web__respuesta" id="faq-{{ $faq->id }}">
                            <div class="acordeon-web__cuerpo">
                                <div class="contenido-web">{!! $faq->respuesta !!}</div>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

        </div>
    </section>
@endif
