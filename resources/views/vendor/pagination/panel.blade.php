@if ($paginator->hasPages())
    <ul class="pagination">

        @if ($paginator->onFirstPage())
            <span aria-disabled="true" class="pagination__inactivo">
                <i class="fa-solid fa-chevron-left"></i>
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Pagina anterior">
                <i class="fa-solid fa-chevron-left"></i>
            </a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span aria-disabled="true" class="pagination__inactivo">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span aria-current="page" class="pagination__actual">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Pagina siguiente">
                <i class="fa-solid fa-chevron-right"></i>
            </a>
        @else
            <span aria-disabled="true" class="pagination__inactivo">
                <i class="fa-solid fa-chevron-right"></i>
            </span>
        @endif

    </ul>
@endif
