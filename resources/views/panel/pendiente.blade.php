<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Panel de PsicoCMS</title>
    <link rel="stylesheet" href="{{ asset('fonts/fontawesome-free-6.1.2-web/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('panel/css/instalacion.css') }}">
</head>
<body class="instalador">

    <main class="instalador__contenedor">

        <header class="instalador__cabecera">
            <p class="instalador__marca"><i class="fa-solid fa-spa"></i> PsicoCMS</p>
            <h1 class="instalador__titulo">Hola, {{ auth()->user()->nombre }}</h1>
            <p class="instalador__descripcion">Tu instalacion se ha completado correctamente.</p>
        </header>

        <section class="tarjeta">

            @if (session('exito'))
                <div class="aviso aviso--exito" role="status">
                    <i class="fa-solid fa-circle-check"></i>
                    <p>{{ session('exito') }}</p>
                </div>
            @endif

            <div class="aviso aviso--info">
                <i class="fa-solid fa-screwdriver-wrench"></i>
                <div>
                    <p class="aviso__titulo">Panel en construccion</p>
                    <p>El layout y el menu del dashboard se construyen en la FASE 3.
                        Esta pantalla es provisional y confirma que la instalacion y el acceso funcionan.</p>
                </div>
            </div>

            <div class="acciones acciones--derecha">
                <form method="POST" action="{{ route('panel.salir') }}">
                    @csrf
                    <button class="boton boton--secundario" type="submit">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i> Cerrar sesion
                    </button>
                </form>
            </div>

        </section>

    </main>

</body>
</html>
