<div class="campo">
    <span class="campo__etiqueta">Icono</span>

    <div class="iconos" role="radiogroup" aria-label="Icono">
        @foreach ($iconos as $grupo => $lista)
            <p class="iconos__grupo">{{ $grupo }}</p>

            <div class="iconos__lista">
                @foreach ($lista as $clase => $nombre)
                    <label class="icono-opcion" title="{{ $nombre }}">
                        <input class="icono-opcion__entrada" type="radio" name="icono"
                               value="{{ $clase }}" @checked($iconoActual === $clase)>
                        <span class="icono-opcion__cara">
                            <i class="fa-solid {{ $clase }}" aria-hidden="true"></i>
                            <span class="icono-opcion__nombre">{{ $nombre }}</span>
                        </span>
                    </label>
                @endforeach
            </div>
        @endforeach
    </div>

    @error('icono')
        <p class="campo__error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p>
    @enderror
</div>
