@extends('instalacion.layout')

@section('titulo', 'Vamos a conectar tu base de datos')
@section('descripcion', 'PsicoCMS creara la base de datos por ti. Solo necesita los datos de tu servidor MySQL.')

@section('contenido')

    <div class="aviso aviso--info">
        <i class="fa-solid fa-circle-info"></i>
        <div>
            <p class="aviso__titulo">Si usas XAMPP en tu ordenador</p>
            <p>Normalmente el servidor es <strong>127.0.0.1</strong>, el puerto <strong>3306</strong>,
                el usuario <strong>root</strong> y la contrasena esta vacia. Elige el nombre que quieras
                para la base de datos: si no existe, la creamos automaticamente.</p>
        </div>
    </div>

    <div class="aviso aviso--info">
        <i class="fa-solid fa-triangle-exclamation"></i>
        <div>
            <p class="aviso__titulo">Si has arrancado con <code>php artisan serve</code></p>
            <p>Al guardar, PsicoCMS actualiza el fichero de configuracion y el servidor de desarrollo
                se reinicia solo, asi que el navegador puede mostrar un aviso de "no se puede acceder".
                <strong>No pasa nada</strong>: recarga la pagina y el asistente continuara donde lo dejaste.
                Para evitarlo, arranca el servidor con <code>php artisan serve --no-reload</code>.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('instalacion.guardar.base-datos') }}" id="formulario-bd">
        @csrf

        <div class="rejilla">
            <div class="campo">
                <label class="campo__etiqueta" for="db_host">Servidor</label>
                <input class="campo__control" type="text" id="db_host" name="db_host"
                       value="{{ old('db_host', '127.0.0.1') }}" required>
            </div>

            <div class="campo">
                <label class="campo__etiqueta" for="db_port">Puerto</label>
                <input class="campo__control" type="number" id="db_port" name="db_port"
                       value="{{ old('db_port', '3306') }}" required>
            </div>
        </div>

        <div class="campo">
            <label class="campo__etiqueta" for="db_database">Nombre de la base de datos</label>
            <input class="campo__control" type="text" id="db_database" name="db_database"
                   value="{{ old('db_database', 'psicocms') }}" required>
            <p class="campo__ayuda">Solo letras, numeros y guiones bajos. Se creara si todavia no existe.</p>
        </div>

        <div class="rejilla">
            <div class="campo">
                <label class="campo__etiqueta" for="db_username">Usuario de MySQL</label>
                <input class="campo__control" type="text" id="db_username" name="db_username"
                       value="{{ old('db_username', 'root') }}" required>
            </div>

            <div class="campo">
                <label class="campo__etiqueta" for="db_password">Contrasena de MySQL</label>
                <input class="campo__control" type="password" id="db_password" name="db_password"
                       value="{{ old('db_password') }}" autocomplete="off">
                <p class="campo__ayuda">Dejala vacia si tu MySQL no tiene contrasena.</p>
            </div>
        </div>

        <div class="aviso aviso--exito" id="resultado-conexion" hidden role="status">
            <i class="fa-solid fa-circle-check"></i>
            <p id="resultado-conexion-texto"></p>
        </div>

        <div class="acciones acciones--derecha">
            <button class="boton boton--secundario" type="button" id="probar-conexion">
                <i class="fa-solid fa-plug"></i> Probar conexion
            </button>
            <button class="boton" type="submit">
                Crear base de datos y continuar <i class="fa-solid fa-arrow-right"></i>
            </button>
        </div>
    </form>

@endsection

@push('scripts')
    <script src="{{ asset('panel/js/instalacion-bd.js') }}"></script>
@endpush
