<?php

namespace Tests\Feature;

use App\Http\Controllers\Panel\TemaController;
use App\Http\Middleware\VerificarInstalacion;
use App\Models\Especialidad;
use App\Models\Faq;
use App\Models\PerfilPublico;
use App\Models\Plan;
use App\Models\Psicologa;
use App\Models\Servicio;
use App\Services\TemaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class TemaTest extends TestCase
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

    private function temas(): TemaService
    {
        return app(TemaService::class);
    }

    /* ---------- Proteccion ---------- */

    public function test_los_temas_requieren_autenticacion(): void
    {
        Auth::logout();

        $this->get('/panel-psicologa/temas')->assertRedirect('/acceso-psicologa');
        $this->put(route('panel.temas.activar', 'serena'))->assertRedirect('/acceso-psicologa');
        $this->put(route('panel.temas.modo'), ['modo' => 'landing'])->assertRedirect('/acceso-psicologa');
        $this->get(route('panel.temas.previsualizar', 'serena'))->assertRedirect('/acceso-psicologa');
    }

    /* ---------- Catalogo de temas ---------- */

    public function test_hay_cinco_temas_instalados(): void
    {
        $this->assertSame(
            ['aurora', 'base', 'nube', 'serena', 'terra'],
            $this->temas()->slugsDisponibles()
        );
    }

    public function test_cada_tema_declara_lo_que_necesita_la_galeria(): void
    {
        foreach ($this->temas()->disponibles() as $slug => $tema) {
            $this->assertSame($slug, $tema['slug']);
            $this->assertNotEmpty($tema['nombre'], "{$slug} sin nombre");
            $this->assertNotEmpty($tema['descripcion'], "{$slug} sin descripcion");
            $this->assertSame(['landing', 'multipagina'], $tema['modos'], "{$slug} con modos raros");

            foreach (['primario', 'secundario', 'fondo', 'texto', 'acento'] as $rol) {
                $this->assertMatchesRegularExpression(
                    '/^#[0-9a-fA-F]{6}$/',
                    $tema['paleta'][$rol],
                    "{$slug} tiene mal el color {$rol}"
                );
            }
        }
    }

    public function test_cada_tema_tiene_su_hoja_de_paleta(): void
    {
        foreach ($this->temas()->slugsDisponibles() as $slug) {
            $this->assertFileExists(public_path("themes/{$slug}/css/theme.css"));
        }
    }

    public function test_los_temas_heredan_la_maquetacion_del_base(): void
    {
        $hojas = $this->temas()->hojas('serena');

        $this->assertCount(5, $hojas);
        $this->assertStringContainsString('themes/base/css/styles.css', $hojas[2]);
        $this->assertStringContainsString('themes/serena/css/theme.css', $hojas[4]);

        // La paleta va la ultima: es la que tiene que ganar.
        $this->assertStringContainsString('themes/serena', end($hojas));
    }

    public function test_el_tema_base_no_hereda_de_nadie(): void
    {
        foreach ($this->temas()->hojas('base') as $hoja) {
            $this->assertStringContainsString('themes/base/', $hoja);
        }
    }

    public function test_la_paleta_de_cada_tema_redefine_las_variables_del_base(): void
    {
        $variables = fn (string $slug) => preg_match_all(
            '/--color-[\w\d-]+:/',
            file_get_contents(public_path("themes/{$slug}/css/theme.css"))
        );

        $esperadas = $variables('base');

        $this->assertGreaterThan(40, $esperadas);

        foreach (['serena', 'aurora', 'terra', 'nube'] as $slug) {
            $this->assertSame($esperadas, $variables($slug), "{$slug} no redefine todas las variables");
        }
    }

    public function test_cada_tema_usa_su_propio_color_primario(): void
    {
        $primarios = [];

        foreach ($this->temas()->disponibles() as $slug => $tema) {
            $hoja = file_get_contents(public_path("themes/{$slug}/css/theme.css"));

            $this->assertStringContainsString(
                '--color-primary: ' . $tema['paleta']['primario'],
                $hoja,
                "{$slug} no usa el primario que anuncia su theme.json"
            );

            $primarios[] = $tema['paleta']['primario'];
        }

        $this->assertSame($primarios, array_unique($primarios), 'Dos temas comparten color primario');
    }

    /* ---------- Galeria ---------- */

    public function test_la_galeria_muestra_los_temas_y_el_enlace_de_diseno_personalizado(): void
    {
        $this->get('/panel-psicologa/temas')
            ->assertOk()
            ->assertSee('Calma')
            ->assertSee('Serena')
            ->assertSee('Aurora')
            ->assertSee('Terra')
            ->assertSee('Nube')
            ->assertSee('Una sola pagina')
            ->assertSee('Varias paginas')
            ->assertSee(TemaController::URL_DISENO_PERSONALIZADO);
    }

    public function test_la_galeria_marca_el_tema_activo(): void
    {
        $this->temas()->activar('terra');

        $this->assertSame('terra', $this->get('/panel-psicologa/temas')->assertOk()->viewData('activo'));
    }

    /* ---------- Activar tema y modo ---------- */

    public function test_se_activa_un_tema(): void
    {
        $this->put(route('panel.temas.activar', 'aurora'))
            ->assertRedirect()
            ->assertSessionHas('exito');

        $this->assertSame('aurora', $this->temas()->slugActivo());
    }

    public function test_un_tema_que_no_existe_da_404(): void
    {
        $this->put(route('panel.temas.activar', 'inventado'))->assertNotFound();

        $this->assertSame(TemaService::TEMA_POR_DEFECTO, $this->temas()->slugActivo());
    }

    public function test_si_el_tema_guardado_desaparece_se_cae_al_de_por_defecto(): void
    {
        app(\App\Services\AjustesService::class)->guardar('tema_activo', 'borrado', 'apariencia');

        $this->assertSame(TemaService::TEMA_POR_DEFECTO, $this->temas()->slugActivo());
    }

    public function test_se_cambia_el_formato_de_la_web(): void
    {
        $this->put(route('panel.temas.modo'), ['modo' => 'multipagina'])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertSame('multipagina', $this->temas()->modoActivo());

        $this->put(route('panel.temas.modo'), ['modo' => 'landing']);

        $this->assertSame('landing', $this->temas()->modoActivo());
    }

    public function test_un_formato_inventado_se_rechaza(): void
    {
        $this->put(route('panel.temas.modo'), ['modo' => 'carrusel'])
            ->assertSessionHasErrors('modo');

        $this->assertSame(TemaService::MODO_POR_DEFECTO, $this->temas()->modoActivo());
    }

    /* ---------- Previsualizacion ---------- */

    public function test_la_previsualizacion_usa_los_datos_reales(): void
    {
        $perfil = PerfilPublico::actual();
        $perfil->update([
            'nombre_publico' => 'Ana Ruiz Molina',
            'eslogan' => 'Un espacio seguro para volver a encontrarte',
            'telefono_citas' => '600 11 22 33',
            'sobre_mi' => '<p>Llevo diez anos acompanando procesos.</p>',
        ]);

        Servicio::create(['titulo' => 'Terapia individual', 'orden' => 1, 'activo' => true]);
        Especialidad::create(['nombre' => 'Ansiedad y estres', 'orden' => 1, 'activo' => true]);
        Plan::create(['nombre' => 'Sesion online', 'modalidad' => 'online', 'precio' => 55, 'orden' => 1]);
        Faq::create(['pregunta' => '¿Cuanto dura una sesion?', 'respuesta' => '<p>50 minutos.</p>', 'orden' => 1, 'activa' => true]);

        $this->get(route('panel.temas.previsualizar', 'serena'))
            ->assertOk()
            ->assertSee('Ana Ruiz Molina')
            ->assertSee('Un espacio seguro para volver a encontrarte')
            ->assertSee('Terapia individual')
            ->assertSee('Ansiedad y estres')
            ->assertSee('Sesion online')
            ->assertSee('¿Cuanto dura una sesion?', false)
            ->assertSee('themes/serena/css/theme.css', false);
    }

    public function test_la_previsualizacion_no_cambia_el_tema_activo(): void
    {
        $this->temas()->activar('base');

        $this->get(route('panel.temas.previsualizar', 'terra'))
            ->assertOk()
            ->assertSee('Todavia no lo has activado');

        $this->assertSame('base', $this->temas()->slugActivo());
    }

    public function test_la_previsualizacion_distingue_los_dos_formatos(): void
    {
        $this->get(route('panel.temas.previsualizar', ['slug' => 'nube', 'modo' => 'multipagina']))
            ->assertOk()
            ->assertSee('Cada seccion en su propia direccion')
            ->assertSee('/preguntas-frecuentes');

        $this->get(route('panel.temas.previsualizar', ['slug' => 'nube', 'modo' => 'landing']))
            ->assertOk()
            ->assertDontSee('Cada seccion en su propia direccion');
    }

    public function test_sin_modo_la_previsualizacion_usa_el_configurado(): void
    {
        $this->temas()->guardarModo('multipagina');

        $this->assertSame(
            'multipagina',
            $this->get(route('panel.temas.previsualizar', 'aurora'))->assertOk()->viewData('modo')
        );
    }

    public function test_la_previsualizacion_de_un_tema_inexistente_da_404(): void
    {
        $this->get(route('panel.temas.previsualizar', 'inventado'))->assertNotFound();
    }

    public function test_la_previsualizacion_aguanta_una_web_vacia(): void
    {
        $this->get(route('panel.temas.previsualizar', 'base'))
            ->assertOk()
            ->assertSee('Tu nombre');
    }
}
