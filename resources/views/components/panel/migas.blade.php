@props(['items' => []])

<nav {{ $attributes->merge(['class' => 'migas']) }} aria-label="Ruta de navegacion">
    <ol class="migas__lista">
        @foreach ($items as $item)
            <li class="migas__item">
                @if (! empty($item['url']) && ! $loop->last)
                    <a class="migas__enlace" href="{{ $item['url'] }}">{{ $item['texto'] }}</a>
                @else
                    <span class="migas__actual" @if ($loop->last) aria-current="page" @endif>{{ $item['texto'] }}</span>
                @endif

                @unless ($loop->last)
                    <i class="fa-solid fa-chevron-right migas__flecha" aria-hidden="true"></i>
                @endunless
            </li>
        @endforeach
    </ol>
</nav>
