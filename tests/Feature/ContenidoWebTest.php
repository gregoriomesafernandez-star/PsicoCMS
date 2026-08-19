<?php

namespace Tests\Feature;

use App\Http\Middleware\VerificarInstalacion;
use App\Models\Especialidad;
use App\Models\PerfilPublico;
use App\Models\Plan;
use App\Models\Psicologa;
use App\Models\Servicio;
use App\Services\ContenidoWebService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ContenidoWebTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(VerificarInstalacion::class);

        $this->actingAs(Psicologa::create([
            'nombre' => 'Ana',
            'apellidos' => 'Ruiz',
            'email' => 'ana@ejemplo.test',
            'telefono' => '600111000',
            'password' => 'secreto-largo',
        ]));
    }

    private function datosDePerfil(array $extra = []): array
    {
        return array_merge([
            'nombre_publico' => 'Ana Ruiz',
            'eslogan' => 'Un espacio seguro',
            'num_colegiado' => 'M-1234',
            'telefono_citas' => '600 11 22 33',
            'email_citas' => 'citas@ejemplo.test',
        ], $extra);
    }

    /* ---------- Proteccion ---------- */

    public function test_el_contenido_web_requiere_autenticacion(): void
    {
        Auth::logout();

        $this->get('/panel-psicologa/contenido')->assertRedirect('/acceso-psicologa');
        $this->put(route('panel.contenido.perfil'), $this->datosDePerfil())->assertRedirect('/acceso-psicologa');
        $this->put(route('panel.contenido.sobre-mi'), ['sobre_mi' => 'x'])->assertRedirect('/acceso-psicologa');
        $this->put(route('panel.contenido.seo'))->assertRedirect('/acceso-psicologa');
        $this->post(route('panel.planes.store'), ['nombre' => 'x'])->assertRedirect('/acceso-psicologa');
    }

    public function test_servicios_y_especialidades_requieren_autenticacion(): void
    {
        $servicio = Servicio::create(['titulo' => 'Terapia', 'orden' => 1]);
        $especialidad = Especialidad::create(['nombre' => 'Ansiedad', 'orden' => 1]);

        Auth::logout();

        $this->get('/panel-psicologa/servicios')->assertRedirect('/acceso-psicologa');
        $this->get('/panel-psicologa/especialidades')->assertRedirect('/acceso-psicologa');
        $this->patch(route('panel.servicios.activo', $servicio))->assertRedirect('/acceso-psicologa');
        $this->delete(route('panel.especialidades.destroy', $especialidad))->assertRedirect('/acceso-psicologa');
    }

    /* ---------- Pantallas ---------- */

    public function test_la_pantalla_de_contenido_muestra_sus_pestanas(): void
    {
        $this->get('/panel-psicologa/contenido')
            ->assertOk()
            ->assertSee('Datos publicos')
            ->assertSee('Sobre mi')
            ->assertSee('Planes y precios')
            ->assertSee('Consulta y mapa')
            ->assertSee('Buscadores');
    }

    public function test_las_pantallas_del_catalogo_muestran_su_empty_state(): void
    {
        $this->get('/panel-psicologa/servicios')
            ->assertOk()
            ->assertSee('Todavia no hay servicios');

        $this->get('/panel-psicologa/especialidades')
            ->assertOk()
            ->assertSee('Todavia no hay especialidades')
            ->assertSee('Anadir las mas habituales');
    }

    /* ---------- Perfil publico ---------- */

    public function test_se_guardan_los_datos_publicos(): void
    {
        $this->put(route('panel.contenido.perfil'), $this->datosDePerfil())
            ->assertSessionHasNoErrors()
            ->assertSessionHas('pestana', 'perfil');

        $perfil = PerfilPublico::actual();

        $this->assertSame('Ana Ruiz', $perfil->nombre_publico);
        $this->assertSame('Un espacio seguro', $perfil->eslogan);
        $this->assertSame('citas@ejemplo.test', $perfil->email_citas);
    }

    public function test_el_eslogan_y_el_contacto_son_obligatorios(): void
    {
        $this->put(route('panel.contenido.perfil'), [
            'nombre_publico' => 'Ana Ruiz',
            'eslogan' => '',
            'telefono_citas' => '',
            'email_citas' => 'esto-no-es-un-email',
        ])->assertSessionHasErrors(['eslogan', 'telefono_citas', 'email_citas']);
    }

    public function test_la_foto_se_sube_y_se_puede_quitar(): void
    {
        Storage::fake('public');

        $this->put(route('panel.contenido.perfil'), $this->datosDePerfil([
            'foto' => UploadedFile::fake()->create('ana.jpg', 90, 'image/jpeg'),
        ]))->assertSessionHasNoErrors();

        $ruta = PerfilPublico::actual()->foto;

        $this->assertNotNull($ruta);
        Storage::disk('public')->assertExists($ruta);

        $this->delete(route('panel.contenido.foto'))->assertRedirect();

        $this->assertNull(PerfilPublico::actual()->fresh()->foto);
        Storage::disk('public')->assertMissing($ruta);
    }

    public function test_un_fichero_que_no_es_imagen_se_rechaza_como_foto(): void
    {
        Storage::fake('public');

        $this->put(route('panel.contenido.perfil'), $this->datosDePerfil([
            'foto' => UploadedFile::fake()->create('virus.exe', 20, 'application/x-msdownload'),
        ]))->assertSessionHasErrors('foto');

        $this->assertNull(PerfilPublico::actual()->foto);
    }

    /* ---------- Sobre mi ---------- */

    public function test_el_sobre_mi_se_guarda_saneado(): void
    {
        $this->put(route('panel.contenido.sobre-mi'), [
            'sobre_mi' => '<p onclick="robar()">Soy <strong>psicologa</strong></p><script>alert(1)</script>',
        ])->assertSessionHasNoErrors()->assertSessionHas('pestana', 'sobre-mi');

        $sobreMi = PerfilPublico::actual()->sobre_mi;

        $this->assertStringContainsString('<strong>psicologa</strong>', $sobreMi);
        $this->assertStringNotContainsString('onclick', $sobreMi);
        $this->assertStringNotContainsString('<script', $sobreMi);
    }

    public function test_el_sobre_mi_no_puede_quedarse_vacio(): void
    {
        $this->put(route('panel.contenido.sobre-mi'), ['sobre_mi' => ''])
            ->assertSessionHasErrors('sobre_mi');
    }

    /* ---------- Consulta y mapa ---------- */

    public function test_del_mapa_se_guarda_solo_la_url_del_iframe(): void
    {
        $this->put(route('panel.contenido.consulta'), [
            'direccion' => 'Calle Mayor 12, Madrid',
            'lugar_consulta' => 'Centro Aurora',
            'horarios' => 'Lunes a viernes de 9 a 20',
            'mapa_embed' => '<iframe src="https://www.google.com/maps/embed?pb=abc" width="600"></iframe>',
        ])->assertSessionHasNoErrors();

        $this->assertSame(
            'https://www.google.com/maps/embed?pb=abc',
            PerfilPublico::actual()->mapa_embed
        );
    }

    public function test_un_mapa_de_otro_dominio_se_rechaza(): void
    {
        $this->put(route('panel.contenido.consulta'), [
            'mapa_embed' => '<iframe src="https://sitio-malo.test/pixel.html"></iframe>',
        ])->assertSessionHasErrors('mapa_embed');

        $this->assertNull(PerfilPublico::actual()->mapa_embed);
    }

    public function test_el_mapa_puede_dejarse_vacio(): void
    {
        $this->put(route('panel.contenido.consulta'), [
            'direccion' => 'Calle Mayor 12',
            'mapa_embed' => '',
        ])->assertSessionHasNoErrors();

        $this->assertNull(PerfilPublico::actual()->mapa_embed);
    }

    /* ---------- SEO ---------- */

    public function test_el_seo_se_guarda_por_pagina(): void
    {
        $this->put(route('panel.contenido.seo'), [
            'meta_title' => 'Ana Ruiz | Psicologa',
            'meta_description' => 'Terapia presencial y online.',
            'paginas' => [
                'blog' => ['title' => 'Blog de Ana', 'description' => 'Articulos de psicologia.'],
                'inventada' => ['title' => 'No deberia guardarse'],
            ],
        ])->assertSessionHasNoErrors()->assertSessionHas('pestana', 'seo');

        $perfil = PerfilPublico::actual();

        $this->assertSame('Ana Ruiz | Psicologa', $perfil->meta_title);

        $seo = app(ContenidoWebService::class)->seo();

        $this->assertSame('Blog de Ana', $seo['blog']['title']);
        $this->assertArrayNotHasKey('inventada', $seo);
        $this->assertNull($seo['servicios']['title']);
    }

    public function test_el_titulo_seo_tiene_un_limite(): void
    {
        $this->put(route('panel.contenido.seo'), [
            'meta_title' => str_repeat('a', 71),
        ])->assertSessionHasErrors('meta_title');
    }

    /* ---------- Planes ---------- */

    public function test_se_crea_un_plan_con_sus_caracteristicas(): void
    {
        $this->post(route('panel.planes.store'), [
            'nombre' => 'Sesion individual',
            'modalidad' => 'presencial',
            'precio' => '60,50',
            'descripcion' => 'Sesion de 50 minutos',
            'caracteristicas' => "Sesion de 50 minutos\n\nSeguimiento por email\n   ",
            'destacado' => '1',
        ])->assertSessionHasNoErrors();

        $plan = Plan::first();

        $this->assertSame('60.50', $plan->precio);
        $this->assertSame(['Sesion de 50 minutos', 'Seguimiento por email'], $plan->caracteristicas);
        $this->assertTrue($plan->destacado);
        $this->assertSame(1, $plan->orden);
    }

    public function test_el_plan_exige_nombre_modalidad_y_precio(): void
    {
        $this->post(route('panel.planes.store'), [
            'nombre' => '',
            'modalidad' => 'telepatica',
            'precio' => 'gratis',
        ])->assertSessionHasErrors(['nombre', 'modalidad', 'precio']);

        $this->assertSame(0, Plan::count());
    }

    public function test_los_planes_se_reordenan_con_las_flechas(): void
    {
        $this->post(route('panel.planes.store'), ['nombre' => 'Primero', 'modalidad' => 'online', 'precio' => 50]);
        $this->post(route('panel.planes.store'), ['nombre' => 'Segundo', 'modalidad' => 'online', 'precio' => 55]);

        $segundo = Plan::where('nombre', 'Segundo')->first();

        $this->patch(route('panel.planes.mover', [$segundo, 'arriba']))->assertSessionHas('pestana', 'planes');

        $this->assertSame(['Segundo', 'Primero'], Plan::ordenados()->pluck('nombre')->all());
    }

    public function test_al_borrar_un_plan_el_resto_se_renumera(): void
    {
        $this->post(route('panel.planes.store'), ['nombre' => 'Uno', 'modalidad' => 'online', 'precio' => 50]);
        $this->post(route('panel.planes.store'), ['nombre' => 'Dos', 'modalidad' => 'online', 'precio' => 50]);
        $this->post(route('panel.planes.store'), ['nombre' => 'Tres', 'modalidad' => 'online', 'precio' => 50]);

        $dos = Plan::where('nombre', 'Dos')->first();

        $this->delete(route('panel.planes.destroy', $dos))->assertRedirect();

        $this->assertDatabaseMissing('planes', ['id' => $dos->id]);
        $this->assertSame([1, 2], Plan::ordenados()->pluck('orden')->all());
    }

    /* ---------- Servicios ---------- */

    public function test_se_crea_un_servicio_con_la_descripcion_saneada(): void
    {
        $this->post(route('panel.servicios.store'), [
            'titulo' => 'Terapia individual',
            'descripcion' => '<p onclick="x()">Sesiones <em>personalizadas</em></p><script>alert(1)</script>',
            'icono' => 'fa-comments',
            'activo' => '1',
        ])->assertSessionHasNoErrors();

        $servicio = Servicio::first();

        $this->assertSame('Terapia individual', $servicio->titulo);
        $this->assertStringContainsString('<em>personalizadas</em>', $servicio->descripcion);
        $this->assertStringNotContainsString('onclick', $servicio->descripcion);
        $this->assertSame('fa-comments', $servicio->icono);
        $this->assertTrue($servicio->activo);
    }

    public function test_un_icono_fuera_del_catalogo_se_rechaza(): void
    {
        $this->post(route('panel.servicios.store'), [
            'titulo' => 'Terapia',
            'icono' => 'fa-bomb',
        ])->assertSessionHasErrors('icono');

        $this->assertSame(0, Servicio::count());
    }

    public function test_el_servicio_sin_icono_usa_el_de_por_defecto(): void
    {
        $this->post(route('panel.servicios.store'), ['titulo' => 'Terapia'])
            ->assertSessionHasNoErrors();

        $this->assertNull(Servicio::first()->icono);
        $this->assertSame(Servicio::ICONO_POR_DEFECTO, Servicio::first()->icono_visible);
    }

    public function test_la_imagen_del_servicio_se_sustituye_y_se_borra_del_disco(): void
    {
        Storage::fake('public');

        $this->post(route('panel.servicios.store'), [
            'titulo' => 'Terapia',
            'imagen' => UploadedFile::fake()->create('uno.jpg', 90, 'image/jpeg'),
        ])->assertSessionHasNoErrors();

        $servicio = Servicio::first();
        $primera = $servicio->imagen;

        Storage::disk('public')->assertExists($primera);

        $this->put(route('panel.servicios.update', $servicio), [
            'titulo' => 'Terapia',
            'imagen' => UploadedFile::fake()->create('dos.jpg', 90, 'image/jpeg'),
        ])->assertSessionHasNoErrors();

        $segunda = $servicio->fresh()->imagen;

        $this->assertNotSame($primera, $segunda);
        Storage::disk('public')->assertMissing($primera);
        Storage::disk('public')->assertExists($segunda);

        $this->delete(route('panel.servicios.destroy', $servicio));

        Storage::disk('public')->assertMissing($segunda);
    }

    public function test_la_imagen_del_servicio_se_puede_quitar(): void
    {
        Storage::fake('public');

        $this->post(route('panel.servicios.store'), [
            'titulo' => 'Terapia',
            'imagen' => UploadedFile::fake()->create('uno.jpg', 90, 'image/jpeg'),
        ]);

        $servicio = Servicio::first();
        $ruta = $servicio->imagen;

        $this->put(route('panel.servicios.update', $servicio), [
            'titulo' => 'Terapia',
            'eliminar_imagen' => '1',
        ])->assertSessionHasNoErrors();

        $this->assertNull($servicio->fresh()->imagen);
        Storage::disk('public')->assertMissing($ruta);
    }

    public function test_la_visibilidad_del_servicio_se_cambia_con_un_boton(): void
    {
        $servicio = Servicio::create(['titulo' => 'Terapia', 'orden' => 1, 'activo' => true]);

        $this->patch(route('panel.servicios.activo', $servicio));
        $this->assertFalse($servicio->fresh()->activo);

        $this->patch(route('panel.servicios.activo', $servicio));
        $this->assertTrue($servicio->fresh()->activo);
    }

    public function test_el_buscador_de_servicios_filtra(): void
    {
        Servicio::create(['titulo' => 'Terapia de pareja', 'orden' => 1]);
        Servicio::create(['titulo' => 'Orientacion familiar', 'orden' => 2]);

        $this->assertSame(
            ['Terapia de pareja'],
            $this->get(route('panel.servicios.index', ['q' => 'pareja']))
                ->assertOk()->viewData('servicios')->pluck('titulo')->all()
        );
    }

    /* ---------- Especialidades ---------- */

    public function test_se_crea_una_especialidad_y_se_coloca_la_ultima(): void
    {
        $this->post(route('panel.especialidades.store'), ['nombre' => 'Ansiedad', 'activo' => '1']);
        $this->post(route('panel.especialidades.store'), ['nombre' => 'Duelo', 'activo' => '1']);

        $this->assertSame(['Ansiedad', 'Duelo'], Especialidad::ordenados()->pluck('nombre')->all());
        $this->assertSame([1, 2], Especialidad::ordenados()->pluck('orden')->all());
    }

    public function test_las_flechas_reordenan_las_especialidades(): void
    {
        $this->post(route('panel.especialidades.store'), ['nombre' => 'Primera']);
        $this->post(route('panel.especialidades.store'), ['nombre' => 'Segunda']);
        $this->post(route('panel.especialidades.store'), ['nombre' => 'Tercera']);

        $segunda = Especialidad::where('nombre', 'Segunda')->first();

        $this->patch(route('panel.especialidades.mover', [$segunda, 'arriba']));

        $this->assertSame(
            ['Segunda', 'Primera', 'Tercera'],
            Especialidad::ordenados()->pluck('nombre')->all()
        );
    }

    public function test_mover_la_primera_hacia_arriba_no_rompe_nada(): void
    {
        $this->post(route('panel.especialidades.store'), ['nombre' => 'Primera']);
        $this->post(route('panel.especialidades.store'), ['nombre' => 'Segunda']);

        $primera = Especialidad::where('nombre', 'Primera')->first();

        $this->patch(route('panel.especialidades.mover', [$primera, 'arriba']))->assertRedirect();

        $this->assertSame(['Primera', 'Segunda'], Especialidad::ordenados()->pluck('nombre')->all());
    }

    public function test_el_orden_se_arregla_aunque_haya_numeros_repetidos(): void
    {
        Especialidad::create(['nombre' => 'A', 'orden' => 5]);
        $b = Especialidad::create(['nombre' => 'B', 'orden' => 5]);

        $this->patch(route('panel.especialidades.mover', [$b, 'arriba']));

        $this->assertSame(['B', 'A'], Especialidad::ordenados()->pluck('nombre')->all());
        $this->assertSame([1, 2], Especialidad::ordenados()->pluck('orden')->all());
    }

    public function test_las_especialidades_de_ejemplo_se_cargan_una_sola_vez(): void
    {
        $this->post(route('panel.especialidades.ejemplos'))->assertRedirect();

        $creadas = Especialidad::count();

        $this->assertGreaterThan(0, $creadas);
        $this->assertSame(range(1, $creadas), Especialidad::ordenados()->pluck('orden')->all());

        $this->post(route('panel.especialidades.ejemplos'))->assertSessionHas('error');
        $this->assertSame($creadas, Especialidad::count());
    }

    public function test_el_resumen_de_la_pantalla_cuenta_lo_que_falta(): void
    {
        $respuesta = $this->get('/panel-psicologa/contenido')->assertOk();

        $this->assertSame(0, $respuesta->viewData('completitud')['hechos']);

        $this->put(route('panel.contenido.perfil'), $this->datosDePerfil());
        $this->put(route('panel.contenido.sobre-mi'), ['sobre_mi' => '<p>Hola</p>']);
        Servicio::create(['titulo' => 'Terapia', 'orden' => 1, 'activo' => true]);

        $completitud = $this->get('/panel-psicologa/contenido')->viewData('completitud');

        $this->assertSame(3, $completitud['hechos']);
        $this->assertTrue($completitud['puntos']['Servicios']);
        $this->assertFalse($completitud['puntos']['Planes y precios']);
    }
}
