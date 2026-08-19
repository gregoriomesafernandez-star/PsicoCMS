@extends('panel.layouts.app')

@php
    $esNuevo = ! $paciente->exists;
    $accion = $esNuevo ? route('panel.pacientes.store') : route('panel.pacientes.update', $paciente);
@endphp

@section('titulo', $esNuevo ? 'Nuevo paciente' : 'Editar ficha')
@section('encabezado', $esNuevo ? 'Nuevo paciente' : 'Editar ficha de ' . $paciente->nombre)
@section('subtitulo', $esNuevo
    ? 'Anade a mano una persona que te ha pedido cita por telefono, WhatsApp o en persona.'
    : 'Amplia o corrige los datos de esta persona.')

@section('migas')
    <x-panel.migas :items="array_values(array_filter([
        ['texto' => 'Inicio', 'url' => route('panel.inicio')],
        ['texto' => 'Pacientes', 'url' => route('panel.pacientes.index')],
        $esNuevo ? null : ['texto' => $paciente->nombre_completo, 'url' => route('panel.pacientes.show', $paciente)],
        ['texto' => $esNuevo ? 'Nuevo paciente' : 'Editar ficha'],
    ]))" />
@endsection

@section('acciones')
    <a class="boton boton--secundario"
       href="{{ $esNuevo ? route('panel.pacientes.index') : route('panel.pacientes.show', $paciente) }}">
        <i class="fa-solid fa-arrow-left"></i> {{ $esNuevo ? 'Volver al listado' : 'Volver a la ficha' }}
    </a>
@endsection

@section('contenido')

    @if ($errors->any())
        <x-panel.aviso tono="error" titulo="Revisa el formulario" class="separado">
            <p>Hay {{ $errors->count() }} campo(s) con errores. Estan marcados en rojo mas abajo.</p>
        </x-panel.aviso>
    @endif

    <form method="POST" action="{{ $accion }}" id="form-paciente">
        @csrf
        @unless ($esNuevo)
            @method('PUT')
        @endunless

        <div class="ficha-paciente">

            <div class="ficha-paciente__principal">

                <x-panel.card titulo="Datos de contacto" icono="fa-address-card" class="separado">
                    <div class="rejilla rejilla--2">
                        <x-panel.campo nombre="nombre" etiqueta="Nombre" :valor="$paciente->nombre"
                                       placeholder="Nombre" obligatorio />

                        <x-panel.campo nombre="apellidos" etiqueta="Apellidos" :valor="$paciente->apellidos"
                                       placeholder="Apellidos" />
                    </div>

                    <div class="rejilla rejilla--2">
                        <x-panel.campo nombre="telefono" etiqueta="Telefono" tipo="tel"
                                       :valor="$paciente->telefono" inputmode="numeric"
                                       ayuda="Es su identificador unico. Solo numeros." obligatorio />

                        <x-panel.campo nombre="email" etiqueta="Email (opcional)" tipo="email"
                                       :valor="$paciente->email" />
                    </div>

                    <x-panel.campo nombre="direccion" etiqueta="Direccion (opcional)"
                                   :valor="$paciente->direccion" />
                </x-panel.card>

                <x-panel.card titulo="Datos personales" icono="fa-id-card" class="separado"
                              descripcion="Todos opcionales. Rellena solo lo que necesites para tu trabajo.">
                    <div class="rejilla rejilla--3">
                        <x-panel.campo nombre="fecha_nacimiento" etiqueta="Fecha de nacimiento" tipo="date"
                                       :valor="$paciente->fecha_nacimiento?->toDateString()" />

                        <x-panel.campo nombre="genero" etiqueta="Genero" tipo="select"
                                       :valor="$paciente->genero"
                                       :opciones="['' => 'Sin especificar'] + \App\Models\Paciente::GENEROS" />

                        <x-panel.campo nombre="dni" etiqueta="DNI / NIE" :valor="$paciente->dni" />
                    </div>
                </x-panel.card>

                <x-panel.card titulo="Consulta" icono="fa-comment-medical"
                              descripcion="Datos generales del paciente, independientes del motivo de cada cita.">
                    <div class="campo">
                        <label class="campo__etiqueta" for="motivo_consulta">Motivo inicial de consulta</label>

                        <textarea class="campo__control editor-rico" data-editor="compacto"
                                  id="motivo_consulta" name="motivo_consulta"
                                  placeholder="Que le trae a terapia">{{ old('motivo_consulta', $paciente->motivo_consulta) }}</textarea>

                        <p class="campo__ayuda">
                            Es el motivo general de esta persona. Cada cita tiene ademas su propio motivo.
                        </p>

                        @error('motivo_consulta')
                            <p class="campo__error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p>
                        @enderror
                    </div>

                    <div class="campo">
                        <label class="campo__etiqueta" for="notas">Notas internas</label>

                        <textarea class="campo__control editor-rico" data-editor="compacto"
                                  id="notas" name="notas"
                                  placeholder="Anotaciones que solo veras tu">{{ old('notas', $paciente->notas) }}</textarea>

                        @error('notas')
                            <p class="campo__error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p>
                        @enderror
                    </div>
                </x-panel.card>

            </div>

            <aside class="ficha-paciente__lateral">
                <x-panel.card titulo="Seguimiento" icono="fa-clipboard-check">
                    <x-panel.campo nombre="estado" etiqueta="Estado" tipo="select"
                                   :valor="$paciente->estado ?? 'activo'"
                                   :opciones="\App\Models\Paciente::ESTADOS"
                                   ayuda="Activo: en terapia. Pausado: descanso temporal. Alta: proceso terminado."
                                   obligatorio />

                    @unless ($esNuevo)
                        <div class="dato-lateral">
                            <span class="dato-lateral__etiqueta">Origen</span>
                            <span class="dato-lateral__valor">{{ $paciente->origen_legible }}</span>
                        </div>

                        <div class="dato-lateral">
                            <span class="dato-lateral__etiqueta">Ficha creada</span>
                            <span class="dato-lateral__valor">{{ $paciente->created_at->format('d/m/Y') }}</span>
                        </div>
                    @endunless

                    <div class="ficha-paciente__acciones">
                        <button class="boton" type="submit">
                            <i class="fa-solid fa-floppy-disk"></i>
                            {{ $esNuevo ? 'Crear paciente' : 'Guardar cambios' }}
                        </button>
                    </div>
                </x-panel.card>
            </aside>

        </div>
    </form>

@endsection

@include('panel.partials.assets-editor')

@push('estilos')
    <link rel="stylesheet" href="{{ recurso('panel/css/citas.css') }}">
    <link rel="stylesheet" href="{{ recurso('panel/css/pacientes.css') }}">
@endpush
