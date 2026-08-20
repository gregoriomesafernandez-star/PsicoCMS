<?php

namespace Tests\Feature;

use App\Http\Middleware\VerificarInstalacion;
use App\Models\Articulo;
use App\Models\Cita;
use App\Models\Faq;
use App\Models\FrasePublica;
use App\Models\Historia;
use App\Models\Paciente;
use App\Models\Psicologa;
use App\Models\RedSocial;
use App\Services\BuscadorService;
use App\Services\FraseService;
use App\Services\NotificacionService;
use App\Services\RedSocialService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ConfiguracionTest extends TestCase
{
    use RefreshDatabase;

    private Psicologa $psicologa;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(VerificarInstalacion::class);

        $this->psicologa = Psicologa::create([
            'nombre' => 'Ana',
            'apellidos' => 'Ruiz',
            'email' => 'ana@ejemplo.test',
            'telefono' => '600111000',
            'password' => 'secreto-largo',
        ]);

        $this->actingAs($this->psicologa);
    }

    private function frase(string $clave = 'hero_boton_cita', string $defecto = 'Pedir cita'): FrasePublica
    {
        return FrasePublica::create([
            'clave' => $clave,
            'seccion' => 'hero',
            'texto_defecto' => $defecto,
        ]);
    }

    /* ---------- Proteccion ---------- */

    public function test_todas_las_pantallas_requieren_autenticacion(): void
    {
        Auth::logout();

        foreach (['frases', 'redes', 'notificaciones', 'perfil', 'buscar', 'ayuda'] as $ruta) {
            $this->get("/panel-psicologa/{$ruta}")->assertRedirect('/acceso-psicologa');
        }

        $this->put(route('panel.frases.update'))->assertRedirect('/acceso-psicologa');
        $this->put(route('panel.perfil.update'))->assertRedirect('/acceso-psicologa');
        $this->post(route('panel.notificaciones.probar'))->assertRedirect('/acceso-psicologa');
    }

    /* ---------- Frases publicas ---------- */

    public function test_el_helper_devuelve_el_texto_por_defecto_y_luego_el_personalizado(): void
    {
        $frase = $this->frase();

        $this->assertSame('Pedir cita', frase('hero_boton_cita'));

        app(FraseService::class)->guardar(['hero_boton_cita' => 'Reserva tu hueco']);

        $this->assertSame('Reserva tu hueco', frase('hero_boton_cita'));
    }

    public function test_el_helper_admite_un_valor_de_respaldo(): void
    {
        $this->assertNull(frase('no_existe'));
        $this->assertSame('Respaldo', frase('no_existe', 'Respaldo'));
    }

    public function test_escribir_el_mismo_texto_por_defecto_no_lo_marca_como_personalizado(): void
    {
        $frase = $this->frase();

        app(FraseService::class)->guardar(['hero_boton_cita' => 'Pedir cita']);

        $this->assertNull($frase->fresh()->texto_personalizado);
        $this->assertSame(0, app(FraseService::class)->resumen()['personalizadas']);
    }

    public function test_vaciar_una_frase_devuelve_el_texto_del_tema(): void
    {
        $frase = $this->frase();

        $this->put(route('panel.frases.update'), ['frases' => ['hero_boton_cita' => 'Otro texto']]);
        $this->assertSame('Otro texto', frase('hero_boton_cita'));

        $this->put(route('panel.frases.update'), ['frases' => ['hero_boton_cita' => '']]);

        $this->assertNull($frase->fresh()->texto_personalizado);
        $this->assertSame('Pedir cita', frase('hero_boton_cita'));
    }

    public function test_se_restaura_una_frase_concreta(): void
    {
        $frase = $this->frase();
        $frase->update(['texto_personalizado' => 'Mio']);

        $this->put(route('panel.frases.restaurar', $frase))->assertRedirect();

        $this->assertNull($frase->fresh()->texto_personalizado);
    }

    public function test_se_restauran_todas_de_una_vez(): void
    {
        $this->frase()->update(['texto_personalizado' => 'Uno']);
        $this->frase('sobre_mi_titulo', 'Sobre mi')->update(['texto_personalizado' => 'Dos']);

        $this->put(route('panel.frases.restaurar-todas'))->assertRedirect();

        $this->assertSame(0, FrasePublica::whereNotNull('texto_personalizado')->count());
    }

    public function test_la_pantalla_agrupa_las_frases_por_seccion(): void
    {
        $this->frase();

        $grupos = $this->get('/panel-psicologa/frases')->assertOk()->viewData('grupos');

        $this->assertArrayHasKey('hero', $grupos);
        $this->assertSame('Portada', $grupos['hero']['nombre']);
    }

    /* ---------- Redes sociales ---------- */

    public function test_las_redes_se_crean_solas_del_catalogo(): void
    {
        $this->get('/panel-psicologa/redes')->assertOk();

        $this->assertSame(count(RedSocialService::REDES), RedSocial::count());
    }

    public function test_se_guardan_las_direcciones_y_la_visibilidad(): void
    {
        $this->get('/panel-psicologa/redes');

        $this->put(route('panel.redes.update'), [
            'urls' => ['instagram' => 'https://instagram.com/ana'],
            'activas' => ['instagram'],
        ])->assertSessionHasNoErrors();

        $instagram = RedSocial::where('red', 'instagram')->first();

        $this->assertSame('https://instagram.com/ana', $instagram->url);
        $this->assertTrue($instagram->activa);
    }

    public function test_una_red_sin_direccion_no_puede_quedar_visible(): void
    {
        $this->get('/panel-psicologa/redes');

        $this->put(route('panel.redes.update'), [
            'urls' => ['facebook' => ''],
            'activas' => ['facebook'],
        ])->assertSessionHasNoErrors();

        $this->assertFalse(RedSocial::where('red', 'facebook')->first()->activa);
        $this->assertSame(0, app(RedSocialService::class)->visibles()->count());
    }

    public function test_una_direccion_invalida_se_rechaza(): void
    {
        $this->get('/panel-psicologa/redes');

        $this->put(route('panel.redes.update'), [
            'urls' => ['instagram' => 'esto no es una url'],
        ])->assertSessionHasErrors('urls.instagram');
    }

    /* ---------- Email y notificaciones ---------- */

    public function test_la_contrasena_de_aplicacion_se_guarda_cifrada(): void
    {
        $this->put(route('panel.notificaciones.update'), [
            'email' => 'ana@gmail.com',
            'password' => 'abcd efgh ijkl mnop',
            'nombre' => 'Consulta de Ana',
            'avisar_citas' => '1',
        ])->assertSessionHasNoErrors();

        $guardada = app(\App\Services\AjustesService::class)->obtener('smtp_password');

        $this->assertNotSame('abcdefghijklmnop', $guardada);
        $this->assertSame('abcdefghijklmnop', Crypt::decryptString($guardada));
        $this->assertTrue(app(NotificacionService::class)->configurado());
    }

    public function test_la_contrasena_no_se_devuelve_nunca_a_la_pantalla(): void
    {
        $this->put(route('panel.notificaciones.update'), [
            'email' => 'ana@gmail.com',
            'password' => 'abcdefghijklmnop',
        ]);

        $respuesta = $this->get('/panel-psicologa/notificaciones')->assertOk();

        $respuesta->assertDontSee('abcdefghijklmnop');
        $this->assertTrue($respuesta->viewData('configuracion')['tiene_password']);
        $this->assertArrayNotHasKey('password', $respuesta->viewData('configuracion'));
    }

    public function test_guardar_sin_contrasena_conserva_la_que_habia(): void
    {
        $this->put(route('panel.notificaciones.update'), [
            'email' => 'ana@gmail.com',
            'password' => 'abcdefghijklmnop',
        ]);

        $this->put(route('panel.notificaciones.update'), [
            'email' => 'otra@gmail.com',
            'password' => '',
        ])->assertSessionHasNoErrors();

        $this->assertTrue(app(NotificacionService::class)->configurado());
        $this->assertSame('otra@gmail.com', app(NotificacionService::class)->configuracion()['email']);
    }

    public function test_una_contrasena_demasiado_corta_se_rechaza(): void
    {
        $this->put(route('panel.notificaciones.update'), [
            'email' => 'ana@gmail.com',
            'password' => 'corta',
        ])->assertSessionHasErrors('password');
    }

    public function test_el_email_es_obligatorio(): void
    {
        $this->put(route('panel.notificaciones.update'), ['email' => 'no-es-un-email'])
            ->assertSessionHasErrors('email');
    }

    public function test_borrar_los_datos_desactiva_los_avisos(): void
    {
        $this->put(route('panel.notificaciones.update'), [
            'email' => 'ana@gmail.com',
            'password' => 'abcdefghijklmnop',
        ]);

        $this->delete(route('panel.notificaciones.olvidar'))->assertRedirect();

        $this->assertFalse(app(NotificacionService::class)->configurado());
    }

    public function test_avisar_no_lanza_si_no_hay_nada_configurado(): void
    {
        $this->assertFalse(app(NotificacionService::class)->avisar('Hola', 'panel.notificaciones.correo-prueba'));
    }

    /* ---------- Mi perfil ---------- */

    public function test_se_guardan_los_datos_privados_con_el_telefono_normalizado(): void
    {
        $this->put(route('panel.perfil.update'), [
            'nombre' => 'Ana Maria',
            'apellidos' => 'Ruiz Molina',
            'email' => 'nueva@ejemplo.test',
            'telefono' => '611 22 33 44',
        ])->assertSessionHasNoErrors();

        $this->psicologa->refresh();

        $this->assertSame('Ana Maria', $this->psicologa->nombre);
        $this->assertSame('nueva@ejemplo.test', $this->psicologa->email);
        $this->assertSame('611223344', $this->psicologa->telefono);
    }

    public function test_el_avatar_se_sube_y_se_puede_quitar(): void
    {
        Storage::fake('public');

        $this->put(route('panel.perfil.update'), [
            'nombre' => 'Ana',
            'email' => 'ana@ejemplo.test',
            'telefono' => '600111000',
            'avatar' => UploadedFile::fake()->create('yo.jpg', 90, 'image/jpeg'),
        ])->assertSessionHasNoErrors();

        $ruta = $this->psicologa->fresh()->avatar;

        $this->assertNotNull($ruta);
        Storage::disk('public')->assertExists($ruta);

        $this->delete(route('panel.perfil.avatar'))->assertRedirect();

        $this->assertNull($this->psicologa->fresh()->avatar);
        Storage::disk('public')->assertMissing($ruta);
    }

    public function test_se_cambia_la_contrasena_dando_la_actual(): void
    {
        $this->put(route('panel.perfil.password'), [
            'password_actual' => 'secreto-largo',
            'password' => 'contrasena-nueva',
            'password_confirmation' => 'contrasena-nueva',
        ])->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('contrasena-nueva', $this->psicologa->fresh()->password));
    }

    public function test_no_se_cambia_la_contrasena_si_la_actual_es_incorrecta(): void
    {
        $this->put(route('panel.perfil.password'), [
            'password_actual' => 'me-la-invento',
            'password' => 'contrasena-nueva',
            'password_confirmation' => 'contrasena-nueva',
        ])->assertSessionHasErrors('password_actual');

        $this->assertTrue(Hash::check('secreto-largo', $this->psicologa->fresh()->password));
    }

    public function test_las_dos_contrasenas_nuevas_tienen_que_coincidir(): void
    {
        $this->put(route('panel.perfil.password'), [
            'password_actual' => 'secreto-largo',
            'password' => 'contrasena-nueva',
            'password_confirmation' => 'otra-distinta',
        ])->assertSessionHasErrors('password');
    }

    /* ---------- Buscador global ---------- */

    private function datosParaBuscar(): void
    {
        $paciente = Paciente::create(['nombre' => 'Marcos', 'apellidos' => 'Perez', 'telefono' => '611223344']);

        Historia::create([
            'paciente_id' => $paciente->id,
            'fecha_sesion' => now()->subDay(),
            'titulo' => 'Sesion sobre ansiedad',
            'contenido' => '<p>Contenido cifrado</p>',
        ]);

        Articulo::create([
            'titulo' => 'Como manejar la ansiedad',
            'slug' => 'como-manejar-la-ansiedad',
            'contenido' => '<p>Texto</p>',
            'publicado' => true,
            'publicado_at' => now(),
        ]);

        Faq::create(['pregunta' => '¿La ansiedad se cura?', 'respuesta' => '<p>Si</p>', 'orden' => 1, 'activa' => true]);
    }

    public function test_el_buscador_encuentra_en_varios_sitios_a_la_vez(): void
    {
        $this->datosParaBuscar();

        $resultados = $this->get(route('panel.buscador', ['q' => 'ansiedad']))
            ->assertOk()->viewData('resultados');

        $this->assertArrayHasKey('historias', $resultados);
        $this->assertArrayHasKey('articulos', $resultados);
        $this->assertArrayHasKey('faqs', $resultados);
    }

    public function test_el_buscador_encuentra_al_paciente_por_telefono(): void
    {
        $this->datosParaBuscar();

        $resultados = $this->get(route('panel.buscador', ['q' => '611223344']))
            ->assertOk()->viewData('resultados');

        $this->assertArrayHasKey('pacientes', $resultados);
        $this->assertSame('Marcos Perez', $resultados['pacientes']->first()['titulo']);
    }

    /**
     * Antes, los digitos sueltos de un texto se buscaban como telefono: buscar
     * "primer articulo2" dejaba "2" y sacaba a todo paciente con un 2 en su numero.
     */
    public function test_buscar_un_titulo_con_numeros_no_arrastra_pacientes(): void
    {
        Paciente::create(['nombre' => 'Carlos', 'telefono' => '673634522']);
        Paciente::create(['nombre' => 'Santiago', 'telefono' => '321456778']);

        Articulo::create([
            'titulo' => 'primer articulo2',
            'slug' => 'primer-articulo2',
            'contenido' => '<p>Texto</p>',
            'publicado' => true,
            'publicado_at' => now(),
        ]);

        $resultados = $this->get(route('panel.buscador', ['q' => 'primer articulo2']))
            ->assertOk()->viewData('resultados');

        $this->assertSame(['articulos'], array_keys($resultados));
        $this->assertSame(1, $resultados['articulos']->count());
    }

    public function test_el_telefono_se_busca_solo_cuando_se_escribe_un_telefono(): void
    {
        Paciente::create(['nombre' => 'Carlos', 'telefono' => '673634522']);

        $this->assertTrue(Paciente::pareceTelefono('673634522'));
        $this->assertTrue(Paciente::pareceTelefono('673 63 45 22'));
        $this->assertTrue(Paciente::pareceTelefono('+34 673634522'));

        $this->assertFalse(Paciente::pareceTelefono('primer articulo2'));
        $this->assertFalse(Paciente::pareceTelefono('22'), 'Menos de 3 digitos no es un telefono');

        // Con espacios en medio sigue encontrandolo.
        $this->assertSame(1, Paciente::buscar('673 63 45 22')->count());
        $this->assertSame(0, Paciente::buscar('sesion 2')->count());
    }

    public function test_las_citas_se_buscan_por_fecha(): void
    {
        $paciente = Paciente::create(['nombre' => 'Marcos', 'telefono' => '611223344']);

        Cita::create([
            'paciente_id' => $paciente->id,
            'modalidad' => 'online',
            'fecha' => '2026-09-24',
            'hora_inicio' => '11:45',
            'hora_fin' => '12:35',
            'duracion' => 50,
            'estado' => 'confirmada',
        ]);

        foreach (['24/09/2026', '24-09-2026', '2026-09-24', '24.09.2026'] as $formato) {
            $resultados = $this->get(route('panel.buscador', ['q' => $formato]))
                ->assertOk()->viewData('resultados');

            $this->assertArrayHasKey('citas', $resultados, "No encuentra con el formato {$formato}");
            $this->assertSame(1, $resultados['citas']->count());
        }
    }

    public function test_las_citas_se_buscan_por_motivo(): void
    {
        $paciente = Paciente::create(['nombre' => 'Marcos', 'telefono' => '611223344']);

        Cita::create([
            'paciente_id' => $paciente->id,
            'modalidad' => 'presencial',
            'fecha' => '2026-10-06',
            'hora_inicio' => '14:30',
            'hora_fin' => '15:20',
            'duracion' => 50,
            'estado' => 'pendiente',
            'motivo' => '<p>Trabajo de duelo</p>',
        ]);

        $resultados = $this->get(route('panel.buscador', ['q' => 'duelo']))
            ->assertOk()->viewData('resultados');

        $this->assertArrayHasKey('citas', $resultados);

        // Cuando lo que coincide es el motivo, se muestra en el resultado.
        $this->assertStringContainsString('Trabajo de duelo', $resultados['citas']->first()['detalle']);
    }

    public function test_una_cita_del_buscador_abre_su_modal_de_detalle(): void
    {
        $paciente = Paciente::create(['nombre' => 'Juan', 'apellidos' => 'Evaristo', 'telefono' => '611223344']);

        Cita::create([
            'paciente_id' => $paciente->id,
            'modalidad' => 'presencial',
            'fecha' => '2026-10-07',
            'hora_inicio' => '09:00',
            'hora_fin' => '09:45',
            'duracion' => 45,
            'estado' => 'pendiente',
        ]);

        $respuesta = $this->get(route('panel.buscador', ['q' => 'Evaristo']))->assertOk();

        // El resultado abre el modal en vez de llevar al listado.
        $respuesta->assertSee('id="modal-detalle"', false)
            ->assertSee('data-ver-cita', false);

        // Y es el modal completo: el mismo del listado, con Editar funcional.
        $respuesta->assertSee('id="modal-cita"', false)
            ->assertSee('id="detalle-editar"', false)
            ->assertSee('cita-detalle.js', false)
            ->assertSee('citas.js', false);
    }

    public function test_al_editar_una_cita_desde_el_buscador_se_vuelve_a_la_busqueda(): void
    {
        $manana = \Carbon\Carbon::tomorrow();

        \App\Models\ConfiguracionDisponibilidad::actual()->fill([
            'duracion_presencial' => 45,
            'descanso_presencial_activo' => false,
            'hora_inicio' => '09:00:00',
            'hora_fin' => '20:00:00',
        ])->save();

        \App\Models\FranjaHoraria::create([
            'modalidad' => 'presencial',
            'dia_semana' => $manana->dayOfWeek,
            'hora_inicio' => '09:00:00',
        ]);

        $paciente = Paciente::create(['nombre' => 'Juan', 'apellidos' => 'Evaristo', 'telefono' => '611223344']);

        $cita = Cita::create([
            'paciente_id' => $paciente->id,
            'modalidad' => 'presencial',
            'fecha' => $manana->toDateString(),
            'hora_inicio' => '09:00',
            'hora_fin' => '09:45',
            'duracion' => 45,
            'estado' => 'pendiente',
        ]);

        // El formulario del buscador lleva el destino y el termino buscado.
        $this->get(route('panel.buscador', ['q' => 'Evaristo']))
            ->assertOk()
            ->assertSee('value="buscador"', false)
            ->assertSee('name="busqueda" value="Evaristo"', false);

        $this->put(route('panel.citas.update', $cita), [
            'paciente_nombre' => 'Juan',
            'paciente_apellidos' => 'Evaristo',
            'paciente_telefono' => '611223344',
            'modalidad' => 'presencial',
            'fecha' => $manana->toDateString(),
            'hora_inicio' => '09:00',
            'estado' => 'confirmada',
            'volver' => 'buscador',
            'busqueda' => 'Evaristo',
        ])->assertRedirect(route('panel.buscador', ['q' => 'Evaristo']));

        $this->assertSame('confirmada', $cita->fresh()->estado);
    }

    public function test_sin_citas_el_buscador_no_carga_sus_modales(): void
    {
        $respuesta = $this->get(route('panel.buscador', ['q' => 'nada-de-nada']))->assertOk();

        $respuesta->assertDontSee('id="modal-detalle"', false)
            ->assertDontSee('cita-detalle.js', false);
    }

    public function test_un_numero_suelto_no_se_toma_por_una_fecha(): void
    {
        $buscador = app(BuscadorService::class);

        $this->assertSame('2026-09-24', $buscador->fechaDelTexto('24/09/2026'));
        $this->assertSame('2026-09-24', $buscador->fechaDelTexto('24/09/26'));

        $this->assertNull($buscador->fechaDelTexto('673634522'));
        $this->assertNull($buscador->fechaDelTexto('primer articulo2'));
        $this->assertNull($buscador->fechaDelTexto('32/09/2026'), 'El 32 de septiembre no existe');
        $this->assertNull($buscador->fechaDelTexto('24/13/2026'), 'No hay mes 13');
    }

    public function test_se_puede_filtrar_por_tipo(): void
    {
        $this->datosParaBuscar();

        $resultados = $this->get(route('panel.buscador', ['q' => 'ansiedad', 'tipo' => 'articulos']))
            ->assertOk()->viewData('resultados');

        $this->assertSame(['articulos'], array_keys($resultados));
    }

    public function test_una_busqueda_demasiado_corta_no_devuelve_nada(): void
    {
        $this->datosParaBuscar();

        $respuesta = $this->get(route('panel.buscador', ['q' => 'a']))->assertOk();

        $this->assertSame([], $respuesta->viewData('resultados'));
        $this->assertFalse($respuesta->viewData('valida'));
        $respuesta->assertSee('Escribe un poco mas');
    }

    public function test_sin_busqueda_se_explica_donde_se_busca(): void
    {
        $this->get(route('panel.buscador'))
            ->assertOk()
            ->assertSee('Se busca en todo tu panel')
            ->assertSee('Pacientes');
    }

    public function test_el_resaltado_escapa_el_html(): void
    {
        Paciente::create(['nombre' => '<script>alert(1)</script>', 'telefono' => '622334455']);

        $respuesta = $this->get(route('panel.buscador', ['q' => 'script']))->assertOk();

        $respuesta->assertDontSee('<script>alert(1)</script>', false);
        $respuesta->assertSee('<mark class="resaltado">script</mark>', false);
    }

    /* ---------- Ayuda ---------- */

    public function test_la_ayuda_tiene_su_indice_y_sus_apartados(): void
    {
        $this->get('/panel-psicologa/ayuda')
            ->assertOk()
            ->assertSee('En esta guia')
            ->assertSee('Primeros pasos')
            ->assertSee('Tu disponibilidad')
            ->assertSee('Historias clinicas')
            ->assertSee('id="configuracion"', false);
    }

    /* ---------- Enlaces del panel ---------- */

    public function test_el_menu_y_la_cabecera_enlazan_a_las_secciones_nuevas(): void
    {
        $html = $this->get('/panel-psicologa')->assertOk()->getContent();

        foreach (['panel.frases', 'panel.redes', 'panel.notificaciones', 'panel.perfil', 'panel.ayuda'] as $ruta) {
            $this->assertStringContainsString(route($ruta), $html, "Falta el enlace a {$ruta}");
        }

        // El buscador de la cabecera ya apunta a su pagina y deja de estar deshabilitado.
        $this->assertStringContainsString(route('panel.buscador'), $html);
        $this->assertStringNotContainsString('Disponible proximamente" disabled', $html);
    }
}
