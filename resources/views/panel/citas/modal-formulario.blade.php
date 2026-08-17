<x-panel.modal id="modal-cita" titulo="Nueva cita" icono="fa-calendar-plus" ancho="medio">

    <form method="POST" action="{{ route('panel.citas.store') }}" id="form-cita">
        @csrf
        <input type="hidden" name="_method" value="POST" id="cita-metodo">
        <input type="hidden" name="volver" value="{{ $volver ?? 'citas' }}">

        <fieldset class="grupo-campos">
            <legend class="grupo-campos__titulo">Paciente</legend>

            <x-panel.campo nombre="paciente_nombre" id="cita-paciente-nombre"
                           etiqueta="Nombre del paciente"
                           placeholder="Nombre y apellidos" obligatorio />

            <div class="rejilla rejilla--2">
                <x-panel.campo nombre="paciente_telefono" id="cita-paciente-telefono"
                               etiqueta="Telefono" tipo="tel"
                               inputmode="numeric" ayuda="Es su identificador unico." obligatorio />

                <x-panel.campo nombre="paciente_email" id="cita-paciente-email"
                               etiqueta="Email (opcional)" tipo="email" />
            </div>
        </fieldset>

        <fieldset class="grupo-campos">
            <legend class="grupo-campos__titulo">Cita</legend>

            <div class="rejilla rejilla--2">
                <x-panel.campo nombre="modalidad" id="cita-modalidad" etiqueta="Modalidad" tipo="select"
                               :opciones="['presencial' => 'Presencial', 'online' => 'Online']" obligatorio />

                <x-panel.campo nombre="fecha" id="cita-fecha" etiqueta="Fecha" tipo="date"
                               :valor="now()->toDateString()" obligatorio />
            </div>

            <div class="campo">
                <label class="campo__etiqueta" for="cita-hora">
                    Hora <span class="campo__obligatorio" aria-hidden="true">*</span>
                </label>

                <div class="huecos" id="huecos-disponibles" role="group" aria-label="Huecos disponibles">
                    <p class="huecos__cargando">Elige modalidad y fecha para ver tus huecos libres.</p>
                </div>

                <input type="hidden" name="hora_inicio" id="cita-hora" value="{{ old('hora_inicio') }}">

                @error('hora_inicio')
                    <p class="campo__error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p>
                @enderror
            </div>

            <x-panel.campo nombre="estado" id="cita-estado" etiqueta="Estado" tipo="select"
                           :opciones="\App\Models\Cita::ESTADOS" valor="pendiente" obligatorio />

            <x-panel.campo nombre="motivo" id="cita-motivo"
                           etiqueta="Motivo de la consulta (opcional)" tipo="textarea"
                           placeholder="Que le trae a consulta" />

            <x-panel.campo nombre="notas" id="cita-notas"
                           etiqueta="Notas internas (opcional)" tipo="textarea"
                           placeholder="Notas que solo veras tu" />
        </fieldset>
    </form>

    <x-slot:pie>
        <button class="boton boton--secundario" type="button" data-cerrar-modal>Cancelar</button>
        <button class="boton" type="submit" form="form-cita">
            <i class="fa-solid fa-floppy-disk"></i> Guardar cita
        </button>
    </x-slot:pie>

</x-panel.modal>
