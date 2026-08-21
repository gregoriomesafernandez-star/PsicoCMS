@php $redes = $sitio->redesVisibles(); @endphp

<footer class="layout__footer">

    {{-- El horario ya se muestra en "Donde estamos", no se repite aqui. --}}
    @if ($redes->isNotEmpty())
        <div class="footer__footer-top">
            <div class="footer__container">
                <div class="footer-top__social">
                    <p class="social__label">{{ frase('footer_redes', 'Sigueme en:') }}</p>

                    <ul class="social__list-social">
                        @foreach ($redes as $red)
                            @php [$nombre, $icono] = app(\App\Services\RedSocialService::class)->datos($red->red); @endphp

                            <li class="list-social__item">
                                <a class="list-social__link" href="{{ $red->url }}"
                                   target="_blank" rel="noopener" title="{{ $nombre }}">
                                    <i class="fa-brands {{ $icono }} list-social__ico"></i>
                                    <span class="visualmente-oculto">{{ $nombre }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <div class="footer__footer-bottom">
        <div class="footer__container">
            <p class="footer-bottom__copyright">
                © {{ date('Y') }} {{ $sitio->nombre() }}.
                {{ frase('footer_derechos', 'Todos los derechos reservados') }}.
                @if ($sitio->perfil()->num_colegiado)
                    <span class="footer-bottom__colegiada">
                        Colegiada n.o {{ $sitio->perfil()->num_colegiado }}
                    </span>
                @endif
            </p>
        </div>
    </div>

</footer>
