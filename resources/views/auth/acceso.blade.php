<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Acceso | PsicoCMS</title>
    <link rel="stylesheet" href="{{ asset('fonts/fontawesome-free-6.1.2-web/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ recurso('panel/css/acceso.css') }}">
</head>
<body class="acceso">

    <main class="acceso__contenedor">

        <section class="acceso__presentacion">
            <p class="acceso__marca"><i class="fa-solid fa-spa"></i> PsicoCMS</p>

            <div class="acceso__bienvenida">
                @if ($perfil?->foto)
                    <img class="acceso__foto" src="{{ asset('storage/' . $perfil->foto) }}"
                         alt="Foto de {{ $perfil->nombre_publico }}">
                @else
                    <span class="acceso__foto acceso__foto--vacia"><i class="fa-solid fa-user"></i></span>
                @endif

                <h1 class="acceso__titulo">
                    @if ($perfil?->nombre_publico)
                        Hola de nuevo, {{ $perfil->nombre_publico }}
                    @else
                        Bienvenida de nuevo
                    @endif
                </h1>

                <p class="acceso__eslogan">
                    {{ $perfil?->eslogan ?: 'Accede a tu panel para gestionar tus citas, pacientes y tu web.' }}
                </p>
            </div>

            <ul class="acceso__ventajas">
                <li><i class="fa-solid fa-calendar-check"></i> Tus citas y tu calendario</li>
                <li><i class="fa-solid fa-users"></i> Pacientes e historias clinicas</li>
                <li><i class="fa-solid fa-globe"></i> Toda tu web, administrable</li>
            </ul>
        </section>

        <section class="acceso__panel">

            <div class="acceso__cabecera">
                <h2 class="acceso__subtitulo">Entra en tu panel</h2>
                <p class="acceso__ayuda">Necesitas los tres datos que elegiste al instalar PsicoCMS.</p>
            </div>

            @if (session('exito'))
                <div class="aviso aviso--exito" role="status">
                    <i class="fa-solid fa-circle-check"></i>
                    <p>{{ session('exito') }}</p>
                </div>
            @endif

            @if ($errors->any())
                <div class="aviso aviso--error" role="alert">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <div>
                        <p class="aviso__titulo">No hemos podido entrar</p>
                        <ul class="aviso__lista">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('acceso.enviar') }}" novalidate>
                @csrf

                <div class="campo">
                    <label class="campo__etiqueta" for="email">Email</label>
                    <span class="campo__grupo">
                        <i class="campo__icono fa-solid fa-envelope"></i>
                        <input class="campo__control" type="email" id="email" name="email"
                               value="{{ old('email') }}" autocomplete="username" autofocus required>
                    </span>
                </div>

                <div class="campo">
                    <label class="campo__etiqueta" for="telefono">Telefono</label>
                    <span class="campo__grupo">
                        <i class="campo__icono fa-solid fa-phone"></i>
                        <input class="campo__control" type="tel" id="telefono" name="telefono"
                               value="{{ old('telefono') }}" inputmode="numeric" required>
                    </span>
                </div>

                <div class="campo">
                    <label class="campo__etiqueta" for="password">Contrasena</label>
                    <span class="campo__grupo">
                        <i class="campo__icono fa-solid fa-lock"></i>
                        <input class="campo__control" type="password" id="password" name="password"
                               autocomplete="current-password" required>
                        <button class="campo__ojo" type="button" id="ver-password"
                                aria-label="Mostrar la contrasena">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </span>
                </div>

                <label class="recordarme">
                    <input type="checkbox" name="recordarme" value="1" @checked(old('recordarme'))>
                    <span>Mantener la sesion iniciada en este dispositivo</span>
                </label>

                <button class="boton boton--bloque" type="submit">
                    <i class="fa-solid fa-arrow-right-to-bracket"></i> Entrar
                </button>
            </form>

            <p class="acceso__pie">
                <i class="fa-solid fa-shield-halved"></i>
                Tu contrasena viaja cifrada y solo tu tienes acceso a este panel.
            </p>

        </section>

    </main>

    <script src="{{ recurso('panel/js/acceso.js') }}"></script>

</body>
</html>
