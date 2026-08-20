@extends('panel.layouts.app')

@section('titulo', 'Mi perfil')
@section('encabezado', 'Mi perfil')
@section('subtitulo', 'Tus datos privados: los que usas para entrar al panel. No se ven en tu web.')

@section('migas')
    <x-panel.migas :items="[
        ['texto' => 'Inicio', 'url' => route('panel.inicio')],
        ['texto' => 'Mi perfil'],
    ]" />
@endsection

@section('contenido')

    <x-panel.aviso tono="info" titulo="Esto es privado" class="separado">
        <p>Estos datos no aparecen en tu web. Lo que ven tus pacientes se configura en
            <a href="{{ route('panel.contenido') }}">Contenido de la web</a>.</p>
    </x-panel.aviso>

    <div class="pestanas" role="tablist">
        @foreach ([
            'datos' => ['Mis datos', 'fa-id-card'],
            'password' => ['Contrasena', 'fa-key'],
        ] as $clave => [$texto, $icono])
            <button class="pestanas__boton {{ $pestana === $clave ? 'pestanas__boton--activo' : '' }}"
                    type="button" role="tab" data-pestana="{{ $clave }}"
                    aria-selected="{{ $pestana === $clave ? 'true' : 'false' }}">
                <i class="fa-solid {{ $icono }}"></i> {{ $texto }}
            </button>
        @endforeach
    </div>

    <section data-panel="datos" @if ($pestana !== 'datos') hidden @endif>

        <div class="perfil-privado">

            <form method="POST" action="{{ route('panel.perfil.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <x-panel.card titulo="Datos de acceso" icono="fa-id-card"
                              descripcion="Con el email, el telefono y tu contrasena entras al panel.">

                    <div class="rejilla rejilla--2">
                        <x-panel.campo nombre="nombre" etiqueta="Nombre" :valor="$psicologa->nombre"
                                       maxlength="100" obligatorio />

                        <x-panel.campo nombre="apellidos" etiqueta="Apellidos" :valor="$psicologa->apellidos"
                                       maxlength="150" />
                    </div>

                    <x-panel.campo nombre="email" etiqueta="Email de acceso" tipo="email"
                                   :valor="$psicologa->email"
                                   ayuda="Es uno de los tres datos que te piden al entrar." obligatorio />

                    <x-panel.campo nombre="telefono" etiqueta="Telefono de acceso"
                                   :valor="$psicologa->telefono"
                                   ayuda="Solo numeros. Es el segundo dato que te piden al entrar." obligatorio />

                    <x-panel.campo nombre="avatar" etiqueta="Cambiar tu foto" tipo="file"
                                   accept="image/jpeg,image/png,image/webp"
                                   ayuda="JPG, PNG o WEBP. Maximo 2 MB. Se ve solo en el panel." />

                    <x-slot:pie>
                        <button class="boton" type="submit">
                            <i class="fa-solid fa-floppy-disk"></i> Guardar mis datos
                        </button>
                    </x-slot:pie>
                </x-panel.card>
            </form>

            <x-panel.card titulo="Tu foto" icono="fa-camera"
                          descripcion="Aparece arriba a la derecha del panel.">

                <figure class="avatar-perfil">
                    @if ($psicologa->avatar)
                        <img class="avatar-perfil__imagen" src="{{ asset('storage/' . $psicologa->avatar) }}"
                             alt="Foto de {{ $psicologa->nombre }}">
                    @else
                        <span class="avatar-perfil__iniciales">
                            {{ strtoupper(mb_substr($psicologa->nombre, 0, 1) . mb_substr($psicologa->apellidos ?? '', 0, 1)) }}
                        </span>
                    @endif
                </figure>

                @if ($psicologa->avatar)
                    <x-panel.confirmar-borrado :accion="route('panel.perfil.avatar')"
                                               titulo="Quitar la foto"
                                               mensaje="Volveran a verse tus iniciales en el panel."
                                               etiqueta="Quitar la foto" />
                @else
                    <p class="celda__secundario">
                        Sin foto se ven tus iniciales. Sube una desde el formulario de al lado.
                    </p>
                @endif

            </x-panel.card>

        </div>

    </section>

    <section data-panel="password" @if ($pestana !== 'password') hidden @endif>

        <form method="POST" action="{{ route('panel.perfil.password') }}">
            @csrf
            @method('PUT')

            <x-panel.card titulo="Cambiar la contrasena" icono="fa-key"
                          descripcion="Para cambiarla tienes que escribir primero la que usas ahora.">

                <x-panel.campo nombre="password_actual" etiqueta="Contrasena actual" tipo="password"
                               autocomplete="current-password" obligatorio />

                <div class="rejilla rejilla--2">
                    <x-panel.campo nombre="password" etiqueta="Contrasena nueva" tipo="password"
                                   autocomplete="new-password"
                                   ayuda="Al menos 8 caracteres." obligatorio />

                    <x-panel.campo nombre="password_confirmation" etiqueta="Repite la contrasena nueva"
                                   tipo="password" autocomplete="new-password" obligatorio />
                </div>

                <x-panel.aviso tono="atencion" titulo="Ojo">
                    <p>Al cambiarla se cerrara la sesion en cualquier otro dispositivo donde estuvieras
                        dentro. Aqui seguiras conectada.</p>
                </x-panel.aviso>

                <x-slot:pie>
                    <button class="boton" type="submit">
                        <i class="fa-solid fa-key"></i> Cambiar contrasena
                    </button>
                </x-slot:pie>
            </x-panel.card>
        </form>

    </section>

@endsection

@push('estilos')
    <link rel="stylesheet" href="{{ recurso('panel/css/contenido.css') }}">
    <link rel="stylesheet" href="{{ recurso('panel/css/frases.css') }}">
@endpush

@push('scripts')
    <script src="{{ recurso('panel/js/perfil.js') }}"></script>
@endpush
