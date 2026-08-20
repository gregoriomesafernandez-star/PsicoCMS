@php
    $apariencia = app(\App\Services\AparienciaService::class)->actual();
@endphp

<x-panel.modal id="modal-apariencia" titulo="Apariencia del panel" icono="fa-palette">
    <form method="POST" action="{{ route('panel.apariencia') }}" id="form-apariencia">
        @csrf
        @method('PUT')

        <p class="apariencia__intro">
            Elige como quieres ver tu panel. Se guarda en tu cuenta, asi que lo encontraras igual
            aunque cierres sesion o entres desde otro dispositivo.
        </p>

        <fieldset class="apariencia__bloque">
            <legend class="apariencia__titulo">Claro u oscuro</legend>

            <div class="temas-panel">
                @foreach (\App\Services\AparienciaService::TEMAS as $clave => [$nombre, $icono, $explicacion])
                    <label class="tema-panel">
                        <input class="tema-panel__entrada" type="radio" name="tema" value="{{ $clave }}"
                               @checked($apariencia['tema'] === $clave)>

                        <span class="tema-panel__cara">
                            <span class="tema-panel__muestra tema-panel__muestra--{{ $clave }}">
                                <span class="tema-panel__barra"></span>
                                <span class="tema-panel__linea"></span>
                                <span class="tema-panel__linea tema-panel__linea--corta"></span>
                            </span>

                            <span class="tema-panel__texto">
                                <span class="tema-panel__nombre">
                                    <i class="fa-solid {{ $icono }}" aria-hidden="true"></i> {{ $nombre }}
                                </span>
                                <span class="tema-panel__explicacion">{{ $explicacion }}</span>
                            </span>
                        </span>
                    </label>
                @endforeach
            </div>
        </fieldset>

        <fieldset class="apariencia__bloque">
            <legend class="apariencia__titulo">Color principal</legend>

            <div class="colores" role="radiogroup" aria-label="Color principal del panel">
                @foreach (\App\Services\AparienciaService::COLORES as $clave => [$nombre, $hex])
                    <label class="color-opcion" title="{{ $nombre }}">
                        <input class="color-opcion__entrada" type="radio" name="color" value="{{ $clave }}"
                               @checked($apariencia['color'] === $clave)>

                        <span class="color-opcion__muestra" style="background-color: {{ $hex }}">
                            <i class="fa-solid fa-check" aria-hidden="true"></i>
                        </span>

                        <span class="color-opcion__nombre">{{ $nombre }}</span>
                    </label>
                @endforeach
            </div>
        </fieldset>

        <x-panel.aviso tono="info" titulo="Se ve al momento">
            <p>Los cambios se aplican mientras eliges. Si cierras sin guardar, el panel vuelve a como estaba.</p>
        </x-panel.aviso>
    </form>

    <x-slot:pie>
        <button class="boton boton--secundario" type="button" data-cerrar-modal>Cancelar</button>
        <button class="boton" type="submit" form="form-apariencia">
            <i class="fa-solid fa-floppy-disk"></i> Guardar apariencia
        </button>
    </x-slot:pie>
</x-panel.modal>
