@extends('instalacion.layout')

@section('titulo', 'Cuentanos sobre ti')
@section('descripcion', 'Esta informacion es la que veran tus pacientes en la web. Podras cambiarla y ampliarla cuando quieras desde el panel.')

@section('contenido')

    <form method="POST" action="{{ route('instalacion.guardar.perfil') }}">
        @csrf

        <div class="campo">
            <label class="campo__etiqueta" for="eslogan">Frase gancho o eslogan</label>
            <input class="campo__control" type="text" id="eslogan" name="eslogan"
                   value="{{ old('eslogan', $datos['perfil']['eslogan'] ?? '') }}"
                   placeholder="Acompanamiento psicologico cercano y sin juicios" required>
            <p class="campo__ayuda">Es lo primero que se lee al entrar en tu web.</p>
        </div>

        <div class="rejilla--tres rejilla">
            <div class="campo">
                <label class="campo__etiqueta" for="num_colegiado">Numero de colegiada</label>
                <input class="campo__control" type="text" id="num_colegiado" name="num_colegiado"
                       value="{{ old('num_colegiado', $datos['perfil']['num_colegiado'] ?? '') }}">
            </div>

            <div class="campo">
                <label class="campo__etiqueta" for="telefono_citas">Telefono para citas</label>
                <input class="campo__control" type="tel" id="telefono_citas" name="telefono_citas"
                       value="{{ old('telefono_citas', $datos['perfil']['telefono_citas'] ?? '') }}"
                       inputmode="numeric" required>
            </div>

            <div class="campo">
                <label class="campo__etiqueta" for="email_citas">Email para citas</label>
                <input class="campo__control" type="email" id="email_citas" name="email_citas"
                       value="{{ old('email_citas', $datos['perfil']['email_citas'] ?? '') }}" required>
            </div>
        </div>

        <div class="campo">
            <label class="campo__etiqueta" for="sobre_mi">Sobre mi</label>
            <textarea class="campo__control" id="sobre_mi" name="sobre_mi" required
                      placeholder="Cuenta tu formacion, tu enfoque y como trabajas con tus pacientes.">{{ old('sobre_mi', $datos['perfil']['sobre_mi'] ?? '') }}</textarea>
        </div>

        <div class="rejilla">
            <div class="campo">
                <label class="campo__etiqueta" for="direccion">Direccion de la consulta</label>
                <input class="campo__control" type="text" id="direccion" name="direccion"
                       value="{{ old('direccion', $datos['perfil']['direccion'] ?? '') }}"
                       placeholder="Calle Ejemplo 12, 28001 Madrid">
            </div>

            <div class="campo">
                <label class="campo__etiqueta" for="lugar_consulta">Nombre del centro o lugar</label>
                <input class="campo__control" type="text" id="lugar_consulta" name="lugar_consulta"
                       value="{{ old('lugar_consulta', $datos['perfil']['lugar_consulta'] ?? '') }}"
                       placeholder="Centro de Psicologia Bienestar">
            </div>
        </div>

        <div class="acciones">
            <a class="boton boton--secundario" href="{{ route('instalacion.paso', ['paso' => 2]) }}">
                <i class="fa-solid fa-arrow-left"></i> Atras
            </a>
            <button class="boton" type="submit">
                Continuar <i class="fa-solid fa-arrow-right"></i>
            </button>
        </div>
    </form>

@endsection
