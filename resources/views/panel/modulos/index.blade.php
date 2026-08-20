@extends('panel.layouts.app')

@section('titulo', 'Modulos')
@section('encabezado', 'Que se ve en tu web')
@section('subtitulo', 'Enciende o apaga secciones enteras. Lo que apagues deja de verse, pero no se borra nada.')

@section('migas')
    <x-panel.migas :items="[
        ['texto' => 'Inicio', 'url' => route('panel.inicio')],
        ['texto' => 'Modulos'],
    ]" />
@endsection

@section('contenido')

    <div class="rejilla rejilla--3 separado">
        <x-panel.card class="metrica">
            <span class="metrica__icono metrica__icono--azul"><i class="fa-solid fa-toggle-on"></i></span>
            <p class="metrica__etiqueta">Secciones en total</p>
            <p class="metrica__valor">{{ $resumen['total'] }}</p>
        </x-panel.card>

        <x-panel.card class="metrica">
            <span class="metrica__icono metrica__icono--verde"><i class="fa-solid fa-eye"></i></span>
            <p class="metrica__etiqueta">Encendidas</p>
            <p class="metrica__valor">{{ $resumen['activos'] }}</p>
        </x-panel.card>

        <x-panel.card class="metrica">
            <span class="metrica__icono metrica__icono--gris"><i class="fa-solid fa-eye-slash"></i></span>
            <p class="metrica__etiqueta">Apagadas</p>
            <p class="metrica__valor">{{ $resumen['apagados'] }}</p>
        </x-panel.card>
    </div>

    <form method="POST" action="{{ route('panel.modulos.update') }}">
        @csrf
        @method('PUT')

        <x-panel.card titulo="Secciones de tu web" icono="fa-toggle-on"
                      descripcion="No todas las psicologas necesitan lo mismo. Deja encendido solo lo que vayas a usar.">

            <ul class="modulos">
                @foreach ($modulos as $clave => $modulo)
                    <li class="modulo {{ $modulo['activo'] ? '' : 'modulo--apagado' }}">

                        <span class="modulo__icono">
                            <i class="fa-solid {{ $modulo['icono'] }}" aria-hidden="true"></i>
                        </span>

                        <div class="modulo__cuerpo">
                            <p class="modulo__nombre">{{ $modulo['nombre'] }}</p>
                            <p class="modulo__descripcion">{{ $modulo['descripcion'] }}</p>
                            <p class="modulo__aviso">
                                <i class="fa-solid fa-circle-info" aria-hidden="true"></i> {{ $modulo['aviso'] }}
                            </p>
                        </div>

                        <div class="modulo__interruptor">
                            <x-panel.toggle nombre="modulos[]" :valor="$clave" :id="'modulo-' . $clave"
                                            :activo="$modulo['activo']"
                                            :etiqueta="$modulo['activo'] ? 'Encendido' : 'Apagado'" />
                        </div>

                    </li>
                @endforeach
            </ul>

            <x-slot:pie>
                <button class="boton" type="submit">
                    <i class="fa-solid fa-floppy-disk"></i> Guardar cambios
                </button>
            </x-slot:pie>
        </x-panel.card>
    </form>

@endsection

@push('estilos')
    <link rel="stylesheet" href="{{ recurso('panel/css/citas.css') }}">
    <link rel="stylesheet" href="{{ recurso('panel/css/modulos.css') }}">
@endpush

@push('scripts')
    <script src="{{ recurso('panel/js/modulos.js') }}"></script>
@endpush
