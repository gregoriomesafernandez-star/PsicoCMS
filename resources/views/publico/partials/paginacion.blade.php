@if ($paginator->hasPages())
    <nav class="paginacion-web" aria-label="Paginas del blog">

        @if ($paginator->onFirstPage())
            <span class="paginacion-web__paso es-inactivo" aria-hidden="true">
                <i class="fa-solid fa-chevron-left"></i>
            </span>
        @else
            <a class="paginacion-web__paso" href="{{ $paginator->previousPageUrl() }}" rel="prev"
               aria-label="Pagina anterior">
                <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
            </a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="paginacion-web__salto" aria-hidden="true">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $pagina => $url)
                    @if ($pagina == $paginator->currentPage())
                        <span class="paginacion-web__numero es-actual" aria-current="page">{{ $pagina }}</span>
                    @else
                        <a class="paginacion-web__numero" href="{{ $url }}"
                           aria-label="Pagina {{ $pagina }}">{{ $pagina }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a class="paginacion-web__paso" href="{{ $paginator->nextPageUrl() }}" rel="next"
               aria-label="Pagina siguiente">
                <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
            </a>
        @else
            <span class="paginacion-web__paso es-inactivo" aria-hidden="true">
                <i class="fa-solid fa-chevron-right"></i>
            </span>
        @endif

    </nav>
@endif
