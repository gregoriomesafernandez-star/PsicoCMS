@extends('panel.layouts.app')

@section('titulo', 'Contenido de la web')
@section('encabezado', 'Contenido de la web')
@section('subtitulo', 'Todo lo que rellenaste en la instalacion, listo para ampliar y corregir cuando quieras.')

@section('migas')
    <x-panel.migas :items="[
        ['texto' => 'Inicio', 'url' => route('panel.inicio')],
        ['texto' => 'Contenido de la web'],
    ]" />
@endsection

@section('contenido')

    <x-panel.card titulo="Como va tu web" icono="fa-gauge-high"
                  :descripcion="$completitud['hechos'] . ' de ' . $completitud['total'] . ' bloques rellenos.'"
                  class="separado">

        <div class="progreso-web">
            <div class="progreso-web__barra">
                <span class="progreso-web__relleno" style="width: {{ $completitud['porcentaje'] }}%"></span>
            </div>
            <span class="progreso-web__valor">{{ $completitud['porcentaje'] }}%</span>
        </div>

        <ul class="completitud">
            @foreach ($completitud['puntos'] as $nombre => $hecho)
                <li class="completitud__punto {{ $hecho ? 'completitud__punto--hecho' : '' }}">
                    <i class="fa-solid {{ $hecho ? 'fa-circle-check' : 'fa-circle' }}" aria-hidden="true"></i>
                    {{ $nombre }}
                </li>
            @endforeach
        </ul>

    </x-panel.card>

    <div class="pestanas" role="tablist">
        @foreach ([
            'perfil' => ['Datos publicos', 'fa-id-card'],
            'sobre-mi' => ['Sobre mi', 'fa-user-pen'],
            'planes' => ['Planes y precios', 'fa-tags'],
            'consulta' => ['Consulta y mapa', 'fa-location-dot'],
            'seo' => ['Buscadores', 'fa-magnifying-glass-chart'],
        ] as $clave => [$texto, $icono])
            <button class="pestanas__boton {{ $pestana === $clave ? 'pestanas__boton--activo' : '' }}"
                    type="button" role="tab" data-pestana="{{ $clave }}"
                    aria-selected="{{ $pestana === $clave ? 'true' : 'false' }}">
                <i class="fa-solid {{ $icono }}"></i> {{ $texto }}
            </button>
        @endforeach
    </div>

    {{-- Datos publicos --}}
    <section data-panel="perfil" @if ($pestana !== 'perfil') hidden @endif>

        <div class="contenido-perfil">

            <form method="POST" action="{{ route('panel.contenido.perfil') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <x-panel.card titulo="Tus datos publicos" icono="fa-id-card"
                              descripcion="Lo que ve cualquier persona que entre en tu web.">

                    <x-panel.campo nombre="nombre_publico" etiqueta="Nombre publico"
                                   :valor="$perfil->nombre_publico"
                                   placeholder="Por ejemplo: Ana Ruiz Molina"
                                   ayuda="Como quieres que te llamen en tu web." obligatorio />

                    <x-panel.campo nombre="eslogan" etiqueta="Frase gancho" tipo="textarea"
                                   :valor="$perfil->eslogan" maxlength="255" rows="2"
                                   placeholder="Por ejemplo: Un espacio seguro para volver a encontrarte"
                                   ayuda="Es lo primero que se lee al entrar. Una frase corta y cercana." obligatorio />

                    <div class="rejilla rejilla--2">
                        <x-panel.campo nombre="num_colegiado" etiqueta="Numero de colegiada"
                                       :valor="$perfil->num_colegiado" placeholder="Por ejemplo: M-12345" />

                        <x-panel.campo nombre="telefono_citas" etiqueta="Telefono para citas"
                                       :valor="$perfil->telefono_citas" placeholder="600 11 22 33" obligatorio />
                    </div>

                    <x-panel.campo nombre="email_citas" etiqueta="Email para citas" tipo="email"
                                   :valor="$perfil->email_citas" placeholder="citas@tuconsulta.es"
                                   ayuda="Puede ser distinto del que usas para entrar al panel." obligatorio />

                    <x-panel.campo nombre="foto" etiqueta="Cambiar la foto" tipo="file"
                                   accept="image/jpeg,image/png,image/webp"
                                   ayuda="JPG, PNG o WEBP, maximo 4 MB. Preferiblemente sin fondo (PNG)." />

                    <x-slot:pie>
                        <button class="boton" type="submit">
                            <i class="fa-solid fa-floppy-disk"></i> Guardar datos publicos
                        </button>
                    </x-slot:pie>
                </x-panel.card>
            </form>

            <x-panel.card titulo="Tu foto" icono="fa-camera"
                          descripcion="Es la imagen principal de tu web.">

                @if ($perfil->url_foto)
                    <figure class="foto-perfil">
                        <img class="foto-perfil__imagen" src="{{ $perfil->url_foto }}"
                             alt="Foto de {{ $perfil->nombre_publico }}">
                    </figure>

                    <x-panel.confirmar-borrado :accion="route('panel.contenido.foto')"
                                               titulo="Quitar la foto"
                                               mensaje="Tu web se quedara sin foto hasta que subas otra."
                                               etiqueta="Quitar la foto" />
                @else
                    <x-panel.empty-state icono="fa-camera" titulo="Todavia no hay foto"
                                         mensaje="Sube una desde el formulario de al lado. Se vera mucho mejor si tiene el fondo recortado." />
                @endif

            </x-panel.card>

        </div>

    </section>

    {{-- Sobre mi --}}
    <section data-panel="sobre-mi" @if ($pestana !== 'sobre-mi') hidden @endif>

        <form method="POST" action="{{ route('panel.contenido.sobre-mi') }}">
            @csrf
            @method('PUT')

            <x-panel.card titulo="Sobre mi" icono="fa-user-pen"
                          descripcion="Cuentale a quien te lee quien eres, como trabajas y por que puede confiar en ti.">

                <div class="campo">
                    <label class="campo__etiqueta" for="sobre_mi">
                        Tu presentacion <span class="campo__obligatorio" aria-hidden="true">*</span>
                    </label>

                    <textarea class="campo__control editor-rico @error('sobre_mi') campo__control--error @enderror"
                              id="sobre_mi" name="sobre_mi"
                              placeholder="Soy psicologa sanitaria y llevo mas de diez anos acompanando a personas que...">{{ old('sobre_mi', $perfil->sobre_mi) }}</textarea>

                    @error('sobre_mi')
                        <p class="campo__error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p>
                    @enderror
                </div>

                <x-panel.aviso tono="info" titulo="Un consejo">
                    <p>Escribe como hablarias en la primera sesion: en primera persona, sin tecnicismos y
                        contando que puede esperar la persona que te lee.</p>
                </x-panel.aviso>

                <x-slot:pie>
                    <button class="boton" type="submit">
                        <i class="fa-solid fa-floppy-disk"></i> Guardar presentacion
                    </button>
                </x-slot:pie>
            </x-panel.card>
        </form>

    </section>

    {{-- Planes y precios --}}
    <section data-panel="planes" @if ($pestana !== 'planes') hidden @endif>

        <x-panel.card titulo="Planes y precios" icono="fa-tags"
                      descripcion="Las tarifas que se mostraran en tu web, tanto presenciales como online.">

            <x-slot:acciones>
                <button class="boton" type="button" id="boton-nuevo-plan">
                    <i class="fa-solid fa-plus"></i> Nuevo plan
                </button>
            </x-slot:acciones>

            @if ($planes->isEmpty())
                <x-panel.empty-state icono="fa-tags" titulo="Todavia no hay planes"
                                     mensaje="Anade al menos uno por modalidad para que tus pacientes sepan cuanto cuesta una sesion.">
                    <button class="boton" type="button" data-nuevo-plan>
                        <i class="fa-solid fa-plus"></i> Crear el primero
                    </button>
                </x-panel.empty-state>
            @else
                <ol class="ordenable">
                    @foreach ($planes as $plan)
                        @php
                            $datosPlan = json_encode([
                                'id' => $plan->id,
                                'nombre' => $plan->nombre,
                                'modalidad' => $plan->modalidad,
                                'precio' => (float) $plan->precio,
                                'descripcion' => $plan->descripcion,
                                'caracteristicas' => $plan->caracteristicas_texto,
                                'destacado' => $plan->destacado,
                            ]);
                        @endphp

                        <li class="ordenable__fila">

                            @include('panel.partials.flechas-orden', [
                                'rutaMover' => 'panel.planes.mover',
                                'registro' => $plan,
                                'primero' => $loop->first,
                                'ultimo' => $loop->last,
                                'posicion' => $loop->iteration,
                            ])

                            <div class="ordenable__cuerpo">
                                <p class="ordenable__titulo">
                                    {{ $plan->nombre }}
                                    @if ($plan->destacado)
                                        <x-panel.badge tono="atencion">Destacado</x-panel.badge>
                                    @endif
                                </p>

                                <p class="plan__meta">
                                    <x-panel.badge :tono="$plan->modalidad === 'online' ? 'info' : 'exito'">
                                        {{ $plan->modalidad_visible }}
                                    </x-panel.badge>
                                    <strong class="plan__precio">{{ $plan->precio_visible }}</strong>
                                    <span class="plan__sesion">por sesion</span>
                                </p>

                                @if (filled($plan->descripcion))
                                    <p class="celda__secundario">{{ $plan->descripcion }}</p>
                                @endif

                                @if (filled($plan->caracteristicas))
                                    <ul class="plan__caracteristicas">
                                        @foreach ($plan->caracteristicas as $caracteristica)
                                            <li><i class="fa-solid fa-check"></i> {{ $caracteristica }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>

                            <div class="ordenable__acciones">
                                <button class="boton boton--secundario boton--icono" type="button"
                                        data-editar-plan data-plan="{{ $datosPlan }}"
                                        aria-label="Editar el plan" title="Editar">
                                    <i class="fa-solid fa-pen"></i>
                                </button>

                                <x-panel.confirmar-borrado :accion="route('panel.planes.destroy', $plan)"
                                                           titulo="Eliminar plan"
                                                           :mensaje="'Se borrara para siempre el plan: ' . $plan->nombre"
                                                           etiqueta="Eliminar plan" solo-icono />
                            </div>

                        </li>
                    @endforeach
                </ol>
            @endif

        </x-panel.card>

    </section>

    {{-- Consulta y mapa --}}
    <section data-panel="consulta" @if ($pestana !== 'consulta') hidden @endif>

        <form method="POST" action="{{ route('panel.contenido.consulta') }}">
            @csrf
            @method('PUT')

            <x-panel.card titulo="Donde y cuando atiendes" icono="fa-location-dot"
                          descripcion="Estos datos se muestran en la pagina de contacto y junto al formulario de reservas.">

                <div class="rejilla rejilla--2">
                    <x-panel.campo nombre="lugar_consulta" etiqueta="Nombre del lugar"
                                   :valor="$perfil->lugar_consulta"
                                   placeholder="Por ejemplo: Centro de Psicologia Aurora" />

                    <x-panel.campo nombre="horarios" etiqueta="Horario visible en la web"
                                   :valor="$perfil->horarios"
                                   placeholder="Lunes a viernes, de 9:00 a 20:00"
                                   ayuda="Es solo informativo. Los huecos reales salen de Disponibilidad." />
                </div>

                <x-panel.campo nombre="direccion" etiqueta="Direccion"
                               :valor="$perfil->direccion"
                               placeholder="Calle Mayor 12, 2 B, 28013 Madrid" />

                <x-panel.campo nombre="mapa_embed" etiqueta="Mapa de Google" tipo="textarea"
                               :valor="$perfil->mapa_embed" rows="3"
                               placeholder="Pega aqui el codigo que te da Google Maps"
                               ayuda="En Google Maps: Compartir > Insertar un mapa > Copiar HTML. Puedes pegar el codigo entero, nos quedamos solo con lo necesario." />

                <x-panel.aviso tono="info" titulo="Como conseguir el mapa">
                    <ol class="lista-pasos">
                        <li>Busca la direccion de tu consulta en Google Maps.</li>
                        <li>Pulsa <strong>Compartir</strong> y luego la pestana <strong>Insertar un mapa</strong>.</li>
                        <li>Pulsa <strong>Copiar HTML</strong> y pega el resultado en el campo de arriba.</li>
                    </ol>
                </x-panel.aviso>

                @if (filled($perfil->mapa_embed))
                    <div class="mapa-vista">
                        <iframe class="mapa-vista__marco" src="{{ $perfil->mapa_embed }}"
                                title="Mapa de la consulta" loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                @endif

                <x-slot:pie>
                    <button class="boton" type="submit">
                        <i class="fa-solid fa-floppy-disk"></i> Guardar datos de la consulta
                    </button>
                </x-slot:pie>
            </x-panel.card>
        </form>

    </section>

    {{-- Buscadores --}}
    <section data-panel="seo" @if ($pestana !== 'seo') hidden @endif>

        <form method="POST" action="{{ route('panel.contenido.seo') }}">
            @csrf
            @method('PUT')

            <x-panel.card titulo="Pagina de inicio" icono="fa-magnifying-glass-chart"
                          descripcion="Asi apareceras en Google cuando alguien busque tu nombre." class="separado">

                <x-panel.campo nombre="meta_title" etiqueta="Titulo en Google"
                               :valor="$perfil->meta_title" maxlength="70"
                               placeholder="Ana Ruiz | Psicologa en Madrid"
                               ayuda="Maximo 70 caracteres." data-contador="meta_title" />

                <x-panel.campo nombre="meta_description" etiqueta="Descripcion en Google" tipo="textarea"
                               :valor="$perfil->meta_description" maxlength="170" rows="3"
                               placeholder="Psicologa sanitaria en Madrid. Terapia presencial y online para ansiedad, autoestima y duelo."
                               ayuda="Maximo 170 caracteres." data-contador="meta_description" />

                @php
                    $vistaTitulo = old('meta_title', $perfil->meta_title) ?: ($perfil->nombre_publico ?: 'Tu nombre | Psicologa');
                    $vistaDescripcion = old('meta_description', $perfil->meta_description) ?: ($perfil->eslogan ?: 'Aqui se vera la descripcion de tu web.');
                @endphp

                <div class="resultado-google">
                    <p class="resultado-google__url">{{ config('app.url') }}</p>
                    <p class="resultado-google__titulo" data-vista="titulo">{{ $vistaTitulo }}</p>
                    <p class="resultado-google__descripcion" data-vista="descripcion">{{ $vistaDescripcion }}</p>
                </div>
            </x-panel.card>

            <x-panel.card titulo="Resto de paginas" icono="fa-file-lines"
                          descripcion="Opcional. Si dejas una vacia se usaran los textos de la pagina de inicio.">

                @foreach ($paginasSeo as $clave => $nombre)
                    <fieldset class="seo-pagina">
                        <legend class="seo-pagina__titulo">{{ $nombre }}</legend>

                        <div class="rejilla rejilla--2">
                            <x-panel.campo :nombre="'paginas[' . $clave . '][title]'"
                                           :id="'seo-' . $clave . '-title'" etiqueta="Titulo en Google"
                                           :valor="$seo[$clave]['title']" maxlength="70"
                                           :placeholder="$nombre . ' | ' . ($perfil->nombre_publico ?: 'Tu nombre')" />

                            <x-panel.campo :nombre="'paginas[' . $clave . '][description]'"
                                           :id="'seo-' . $clave . '-description'" etiqueta="Descripcion en Google"
                                           :valor="$seo[$clave]['description']" maxlength="170" />
                        </div>
                    </fieldset>
                @endforeach

                <x-slot:pie>
                    <button class="boton" type="submit">
                        <i class="fa-solid fa-floppy-disk"></i> Guardar textos para buscadores
                    </button>
                </x-slot:pie>
            </x-panel.card>
        </form>

    </section>

    <x-panel.modal id="modal-plan" titulo="Nuevo plan" icono="fa-tags">
        <form method="POST" action="{{ route('panel.planes.store') }}" id="form-plan">
            @csrf
            <input type="hidden" name="_method" value="POST" id="plan-metodo">

            <x-panel.campo nombre="nombre" id="plan-nombre" etiqueta="Nombre del plan" maxlength="255"
                           placeholder="Por ejemplo: Sesion individual" obligatorio />

            <div class="rejilla rejilla--2">
                <x-panel.campo nombre="modalidad" id="plan-modalidad" etiqueta="Modalidad" tipo="select"
                               :opciones="\App\Models\Plan::MODALIDADES" obligatorio />

                <x-panel.campo nombre="precio" id="plan-precio" etiqueta="Precio por sesion (€)"
                               tipo="number" step="0.01" min="0" placeholder="60" obligatorio />
            </div>

            <x-panel.campo nombre="descripcion" id="plan-descripcion" etiqueta="Descripcion breve"
                           tipo="textarea" rows="2" maxlength="1000"
                           placeholder="Sesion de 50 minutos en consulta" />

            <x-panel.campo nombre="caracteristicas" id="plan-caracteristicas" etiqueta="Que incluye"
                           tipo="textarea" rows="4" maxlength="2000"
                           placeholder="Sesion de 50 minutos&#10;Material de trabajo entre sesiones&#10;Seguimiento por email"
                           ayuda="Una linea por cada punto. Se mostraran como una lista." />

            <x-panel.toggle nombre="destacado" id="plan-destacado"
                            etiqueta="Destacar este plan en la web" />
        </form>

        <x-slot:pie>
            <button class="boton boton--secundario" type="button" data-cerrar-modal>Cancelar</button>
            <button class="boton" type="submit" form="form-plan">
                <i class="fa-solid fa-floppy-disk"></i> Guardar plan
            </button>
        </x-slot:pie>
    </x-panel.modal>

@endsection

@include('panel.partials.assets-editor')

@push('estilos')
    <link rel="stylesheet" href="{{ recurso('panel/css/contenido.css') }}">
@endpush

@push('scripts')
    @php
        $datosPlanes = [
            'urlCrear' => route('panel.planes.store'),
            'urlBase' => url('/panel-psicologa/contenido/planes'),
            'errores' => $errors->any(),
        ];
    @endphp

    <script id="datos-planes" type="application/json">@json($datosPlanes)</script>
    <script src="{{ recurso('panel/js/contenido.js') }}"></script>
@endpush
