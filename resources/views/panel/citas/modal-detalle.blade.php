<x-panel.modal id="modal-detalle" titulo="Detalle de la cita" icono="fa-calendar-check" ancho="estrecho">

    <div class="detalle" id="detalle-cuerpo"></div>

    <form method="POST" action="" id="form-estado-detalle" class="detalle__estado">
        @csrf
        @method('PATCH')

        <label class="campo__etiqueta" for="detalle-estado">Estado de la cita</label>

        <select class="estado-rapido__select" name="estado" id="detalle-estado" data-detalle>
            @foreach (\App\Models\Cita::ESTADOS as $clave => $texto)
                <option value="{{ $clave }}">{{ $texto }}</option>
            @endforeach
        </select>
    </form>

    <x-slot:pie>
        <form method="POST" action="" id="form-borrar-detalle" class="confirmar"
              data-confirmar data-confirmar-titulo="Eliminar cita"
              data-confirmar-mensaje="La cita se borrara definitivamente y no se podra recuperar. El paciente y su ficha no se borran.">
            @csrf
            @method('DELETE')

            <button class="boton boton--peligro" type="submit">
                <i class="fa-solid fa-trash-can"></i> Eliminar
            </button>
        </form>

        <a class="boton boton--secundario" href="#" id="detalle-ficha">
            <i class="fa-solid fa-address-card"></i> Ficha
        </a>

        <button class="boton" type="button" id="detalle-editar">
            <i class="fa-solid fa-pen"></i> Editar
        </button>
    </x-slot:pie>

</x-panel.modal>
