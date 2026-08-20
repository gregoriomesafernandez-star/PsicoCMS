@extends('panel.layouts.app')

@section('titulo', 'Ayuda')
@section('encabezado', 'Como funciona PsicoCMS')
@section('subtitulo', 'Una guia corta de todo el panel, sin tecnicismos. Puedes leerla de arriba abajo o ir al apartado que necesites.')

@section('migas')
    <x-panel.migas :items="[
        ['texto' => 'Inicio', 'url' => route('panel.inicio')],
        ['texto' => 'Ayuda'],
    ]" />
@endsection

@section('contenido')

    <div class="ayuda">

        <aside class="ayuda__indice">
            <nav class="indice" aria-label="Apartados de la ayuda">
                <p class="indice__titulo">En esta guia</p>

                <ul class="indice__lista">
                    @foreach ($apartados as $ancla => [$titulo, $icono, $ruta])
                        <li>
                            <a class="indice__enlace" href="#{{ $ancla }}">
                                <i class="fa-solid {{ $icono }}" aria-hidden="true"></i> {{ $titulo }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </nav>
        </aside>

        <div class="ayuda__contenido">

            <x-panel.card titulo="Primeros pasos" icono="fa-flag-checkered" id="primeros-pasos" class="separado">
                <div class="guia">
                    <p>PsicoCMS tiene dos partes: <strong>tu web</strong>, que ven tus pacientes, y
                        <strong>este panel</strong>, que solo ves tu. Todo lo que cambies aqui se
                        refleja en la web.</p>

                    <p>Si acabas de instalarlo, el orden que te recomiendo es este:</p>

                    <ol class="guia__pasos">
                        <li>Configura <strong>tu disponibilidad</strong>, para que se puedan pedir citas.</li>
                        <li>Repasa el <strong>contenido de tu web</strong>: tu presentacion, servicios y precios.</li>
                        <li>Elige un <strong>tema</strong> y mira como queda con la vista previa.</li>
                        <li>Cuando todo te guste, comparte la direccion de tu web.</li>
                    </ol>

                    <p>En la pagina de <a href="{{ route('panel.inicio') }}">Inicio</a> tienes una tarjeta
                        de "Proximos pasos" que te va marcando lo que te falta.</p>
                </div>
            </x-panel.card>

            <x-panel.card titulo="Tu disponibilidad" icono="fa-clock" id="disponibilidad" class="separado">
                <div class="guia">
                    <p>Es la pieza mas importante: de aqui salen los huecos que tus pacientes pueden
                        reservar. Se configura en <a href="{{ route('panel.disponibilidad') }}">Disponibilidad</a>.</p>

                    <p><strong>Cuanto dura una sesion</strong> y <strong>cuanto descanso quieres entre
                        una y otra</strong> se configuran por separado para online y presencial. Si tus
                        sesiones duran 50 minutos y pones 10 de descanso, tus huecos van de hora en hora:
                        9:00, 10:00, 11:00... Si no pones descanso, van seguidos: 9:00, 9:50, 10:40...</p>

                    <p>Debajo marcas <strong>en que horas atiendes cada dia de la semana</strong>. Puedes
                        pulsar celda a celda o arrastrar para marcar varias seguidas. Ojo: si cambias la
                        duracion o el descanso, hay que volver a marcarlas, y el panel te avisa.</p>

                    <p>Para parar las reservas tienes dos herramientas: el <strong>modo vacaciones</strong>,
                        que las corta del todo, y los <strong>periodos de vacaciones</strong>, para bloquear
                        solo unos dias concretos.</p>
                </div>
            </x-panel.card>

            <x-panel.card titulo="Citas y calendario" icono="fa-calendar-check" id="citas" class="separado">
                <div class="guia">
                    <p>Las citas que reservan tus pacientes aparecen solas. Tambien puedes crearlas tu
                        desde <a href="{{ route('panel.citas.index') }}">Citas</a> o desde el
                        <a href="{{ route('panel.calendario') }}">Calendario</a>, para cuando te llaman
                        por telefono o te escriben por WhatsApp.</p>

                    <p>Al escribir el nombre en el formulario, el panel <strong>busca entre tus pacientes</strong>.
                        Si ya existe, se rellenan sus datos; si no, se crea junto con la cita.</p>

                    <p>En el calendario puedes <strong>arrastrar una cita</strong> para cambiarla de dia u
                        hora. Si el hueco no esta libre, se vuelve a su sitio y te explica por que.</p>

                    <p>Cada cita tiene un estado (pendiente, confirmada, completada, cancelada o no
                        asistio) que puedes cambiar desde el propio listado.</p>
                </div>
            </x-panel.card>

            <x-panel.card titulo="Pacientes" icono="fa-users" id="pacientes" class="separado">
                <div class="guia">
                    <p>Cada persona que reserva se convierte en una ficha en
                        <a href="{{ route('panel.pacientes.index') }}">Pacientes</a>. El
                        <strong>telefono es el identificador</strong>: si la misma persona reserva otra
                        vez, se reconoce y no se duplica.</p>

                    <p>En su ficha puedes ampliar sus datos, ver todas sus citas, entrar a su historia
                        clinica y descargar el documento de proteccion de datos ya relleno.</p>

                    <p><strong>Cuidado al eliminar</strong>: borrar un paciente borra tambien sus citas y
                        toda su historia clinica. El aviso previo te dice exactamente cuanto se va a perder.</p>
                </div>
            </x-panel.card>

            <x-panel.card titulo="Historias clinicas" icono="fa-notes-medical" id="historias" class="separado">
                <div class="guia">
                    <p>Despues de cada sesion puedes anotar lo trabajado en
                        <a href="{{ route('panel.historias.index') }}">Historias</a>. Cada paciente tiene
                        su propia linea de tiempo, ordenada de la sesion mas reciente a la mas antigua.</p>

                    <p>Puedes escribir con formato y <strong>adjuntar fotos o PDF</strong>, por ejemplo el
                        escaneo de las notas que tomaste a mano.</p>

                    <p><strong>Todo lo que escribes aqui se guarda cifrado</strong>, porque son datos de
                        salud. Los archivos que subes no son accesibles desde internet: solo se pueden
                        abrir desde el panel, con tu sesion iniciada.</p>
                </div>
            </x-panel.card>

            <x-panel.card titulo="Proteccion de datos" icono="fa-file-shield" id="documentos" class="separado">
                <div class="guia">
                    <p>En <a href="{{ route('panel.documentos') }}">Documentos</a> tienes la plantilla del
                        consentimiento informado. Ya viene escrita, con los apartados que pide el RGPD.</p>

                    <p>Los <strong>campos automaticos</strong> (el nombre del paciente, su DNI, la fecha...)
                        se rellenan solos al generar el PDF de una persona concreta. Se insertan pulsando
                        en ellos mientras escribes.</p>

                    <p>Desde la ficha de cada paciente puedes descargar su PDF ya relleno, y desde aqui,
                        una plantilla vacia para imprimir y rellenar a mano.</p>
                </div>
            </x-panel.card>

            <x-panel.card titulo="Blog" icono="fa-newspaper" id="blog" class="separado">
                <div class="guia">
                    <p>Escribir en el <a href="{{ route('panel.blog.index') }}">blog</a> es la mejor forma
                        de que te encuentren en Google. Cada articulo puede llevar imagen y categoria.</p>

                    <p>Un articulo puede estar <strong>publicado</strong>, en <strong>borrador</strong> o
                        <strong>programado</strong> si le pones una fecha futura. El boton "Vista previa"
                        guarda y te ensena como queda antes de publicarlo.</p>

                    <p>Si no quieres tener blog, puedes apagarlo entero en
                        <a href="{{ route('panel.modulos') }}">Modulos</a> sin perder lo escrito.</p>
                </div>
            </x-panel.card>

            <x-panel.card titulo="El contenido de tu web" icono="fa-globe" id="web" class="separado">
                <div class="guia">
                    <p>Todo lo que rellenaste al instalar se puede cambiar en
                        <a href="{{ route('panel.contenido') }}">Contenido de la web</a>: tu presentacion,
                        el telefono de citas, los precios, la direccion y el mapa.</p>

                    <p>Cada pestana se guarda por separado, asi que puedes cambiar una cosa sin tocar el
                        resto. Arriba tienes una barra que te dice cuanto llevas relleno.</p>

                    <p>Los <a href="{{ route('panel.servicios.index') }}">servicios</a> y las
                        <a href="{{ route('panel.especialidades.index') }}">especialidades</a> tienen su
                        propia pantalla, donde puedes ordenarlos con las flechas y ocultar los que no
                        quieras ensenar.</p>

                    <p>En <a href="{{ route('panel.frases') }}">Frases publicas</a> puedes cambiar los
                        textos fijos que trae el tema, como los titulos de cada seccion.</p>
                </div>
            </x-panel.card>

            <x-panel.card titulo="Temas, imagenes y apariencia" icono="fa-palette" id="apariencia" class="separado">
                <div class="guia">
                    <p>En <a href="{{ route('panel.temas.index') }}">Temas</a> eliges como se ve tu web.
                        Hay cinco, y puedes verlos con tus propios datos antes de decidir, pulsando
                        "Ver como queda".</p>

                    <p>Ahi mismo eliges si tu web sera <strong>una sola pagina</strong> con todo seguido, o
                        <strong>varias paginas</strong> navegables.</p>

                    <p>En <a href="{{ route('panel.imagenes') }}">Imagenes</a> cambias las fotos del tema
                        por las tuyas. Si no subes ninguna, se usan las que trae el tema.</p>

                    <p>El boton <strong>Apariencia</strong> de arriba a la derecha cambia el aspecto de
                        <em>este panel</em> (no de tu web): claro u oscuro y el color que prefieras.</p>
                </div>
            </x-panel.card>

            <x-panel.card titulo="Avisos por email y tu perfil" icono="fa-gear" id="configuracion">
                <div class="guia">
                    <p>En <a href="{{ route('panel.notificaciones') }}">Email y notificaciones</a> puedes
                        configurar tu Gmail para recibir un correo cada vez que alguien reserve. Hay un
                        tutorial paso a paso en la propia pantalla.</p>

                    <p>En <a href="{{ route('panel.perfil') }}">Mi perfil</a> cambias tus datos privados:
                        los que usas para entrar, tu contrasena y tu foto.</p>

                    <p>El <strong>buscador de arriba</strong> busca a la vez en pacientes, citas, sesiones,
                        articulos y mas. Es la forma mas rapida de llegar a cualquier sitio.</p>

                    <p>Con las citas puedes buscar de tres formas: por el <strong>nombre o el telefono</strong>
                        del paciente, por la <strong>fecha</strong> (vale <em>24/09/2026</em>, <em>24-09-2026</em>
                        o solo <em>24/09</em> para este ano) y por el <strong>motivo</strong> que anotaste.</p>

                    <p>En <a href="{{ route('panel.modulos') }}">Modulos</a> enciendes y apagas secciones
                        enteras de tu web, por si hay algo que no quieres usar.</p>
                </div>
            </x-panel.card>

        </div>

    </div>

@endsection

@push('estilos')
    <link rel="stylesheet" href="{{ recurso('panel/css/ayuda.css') }}">
@endpush
