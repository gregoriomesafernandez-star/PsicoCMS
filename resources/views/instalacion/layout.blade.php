<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Instalacion de PsicoCMS</title>
    <link rel="stylesheet" href="{{ asset('fonts/fontawesome-free-6.1.2-web/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('panel/css/instalacion.css') }}">
</head>
<body class="instalador">

    <main class="instalador__contenedor">

        <header class="instalador__cabecera">
            <p class="instalador__marca"><i class="fa-solid fa-spa"></i> PsicoCMS</p>
            <h1 class="instalador__titulo">@yield('titulo')</h1>
            <p class="instalador__descripcion">@yield('descripcion')</p>
        </header>

        <nav class="progreso" aria-label="Progreso de la instalacion">
            <ol class="progreso__lista">
                @foreach (['Base de datos', 'Tu cuenta', 'Tu perfil', 'Servicios', 'Tema'] as $indice => $nombre)
                    @php $numero = $indice + 1; @endphp
                    <li class="progreso__paso {{ $numero < $paso ? 'progreso__paso--hecho' : '' }} {{ $numero === $paso ? 'progreso__paso--activo' : '' }}">
                        <span class="progreso__numero">
                            @if ($numero < $paso)
                                <i class="fa-solid fa-check"></i>
                            @else
                                {{ $numero }}
                            @endif
                        </span>
                        <span class="progreso__nombre">{{ $nombre }}</span>
                    </li>
                @endforeach
            </ol>
        </nav>

        <section class="tarjeta">

            @if ($errors->any())
                <div class="aviso aviso--error" role="alert">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <div>
                        <p class="aviso__titulo">Revisa estos campos</p>
                        <ul class="aviso__lista">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            @yield('contenido')

        </section>

        <footer class="instalador__pie">
            <p>Paso {{ $paso }} de {{ $totalPasos }}</p>
        </footer>

    </main>

    @stack('scripts')

</body>
</html>
