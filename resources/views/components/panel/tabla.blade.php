@props([
    'cabeceras' => [],
    'titulo' => null,
])

<div {{ $attributes->merge(['class' => 'tabla__contenedor']) }}>
    <table class="tabla">
        @if ($titulo)
            <caption class="tabla__titulo">{{ $titulo }}</caption>
        @endif

        @if ($cabeceras)
            <thead class="tabla__cabecera">
                <tr>
                    @foreach ($cabeceras as $cabecera)
                        <th scope="col">{{ $cabecera }}</th>
                    @endforeach
                </tr>
            </thead>
        @endif

        <tbody class="tabla__cuerpo">
            {{ $slot }}
        </tbody>
    </table>
</div>
