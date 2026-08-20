@extends('panel.layouts.app')

@section('titulo', 'Email y notificaciones')
@section('encabezado', 'Avisos por email')
@section('subtitulo', 'Recibe un correo cada vez que un paciente reserve cita desde tu web.')

@section('migas')
    <x-panel.migas :items="[
        ['texto' => 'Inicio', 'url' => route('panel.inicio')],
        ['texto' => 'Email y notificaciones'],
    ]" />
@endsection

@section('contenido')

    <div class="notificaciones">

        <div class="notificaciones__principal">

            @if ($configurado)
                <x-panel.aviso tono="exito" titulo="Los avisos estan configurados" class="separado">
                    <p>Se enviaran a <strong>{{ $configuracion['email'] }}</strong>. Puedes comprobarlo
                        ahora mismo con el boton "Enviar email de prueba".</p>
                </x-panel.aviso>
            @else
                <x-panel.aviso tono="atencion" titulo="Todavia no recibiras avisos" class="separado">
                    <p>Rellena el formulario con tu cuenta de Gmail. Si no lo haces, las reservas se
                        guardaran igual en el panel, pero no te llegara ningun correo.</p>
                </x-panel.aviso>
            @endif

            <form method="POST" action="{{ route('panel.notificaciones.update') }}" id="form-notificaciones">
                @csrf
                @method('PUT')

                <x-panel.card titulo="Tu cuenta de Gmail" icono="fa-envelope"
                              descripcion="PsicoCMS usa tu propio Gmail para enviarte los avisos a ti misma."
                              class="separado">

                    <x-panel.campo nombre="email" etiqueta="Correo de Gmail" tipo="email"
                                   :valor="$configuracion['email']"
                                   placeholder="tunombre@gmail.com"
                                   ayuda="A esta direccion llegaran los avisos, y desde ella se envian."
                                   obligatorio />

                    <div class="campo">
                        <label class="campo__etiqueta" for="password">
                            Contrasena de aplicacion
                            @unless ($configuracion['tiene_password'])
                                <span class="campo__obligatorio" aria-hidden="true">*</span>
                            @endunless
                        </label>

                        <div class="password">
                            <input class="campo__control @error('password') campo__control--error @enderror"
                                   type="password" id="password" name="password"
                                   autocomplete="new-password"
                                   placeholder="{{ $configuracion['tiene_password'] ? 'Guardada. Escribe una nueva solo si quieres cambiarla' : 'abcd efgh ijkl mnop' }}">

                            <button class="password__ojo" type="button" data-ver-password="password"
                                    aria-label="Mostrar la contrasena">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>

                        @error('password')
                            <p class="campo__error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p>
                        @else
                            <p class="campo__ayuda">
                                No es la contrasena con la que entras en Gmail. Es una de 16 letras que
                                genera Google: te explico como conseguirla aqui al lado.
                            </p>
                        @enderror
                    </div>

                    <x-panel.campo nombre="nombre" etiqueta="Nombre del remitente"
                                   :valor="$configuracion['nombre']"
                                   placeholder="Consulta de Ana Ruiz"
                                   ayuda="El nombre que veras como remitente del aviso." />

                    <x-panel.toggle nombre="avisar_citas" id="avisar_citas"
                                    :activo="$configuracion['avisar_citas']"
                                    etiqueta="Avisarme cuando un paciente reserve cita" />

                    {{-- Los formularios de probar y borrar viven fuera: un form no
                         puede contener otro, asi que sus botones los referencian. --}}
                    <x-slot:pie>
                        @if ($configurado)
                            <button class="boton boton--peligro" type="submit" form="form-olvidar">
                                <i class="fa-solid fa-trash-can"></i> Borrar datos
                            </button>

                            <button class="boton boton--secundario" type="submit" form="form-probar">
                                <i class="fa-solid fa-paper-plane"></i> Enviar email de prueba
                            </button>
                        @endif

                        <button class="boton" type="submit" form="form-notificaciones">
                            <i class="fa-solid fa-floppy-disk"></i> Guardar configuracion
                        </button>
                    </x-slot:pie>
                </x-panel.card>
            </form>

            @if ($configurado)
                <form method="POST" action="{{ route('panel.notificaciones.probar') }}" id="form-probar" class="oculto">
                    @csrf
                </form>

                <form method="POST" action="{{ route('panel.notificaciones.olvidar') }}" id="form-olvidar" class="oculto"
                      data-confirmar
                      data-confirmar-titulo="Borrar los datos de email"
                      data-confirmar-mensaje="Se borraran tu correo y tu contrasena de aplicacion, y dejaras de recibir avisos."
                      data-confirmar-boton="Si, borrar los datos">
                    @csrf
                    @method('DELETE')
                </form>
            @endif

        </div>

        <aside class="notificaciones__ayuda">
            <x-panel.card titulo="Como conseguir la contrasena" icono="fa-key"
                          descripcion="Son cinco minutos y solo hay que hacerlo una vez.">

                <ol class="pasos">
                    <li class="paso">
                        <span class="paso__numero">1</span>
                        <div class="paso__texto">
                            <p class="paso__titulo">Activa la verificacion en dos pasos</p>
                            <p>Google no deja crear contrasenas de aplicacion sin ella. Esta en
                                <strong>Tu cuenta de Google → Seguridad</strong>.</p>
                            <a class="paso__enlace" href="https://myaccount.google.com/security"
                               target="_blank" rel="noopener">
                                Abrir Seguridad de Google <i class="fa-solid fa-arrow-up-right-from-square"></i>
                            </a>
                        </div>
                    </li>

                    <li class="paso">
                        <span class="paso__numero">2</span>
                        <div class="paso__texto">
                            <p class="paso__titulo">Entra en "Contrasenas de aplicaciones"</p>
                            <p>Busca ese texto en el buscador de tu cuenta de Google, o entra directamente
                                desde el enlace.</p>
                            <a class="paso__enlace" href="https://myaccount.google.com/apppasswords"
                               target="_blank" rel="noopener">
                                Abrir contrasenas de aplicaciones <i class="fa-solid fa-arrow-up-right-from-square"></i>
                            </a>
                        </div>
                    </li>

                    <li class="paso">
                        <span class="paso__numero">3</span>
                        <div class="paso__texto">
                            <p class="paso__titulo">Crea una y ponle el nombre PsicoCMS</p>
                            <p>El nombre da igual, es solo para que recuerdes para que era.</p>
                        </div>
                    </li>

                    <li class="paso">
                        <span class="paso__numero">4</span>
                        <div class="paso__texto">
                            <p class="paso__titulo">Copia las 16 letras y pegalas aqui</p>
                            <p>Google las muestra en cuatro bloques. Da igual si copias los espacios:
                                los quitamos nosotros.</p>
                        </div>
                    </li>
                </ol>

                <x-panel.aviso tono="info" titulo="¿Y esto es seguro?">
                    <p>La contrasena se guarda cifrada y solo se usa para enviarte correos a ti misma.
                        Puedes borrarla cuando quieras con el boton "Borrar datos".</p>
                </x-panel.aviso>

            </x-panel.card>
        </aside>

    </div>

@endsection

@push('estilos')
    <link rel="stylesheet" href="{{ recurso('panel/css/frases.css') }}">
@endpush

@push('scripts')
    <script src="{{ recurso('panel/js/notificaciones.js') }}"></script>
@endpush
