@extends('instalacion.layout')

@section('titulo', 'Crea tu cuenta de acceso')
@section('descripcion', 'Esta sera la unica cuenta del sistema. Con estos tres datos entraras siempre a tu panel.')

@section('contenido')

    <div class="aviso aviso--info">
        <i class="fa-solid fa-shield-halved"></i>
        <div>
            <p class="aviso__titulo">Apunta bien estos datos</p>
            <p>Para entrar en tu panel necesitaras <strong>los tres a la vez</strong>: email, telefono y contrasena.
                Tu contrasena se guarda cifrada y nadie mas puede verla.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('instalacion.guardar.cuenta') }}">
        @csrf

        <div class="rejilla">
            <div class="campo">
                <label class="campo__etiqueta" for="nombre">Nombre</label>
                <input class="campo__control" type="text" id="nombre" name="nombre"
                       value="{{ old('nombre', $datos['cuenta']['nombre'] ?? '') }}" required>
            </div>

            <div class="campo">
                <label class="campo__etiqueta" for="apellidos">Apellidos</label>
                <input class="campo__control" type="text" id="apellidos" name="apellidos"
                       value="{{ old('apellidos', $datos['cuenta']['apellidos'] ?? '') }}">
            </div>
        </div>

        <div class="rejilla">
            <div class="campo">
                <label class="campo__etiqueta" for="email">Email de acceso</label>
                <input class="campo__control" type="email" id="email" name="email"
                       value="{{ old('email', $datos['cuenta']['email'] ?? '') }}" required>
            </div>

            <div class="campo">
                <label class="campo__etiqueta" for="telefono">Telefono de acceso</label>
                <input class="campo__control" type="tel" id="telefono" name="telefono"
                       value="{{ old('telefono', $datos['cuenta']['telefono'] ?? '') }}"
                       inputmode="numeric" required>
                <p class="campo__ayuda">Solo numeros. Se guarda sin espacios.</p>
            </div>
        </div>

        <div class="rejilla">
            <div class="campo">
                <label class="campo__etiqueta" for="password">Contrasena</label>
                <input class="campo__control" type="password" id="password" name="password"
                       autocomplete="new-password" required>
                <div class="fuerza">
                    <div class="fuerza__barra"><div class="fuerza__relleno" id="fuerza-relleno"></div></div>
                    <span class="fuerza__texto" id="fuerza-texto">Minimo 8 caracteres</span>
                </div>
            </div>

            <div class="campo">
                <label class="campo__etiqueta" for="password_confirmation">Repite la contrasena</label>
                <input class="campo__control" type="password" id="password_confirmation"
                       name="password_confirmation" autocomplete="new-password" required>
            </div>
        </div>

        <div class="acciones">
            <a class="boton boton--secundario" href="{{ route('instalacion.paso', ['paso' => 1]) }}">
                <i class="fa-solid fa-arrow-left"></i> Atras
            </a>
            <button class="boton" type="submit">
                Continuar <i class="fa-solid fa-arrow-right"></i>
            </button>
        </div>
    </form>

@endsection

@push('scripts')
    <script src="{{ recurso('panel/js/instalacion-password.js') }}"></script>
@endpush
