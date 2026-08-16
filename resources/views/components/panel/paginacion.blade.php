@props([
    'coleccion',
    'nombre' => 'resultados',
])

@if ($coleccion->hasPages() || $coleccion->total() > 0)
    <nav class="paginacion" aria-label="Paginacion">

        <p class="paginacion__resumen">
            Mostrando {{ $coleccion->firstItem() ?? 0 }}-{{ $coleccion->lastItem() ?? 0 }}
            de {{ $coleccion->total() }} {{ $nombre }}
        </p>

        @if ($coleccion->hasPages())
            <div class="paginacion__enlaces">
                {{ $coleccion->onEachSide(1)->withQueryString()->links() }}
            </div>
        @endif

    </nav>
@endif
