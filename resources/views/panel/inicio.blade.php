@extends('panel.layouts.app')

@section('titulo', 'Inicio')
@section('encabezado', 'Hola, ' . $psicologa->nombre)
@section('subtitulo', 'Este es tu panel de gestion. Desde aqui controlas tu agenda, tus pacientes y tu web.')

@section('contenido')

    <div class="rejilla rejilla--principal">

        <x-panel.card titulo="Tu panel esta listo" icono="fa-circle-check"
                      descripcion="La estructura del dashboard ya funciona. Las siguientes secciones se iran activando por fases.">

            <x-panel.aviso tono="info" titulo="Que puedes hacer ahora">
                <p>El menu lateral ya muestra todas las secciones que tendras. Las que aparecen con un
                    candado todavia no estan disponibles: se activan solas en cuanto se desarrolla su fase.</p>
            </x-panel.aviso>

            <div class="inicio__pasos">
                <h3 class="inicio__subtitulo">Proximos pasos</h3>

                <ul class="pasos">
                    <li class="pasos__item">
                        <span class="pasos__numero">1</span>
                        <div>
                            <p class="pasos__titulo">Configura tu disponibilidad</p>
                            <p class="pasos__texto">Marca los dias y horas en los que atiendes, online y presencial.</p>
                        </div>
                        <x-panel.badge tono="atencion">Proxima fase</x-panel.badge>
                    </li>

                    <li class="pasos__item">
                        <span class="pasos__numero">2</span>
                        <div>
                            <p class="pasos__titulo">Revisa tus citas</p>
                            <p class="pasos__texto">Consulta tu agenda y anade citas manualmente.</p>
                        </div>
                        <x-panel.badge tono="atencion">Proxima fase</x-panel.badge>
                    </li>

                    <li class="pasos__item">
                        <span class="pasos__numero">3</span>
                        <div>
                            <p class="pasos__titulo">Publica tu web</p>
                            <p class="pasos__texto">Ajusta tus textos, imagenes y el tema visual.</p>
                        </div>
                        <x-panel.badge tono="atencion">Proxima fase</x-panel.badge>
                    </li>
                </ul>
            </div>

        </x-panel.card>

        <div class="rejilla">

            <x-panel.card titulo="Tu web" icono="fa-globe">
                <dl class="datos">
                    <div class="datos__fila">
                        <dt>Tema activo</dt>
                        <dd>{{ $tema['nombre'] ?? 'Sin tema' }}</dd>
                    </div>
                    <div class="datos__fila">
                        <dt>Formato</dt>
                        <dd>{{ $modoWeb === 'landing' ? 'Una sola pagina' : 'Varias paginas' }}</dd>
                    </div>
                    <div class="datos__fila">
                        <dt>Colegiada</dt>
                        <dd>{{ $perfil?->num_colegiado ?: 'Sin indicar' }}</dd>
                    </div>
                </dl>

                <a class="boton boton--secundario boton--pequeno" href="{{ route('home') }}"
                   target="_blank" rel="noopener">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i> Ver mi web
                </a>
            </x-panel.card>

            <x-panel.card titulo="Tus datos de contacto" icono="fa-address-card">
                <dl class="datos">
                    <div class="datos__fila">
                        <dt>Telefono para citas</dt>
                        <dd>{{ $perfil?->telefono_citas ?: 'Sin indicar' }}</dd>
                    </div>
                    <div class="datos__fila">
                        <dt>Email para citas</dt>
                        <dd>{{ $perfil?->email_citas ?: 'Sin indicar' }}</dd>
                    </div>
                    <div class="datos__fila">
                        <dt>Consulta</dt>
                        <dd>{{ $perfil?->lugar_consulta ?: 'Sin indicar' }}</dd>
                    </div>
                </dl>
            </x-panel.card>

        </div>

    </div>

@endsection

@push('estilos')
    <link rel="stylesheet" href="{{ asset('panel/css/inicio.css') }}">
@endpush
