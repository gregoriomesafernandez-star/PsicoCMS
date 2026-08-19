<div class="ordenable__orden">

    <form method="POST" action="{{ route($rutaMover, [$registro, 'arriba']) }}">
        @csrf
        @method('PATCH')
        <button class="ordenable__flecha" type="submit" @disabled($primero)
                aria-label="Subir una posicion" title="Subir">
            <i class="fa-solid fa-chevron-up"></i>
        </button>
    </form>

    <span class="ordenable__posicion">{{ $posicion }}</span>

    <form method="POST" action="{{ route($rutaMover, [$registro, 'abajo']) }}">
        @csrf
        @method('PATCH')
        <button class="ordenable__flecha" type="submit" @disabled($ultimo)
                aria-label="Bajar una posicion" title="Bajar">
            <i class="fa-solid fa-chevron-down"></i>
        </button>
    </form>

</div>
