@extends('panel.layouts.app')

@section('titulo', 'Categorias')
@section('encabezado', 'Categorias del blog')
@section('subtitulo', 'Agrupa tus articulos para que tus pacientes encuentren lo que buscan.')

@section('acciones')
    <a class="boton boton--secundario" href="{{ route('panel.blog.index') }}">
        <i class="fa-solid fa-newspaper"></i> Ver articulos
    </a>
    <button class="boton" type="button" id="boton-nueva-categoria">
        <i class="fa-solid fa-plus"></i> Nueva categoria
    </button>
@endsection

@section('contenido')

    <x-panel.card titulo="Listado de categorias" icono="fa-tags"
                  descripcion="El orden mas bajo aparece primero en la web.">

        @if ($categorias->isEmpty())
            <x-panel.empty-state icono="fa-tags" titulo="Todavia no hay categorias"
                                 mensaje="Crea categorias para organizar los articulos de tu blog.">
                <button class="boton" type="button" data-nueva-categoria>
                    <i class="fa-solid fa-plus"></i> Crear la primera categoria
                </button>
            </x-panel.empty-state>
        @else
            <x-panel.tabla :cabeceras="['Categoria', 'Enlace', 'Articulos', 'Orden', 'Acciones']">
                @foreach ($categorias as $categoria)
                    <tr>
                        <td>
                            <p class="celda__principal">{{ $categoria->nombre }}</p>
                            <p class="celda__secundario">{{ $categoria->descripcion ?: 'Sin descripcion' }}</p>
                        </td>

                        <td><code class="celda__codigo">/{{ $categoria->slug }}</code></td>

                        <td>
                            <x-panel.badge :tono="$categoria->articulos_count > 0 ? 'info' : 'neutro'">
                                {{ $categoria->articulos_count }}
                            </x-panel.badge>
                        </td>

                        <td class="celda__secundario">{{ $categoria->orden }}</td>

                        <td>
                            <div class="acciones-fila">
                                <button class="boton boton--secundario boton--icono" type="button"
                                        data-editar-categoria
                                        data-categoria="{{ json_encode([
                                            'id' => $categoria->id,
                                            'nombre' => $categoria->nombre,
                                            'descripcion' => $categoria->descripcion,
                                            'orden' => $categoria->orden,
                                        ]) }}"
                                        aria-label="Editar {{ $categoria->nombre }}" title="Editar categoria">
                                    <i class="fa-solid fa-pen"></i>
                                </button>

                                <x-panel.confirmar-borrado :accion="route('panel.categorias.destroy', $categoria)"
                                                           titulo="Eliminar categoria"
                                                           :mensaje="$categoria->articulos_count > 0
                                                                ? 'Sus ' . $categoria->articulos_count . ' articulo(s) no se borran: se quedaran sin categoria.'
                                                                : 'La categoria se borrara definitivamente.'"
                                                           etiqueta="Eliminar categoria" solo-icono />
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-panel.tabla>
        @endif

    </x-panel.card>

    <x-panel.modal id="modal-categoria" titulo="Nueva categoria" icono="fa-tag" ancho="estrecho">
        <form method="POST" action="{{ route('panel.categorias.store') }}" id="form-categoria">
            @csrf
            <input type="hidden" name="_method" value="POST" id="categoria-metodo">

            <x-panel.campo nombre="nombre" id="categoria-nombre" etiqueta="Nombre"
                           placeholder="Por ejemplo: Ansiedad" obligatorio />

            <x-panel.campo nombre="descripcion" id="categoria-descripcion" etiqueta="Descripcion (opcional)"
                           tipo="textarea" maxlength="500"
                           placeholder="De que trata esta categoria" />

            <x-panel.campo nombre="orden" id="categoria-orden" etiqueta="Orden" tipo="number"
                           valor="0" min="0" max="999"
                           ayuda="Las categorias se ordenan de menor a mayor." />
        </form>

        <x-slot:pie>
            <button class="boton boton--secundario" type="button" data-cerrar-modal>Cancelar</button>
            <button class="boton" type="submit" form="form-categoria">
                <i class="fa-solid fa-floppy-disk"></i> Guardar categoria
            </button>
        </x-slot:pie>
    </x-panel.modal>

@endsection

@push('estilos')
    <link rel="stylesheet" href="{{ asset('panel/css/citas.css') }}">
    <link rel="stylesheet" href="{{ asset('panel/css/blog.css') }}">
@endpush

@push('scripts')
    @php
        $datosCategorias = [
            'urlCrear' => route('panel.categorias.store'),
            'urlBase' => url('/panel-psicologa/categorias'),
            'errores' => $errors->any(),
        ];
    @endphp

    <script id="datos-categorias" type="application/json">@json($datosCategorias)</script>
    <script src="{{ asset('panel/js/categorias.js') }}"></script>
@endpush
