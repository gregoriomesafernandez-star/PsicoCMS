@extends('panel.layouts.app')

@php
    $esNuevo = ! $articulo->exists;
    $accion = $esNuevo ? route('panel.blog.store') : route('panel.blog.update', $articulo);
    $estadoActual = $articulo->exists && $articulo->publicado ? 'publicado' : 'borrador';
    $fechaActual = $articulo->publicado_at?->format('Y-m-d\TH:i');
@endphp

@section('titulo', $esNuevo ? 'Nuevo articulo' : 'Editar articulo')
@section('encabezado', $esNuevo ? 'Nuevo articulo' : 'Editar articulo')
@section('subtitulo', $esNuevo
    ? 'Escribe una nueva entrada para el blog de tu web.'
    : 'Modifica el contenido y la configuracion de esta entrada.')

@section('acciones')
    <a class="boton boton--secundario" href="{{ route('panel.blog.index') }}">
        <i class="fa-solid fa-arrow-left"></i> Volver al listado
    </a>

    @unless ($esNuevo)
        <button class="boton boton--secundario" type="submit" form="form-articulo"
                name="accion" value="previsualizar"
                title="Guarda los cambios y te muestra como queda el articulo">
            <i class="fa-solid fa-eye"></i> Vista previa
        </button>
    @endunless
@endsection

@section('contenido')

    @if ($errors->any())
        <x-panel.aviso tono="error" titulo="Revisa el formulario" class="separado">
            <p>Hay {{ $errors->count() }} campo(s) con errores. Estan marcados en rojo mas abajo.</p>
        </x-panel.aviso>
    @endif

    <form method="POST" action="{{ $accion }}" enctype="multipart/form-data" id="form-articulo">
        @csrf
        @unless ($esNuevo)
            @method('PUT')
        @endunless

        <div class="editor-articulo">

            <div class="editor-articulo__principal">

                <x-panel.card titulo="Contenido" icono="fa-pen-nib" class="separado">
                    <x-panel.campo nombre="titulo" etiqueta="Titulo del articulo"
                                   :valor="$articulo->titulo"
                                   placeholder="Por ejemplo: Como manejar la ansiedad en el trabajo"
                                   obligatorio />

                    <x-panel.campo nombre="slug" etiqueta="Enlace permanente"
                                   :valor="$articulo->slug"
                                   placeholder="se-genera-solo-desde-el-titulo"
                                   ayuda="Se genera solo a partir del titulo. Puedes cambiarlo si lo prefieres." />

                    <x-panel.campo nombre="extracto" etiqueta="Extracto" tipo="textarea"
                                   :valor="$articulo->extracto" maxlength="300"
                                   placeholder="Resumen breve que se vera en el listado del blog"
                                   ayuda="Texto plano, maximo 300 caracteres." data-contador="extracto" />

                    <div class="campo">
                        <label class="campo__etiqueta" for="contenido">Contenido del articulo</label>

                        <textarea class="campo__control editor-rico @error('contenido') campo__control--error @enderror"
                                  id="contenido" name="contenido"
                                  placeholder="Escribe aqui el articulo completo">{{ old('contenido', $articulo->contenido) }}</textarea>

                        @error('contenido')
                            <p class="campo__error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p>
                        @enderror
                    </div>
                </x-panel.card>

                <x-panel.card titulo="Posicionamiento (SEO)" icono="fa-magnifying-glass-chart"
                              descripcion="Como se vera tu articulo en Google. Si lo dejas vacio se usara el titulo y el extracto.">
                    <x-panel.campo nombre="meta_title" etiqueta="Titulo SEO"
                                   :valor="$articulo->meta_title" maxlength="70"
                                   placeholder="Maximo 70 caracteres" data-contador="meta_title" />

                    <x-panel.campo nombre="meta_description" etiqueta="Descripcion SEO" tipo="textarea"
                                   :valor="$articulo->meta_description" maxlength="170"
                                   placeholder="Maximo 170 caracteres" data-contador="meta_description" />
                </x-panel.card>

            </div>

            <aside class="editor-articulo__lateral">

                <x-panel.card titulo="Publicacion" icono="fa-paper-plane" class="separado">
                    <x-panel.campo nombre="estado" etiqueta="Estado" tipo="select"
                                   :valor="$estadoActual"
                                   :opciones="['borrador' => 'Borrador', 'publicado' => 'Publicado']" obligatorio />

                    <x-panel.campo nombre="publicado_at" etiqueta="Fecha de publicacion"
                                   tipo="datetime-local" :valor="$fechaActual"
                                   ayuda="Dejalo vacio para publicar ahora. Una fecha futura lo deja programado." />

                    <x-panel.campo nombre="categoria_id" etiqueta="Categoria" tipo="select"
                                   :valor="$articulo->categoria_id"
                                   :opciones="['' => 'Sin categoria'] + $categorias" />

                    <p class="campo__ayuda">
                        <a href="{{ route('panel.categorias.index') }}">Administrar categorias</a>
                    </p>

                    <div class="editor-articulo__acciones">
                        <button class="boton" type="submit">
                            <i class="fa-solid fa-floppy-disk"></i>
                            {{ $esNuevo ? 'Crear articulo' : 'Guardar cambios' }}
                        </button>
                    </div>
                </x-panel.card>

                <x-panel.card titulo="Imagen destacada" icono="fa-image">
                    <div class="imagen-destacada">
                        <img class="imagen-destacada__vista {{ $articulo->url_imagen ? '' : 'oculto' }}"
                             id="vista-imagen"
                             src="{{ $articulo->url_imagen ?? '' }}"
                             alt="Imagen destacada del articulo">

                        <div class="imagen-destacada__vacia {{ $articulo->url_imagen ? 'oculto' : '' }}" id="vista-vacia">
                            <i class="fa-solid fa-image"></i>
                            <p>Sin imagen destacada</p>
                        </div>
                    </div>

                    <x-panel.campo nombre="imagen" etiqueta="Subir imagen" tipo="file"
                                   accept="image/jpeg,image/png,image/webp"
                                   ayuda="JPG, PNG o WEBP. Maximo 2 MB." />

                    @if ($articulo->url_imagen)
                        <label class="casilla" for="eliminar_imagen">
                            <input type="checkbox" name="eliminar_imagen" id="eliminar_imagen" value="1">
                            <span>Quitar la imagen actual al guardar</span>
                        </label>
                    @endif
                </x-panel.card>

            </aside>

        </div>
    </form>

@endsection

@include('panel.partials.assets-editor')

@push('estilos')
    <link rel="stylesheet" href="{{ recurso('panel/css/blog.css') }}">
@endpush

@push('scripts')
    <script src="{{ recurso('panel/js/blog.js') }}"></script>
@endpush
