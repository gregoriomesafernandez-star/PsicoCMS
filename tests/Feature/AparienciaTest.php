<?php

namespace Tests\Feature;

use App\Http\Middleware\VerificarInstalacion;
use App\Models\Psicologa;
use App\Services\AparienciaService;
use App\Services\ModuloService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AparienciaTest extends TestCase
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

    private function apariencia(): AparienciaService
    {
        return app(AparienciaService::class);
    }

    private function modulos(): ModuloService
    {
        return app(ModuloService::class);
    }

    /* ---------- Proteccion ---------- */

    public function test_apariencia_y_modulos_requieren_autenticacion(): void
    {
        Auth::logout();

        $this->put(route('panel.apariencia'), ['tema' => 'oscuro', 'color' => 'azul'])
            ->assertRedirect('/acceso-psicologa');
        $this->get('/panel-psicologa/modulos')->assertRedirect('/acceso-psicologa');
        $this->put(route('panel.modulos.update'))->assertRedirect('/acceso-psicologa');
    }

    /* ---------- Apariencia ---------- */

    public function test_por_defecto_el_panel_es_claro_y_azul(): void
    {
        $this->assertSame(['tema' => 'claro', 'color' => 'azul'], $this->apariencia()->actual());

        $this->get('/panel-psicologa')
            ->assertOk()
            ->assertSee('data-tema="claro"', false)
            ->assertSee('data-color="azul"', false);
    }

    public function test_hay_ocho_colores_y_dos_temas(): void
    {
        $this->assertCount(8, AparienciaService::COLORES);
        $this->assertCount(2, AparienciaService::TEMAS);

        // Uno de ellos es el azul original del dashboard.
        $this->assertSame('#3d5a80', AparienciaService::COLORES['azul'][1]);

        foreach (AparienciaService::COLORES as $clave => [$nombre, $hex]) {
            $this->assertMatchesRegularExpression('/^#[0-9a-f]{6}$/', $hex, "{$clave} tiene mal el color");
        }
    }

    public function test_se_guarda_el_tema_y_el_color(): void
    {
        $this->put(route('panel.apariencia'), ['tema' => 'oscuro', 'color' => 'lavanda'])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertSame(['tema' => 'oscuro', 'color' => 'lavanda'], $this->apariencia()->actual());
    }

    public function test_lo_guardado_se_pinta_en_el_html(): void
    {
        $this->apariencia()->guardar('oscuro', 'terracota');

        $this->get('/panel-psicologa')
            ->assertOk()
            ->assertSee('data-tema="oscuro"', false)
            ->assertSee('data-color="terracota"', false);
    }

    public function test_la_apariencia_sobrevive_al_cierre_de_sesion(): void
    {
        $psicologa = Auth::user();

        $this->put(route('panel.apariencia'), ['tema' => 'oscuro', 'color' => 'oceano']);

        // Se cierra sesion y se vuelve a entrar: no se guarda en la sesion, sino en ajustes.
        $this->post(route('panel.salir'));
        Auth::logout();
        $this->actingAs($psicologa);

        $this->get('/panel-psicologa')
            ->assertOk()
            ->assertSee('data-tema="oscuro"', false)
            ->assertSee('data-color="oceano"', false);
    }

    public function test_un_tema_o_color_inventado_se_rechaza(): void
    {
        $this->put(route('panel.apariencia'), ['tema' => 'neon', 'color' => 'azul'])
            ->assertSessionHasErrors('tema');

        $this->put(route('panel.apariencia'), ['tema' => 'claro', 'color' => 'fucsia'])
            ->assertSessionHasErrors('color');

        $this->assertSame(['tema' => 'claro', 'color' => 'azul'], $this->apariencia()->actual());
    }

    public function test_si_lo_guardado_deja_de_existir_se_cae_a_lo_de_por_defecto(): void
    {
        app(\App\Services\AjustesService::class)->guardarVarios([
            'panel_tema' => 'inventado',
            'panel_color' => 'inventado',
        ], 'apariencia');

        $this->assertSame(['tema' => 'claro', 'color' => 'azul'], $this->apariencia()->actual());
    }

    public function test_el_modal_de_apariencia_esta_en_todas_las_pantallas(): void
    {
        foreach (['/panel-psicologa', '/panel-psicologa/citas', '/panel-psicologa/modulos'] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertSee('modal-apariencia', false)
                ->assertSee('data-abrir-apariencia', false);
        }
    }

    /* ---------- Modulos ---------- */

    public function test_todos_los_modulos_empiezan_encendidos(): void
    {
        $this->assertSame(7, count($this->modulos()->claves()));

        foreach ($this->modulos()->claves() as $clave) {
            $this->assertTrue($this->modulos()->activo($clave), "{$clave} deberia estar encendido");
        }
    }

    public function test_la_pantalla_de_modulos_los_lista(): void
    {
        $this->get('/panel-psicologa/modulos')
            ->assertOk()
            ->assertSee('Blog')
            ->assertSee('Reserva de citas')
            ->assertSee('Preguntas frecuentes')
            ->assertSee('Redes sociales');
    }

    public function test_se_apagan_los_modulos_que_no_vienen_marcados(): void
    {
        $this->put(route('panel.modulos.update'), ['modulos' => ['blog', 'faq']])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertTrue($this->modulos()->activo('blog'));
        $this->assertTrue($this->modulos()->activo('faq'));
        $this->assertFalse($this->modulos()->activo('reservas'));
        $this->assertFalse($this->modulos()->activo('redes'));
    }

    public function test_se_pueden_apagar_todos(): void
    {
        $this->put(route('panel.modulos.update'), [])->assertRedirect();

        foreach ($this->modulos()->claves() as $clave) {
            $this->assertFalse($this->modulos()->activo($clave));
        }

        $this->assertSame(0, $this->modulos()->resumen()['activos']);
    }

    public function test_una_clave_inventada_no_crea_ningun_modulo(): void
    {
        $this->put(route('panel.modulos.update'), ['modulos' => ['blog', 'inventado']]);

        $this->assertSame(7, count($this->modulos()->estado()));
        $this->assertFalse($this->modulos()->activo('inventado'));
    }

    public function test_el_helper_responde_al_estado_guardado(): void
    {
        $this->assertTrue(modulo_activo('blog'));

        $this->modulos()->guardar(['faq']);

        $this->assertFalse(modulo_activo('blog'));
        $this->assertTrue(modulo_activo('faq'));
        $this->assertFalse(modulo_activo('no_existe'));
    }

    /* ---------- Efecto en el menu ---------- */

    /**
     * Solo el menu lateral: la pagina de inicio enlaza tambien al blog y a las
     * preguntas desde "Proximos pasos", y eso no es lo que se esta probando.
     */
    private function sidebar(): string
    {
        $html = $this->get('/panel-psicologa')->assertOk()->getContent();

        preg_match('/<aside class="sidebar".*?<\/aside>/s', $html, $coincidencias);

        $this->assertNotEmpty($coincidencias, 'No se ha encontrado el menu lateral');

        return $coincidencias[0];
    }

    public function test_apagar_un_modulo_lo_quita_del_menu_lateral(): void
    {
        $this->assertStringContainsString(route('panel.faqs.index'), $this->sidebar());

        $this->modulos()->guardar(array_diff($this->modulos()->claves(), ['faq']));

        $this->assertStringNotContainsString(route('panel.faqs.index'), $this->sidebar());
    }

    public function test_apagar_el_blog_quita_articulos_y_categorias(): void
    {
        $this->modulos()->guardar(array_diff($this->modulos()->claves(), ['blog']));

        $sidebar = $this->sidebar();

        $this->assertStringNotContainsString(route('panel.blog.index'), $sidebar);
        $this->assertStringNotContainsString(route('panel.categorias.index'), $sidebar);

        // La gestion sigue accesible por URL: el modulo solo decide que se ve en la web.
        $this->get('/panel-psicologa/blog')->assertOk();
    }

    /* ---------- Middleware de rutas publicas ---------- */

    public function test_el_middleware_cierra_una_ruta_publica_con_su_modulo_apagado(): void
    {
        Route::get('/ruta-de-prueba', fn () => 'visible')->middleware('modulo:blog');

        $this->get('/ruta-de-prueba')->assertOk()->assertSee('visible');

        $this->modulos()->guardar(array_diff($this->modulos()->claves(), ['blog']));

        $this->get('/ruta-de-prueba')->assertNotFound();
    }
}
