<?php

namespace Tests\Feature;

use App\Http\Middleware\VerificarInstalacion;
use App\Models\Articulo;
use App\Models\Especialidad;
use App\Models\Faq;
use App\Models\PerfilPublico;
use App\Models\Plan;
use App\Models\Psicologa;
use App\Models\RedSocial;
use App\Models\Servicio;
use App\Services\ModuloService;
use App\Services\TemaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(VerificarInstalacion::class);

        PerfilPublico::actual()->update([
            'nombre_publico' => 'Ana Ruiz',
            'eslogan' => 'Un espacio seguro para volver a encontrarte',
            'num_colegiado' => 'M-1234',
            'telefono_citas' => '600 11 22 33',
            'email_citas' => 'citas@ejemplo.test',
            'sobre_mi' => '<p>Llevo diez anos acompanando procesos.</p>',
            'direccion' => 'Calle Mayor 12, Madrid',
            'horarios' => 'Lunes a viernes de 9 a 20',
        ]);

        Servicio::create(['titulo' => 'Terapia individual', 'orden' => 1, 'activo' => true]);
        Especialidad::create(['nombre' => 'Ansiedad', 'orden' => 1, 'activo' => true]);
        Plan::create(['nombre' => 'Sesion online', 'modalidad' => 'online', 'precio' => 55, 'orden' => 1]);
        Faq::create(['pregunta' => '¿Cuanto dura una sesion?', 'respuesta' => '<p>50 minutos.</p>', 'orden' => 1, 'activa' => true]);

        Articulo::create([
            'titulo' => 'Como manejar la ansiedad',
            'slug' => 'como-manejar-la-ansiedad',
            'contenido' => '<p>Texto</p>',
            'publicado' => true,
            'publicado_at' => now()->subDay(),
        ]);
    }

    private function temas(): TemaService
    {
        return app(TemaService::class);
    }

    private function modulos(): ModuloService
    {
        return app(ModuloService::class);
    }

    private function enMultipagina(): void
    {
        $this->temas()->guardarModo('multipagina');
    }

    /* ---------- La portada ---------- */

    public function test_la_portada_se_ve_sin_estar_autenticado(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Ana Ruiz')
            ->assertSee('Un espacio seguro para volver a encontrarte')
            ->assertSee('Terapia individual')
            ->assertSee('Ansiedad');
    }

    public function test_la_portada_lleva_los_datos_de_contacto(): void
    {
        $respuesta = $this->get('/')->assertOk();

        $respuesta->assertSee('tel:34600112233', false)
            ->assertSee('https://wa.me/34600112233', false)
            ->assertSee('mailto:citas@ejemplo.test', false);
    }

    public function test_la_portada_no_ensena_una_seccion_vacia(): void
    {
        Servicio::query()->delete();

        $this->get('/')->assertOk()->assertDontSee('titulo-servicios', false);
    }

    /* ---------- SEO ---------- */

    public function test_el_head_trae_el_seo_completo(): void
    {
        $respuesta = $this->get('/')->assertOk();

        $respuesta->assertSee('<title>', false)
            ->assertSee('name="description"', false)
            ->assertSee('rel="canonical"', false)
            ->assertSee('property="og:title"', false)
            ->assertSee('property="og:image"', false)
            ->assertSee('name="twitter:card"', false)
            ->assertSee('application/ld+json', false)
            ->assertSee('"@type":"Psychologist"', false)
            ->assertSee('<html lang="es"', false);
    }

    public function test_cada_pagina_puede_tener_su_propio_seo(): void
    {
        $this->enMultipagina();

        app(\App\Services\ContenidoWebService::class)->guardarSeo([
            'servicios' => ['title' => 'Mis servicios de psicologia', 'description' => 'Lo que ofrezco.'],
        ]);

        $this->get('/servicios')
            ->assertOk()
            ->assertSee('<title>Mis servicios de psicologia</title>', false)
            ->assertSee('Lo que ofrezco.', false);
    }

    public function test_el_sitemap_y_el_robots_responden(): void
    {
        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml')
            ->assertSee('<urlset', false)
            ->assertSee(route('home'), false);

        $this->get('/robots.txt')
            ->assertOk()
            ->assertSee('Disallow: /panel-psicologa')
            ->assertSee('Sitemap: ' . route('publico.sitemap'));
    }

    public function test_el_sitemap_lista_las_paginas_en_modo_multipagina(): void
    {
        $this->enMultipagina();

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertSee(route('publico.sobre-mi'), false)
            ->assertSee(route('publico.servicios'), false);
    }

    /* ---------- Los dos modos ---------- */

    public function test_en_landing_todo_vive_en_la_portada(): void
    {
        $this->temas()->guardarModo('landing');

        $this->get('/')
            ->assertOk()
            ->assertSee('id="sobre-mi"', false)
            ->assertSee('id="servicios"', false)
            ->assertSee('id="precios"', false)
            ->assertSee('id="preguntas-frecuentes"', false)
            ->assertSee('id="pide-cita"', false);

        // Las direcciones de seccion llevan a su ancla de la portada.
        $this->get('/sobre-mi')->assertRedirect(route('home') . '#sobre-mi');
        $this->get('/servicios')->assertRedirect(route('home') . '#servicios');
        $this->get('/preguntas-frecuentes')->assertRedirect(route('home') . '#preguntas-frecuentes');
    }

    public function test_en_multipagina_cada_seccion_tiene_su_direccion(): void
    {
        $this->enMultipagina();

        $this->get('/sobre-mi')->assertOk()->assertSee('Llevo diez anos acompanando procesos.', false);
        $this->get('/servicios')->assertOk()->assertSee('Terapia individual');
        $this->get('/preguntas-frecuentes')->assertOk()->assertSee('¿Cuanto dura una sesion?', false);
        $this->get('/blog')->assertOk()->assertSee('Como manejar la ansiedad');
        $this->get('/pide-cita')->assertOk()->assertSee('Donde estamos');
    }

    public function test_el_menu_cambia_segun_el_modo(): void
    {
        $this->temas()->guardarModo('landing');
        $this->get('/')->assertOk()->assertSee(route('home') . '#servicios', false);

        $this->enMultipagina();
        $this->get('/')->assertOk()->assertSee(route('publico.servicios'), false);
    }

    /* ---------- Modulos ---------- */

    public function test_un_modulo_apagado_cierra_su_pagina_y_la_quita_del_menu(): void
    {
        $this->enMultipagina();

        $this->get('/blog')->assertOk();

        $this->modulos()->guardar(array_diff($this->modulos()->claves(), ['blog']));

        $this->get('/blog')->assertNotFound();
        $this->get('/')->assertOk()->assertDontSee(route('publico.blog'), false);
    }

    public function test_con_las_reservas_apagadas_no_hay_boton_de_pedir_cita(): void
    {
        $this->modulos()->guardar(array_diff($this->modulos()->claves(), ['reservas']));

        $this->get('/')->assertOk()->assertDontSee('Pedir cita');
        $this->get('/pide-cita')->assertNotFound();
    }

    public function test_los_planes_apagados_no_se_publican(): void
    {
        $this->temas()->guardarModo('landing');

        $this->get('/')->assertOk()->assertSee('Sesion online');

        $this->modulos()->guardar(array_diff($this->modulos()->claves(), ['planes']));

        $this->get('/')->assertOk()->assertDontSee('Sesion online');
    }

    /* ---------- Redes sociales ---------- */

    public function test_las_redes_visibles_salen_en_el_pie(): void
    {
        app(\App\Services\RedSocialService::class)->sincronizar();

        RedSocial::where('red', 'instagram')->update([
            'url' => 'https://instagram.com/ana',
            'activa' => true,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('https://instagram.com/ana', false)
            ->assertSee('fa-instagram', false);
    }

    /* ---------- Textos e imagenes gestionables ---------- */

    public function test_los_textos_salen_de_las_frases_publicas(): void
    {
        \App\Models\FrasePublica::create([
            'clave' => 'servicios_titulo',
            'seccion' => 'servicios',
            'texto_defecto' => 'Como puedo ayudarte',
        ]);

        $this->get('/')->assertOk()->assertSee('Como puedo ayudarte');

        app(\App\Services\FraseService::class)->guardar(['servicios_titulo' => 'En que te acompano']);

        $this->get('/')->assertOk()->assertSee('En que te acompano')->assertDontSee('Como puedo ayudarte');
    }

    public function test_las_imagenes_salen_del_gestor_de_imagenes(): void
    {
        app(\App\Services\ImagenService::class)->sincronizar();

        $this->get('/')->assertOk()->assertSee('themes/base/img/shape1.png', false);
    }

    /* ---------- Previsualizacion de temas (deuda de FASE 12) ---------- */

    public function test_la_previsualizacion_usa_las_vistas_publicas_reales(): void
    {
        $psicologa = Psicologa::create([
            'nombre' => 'Ana',
            'apellidos' => 'Ruiz',
            'email' => 'ana@ejemplo.test',
            'telefono' => '600111000',
            'password' => 'secreto-largo',
        ]);

        $respuesta = $this->actingAs($psicologa)
            ->get(route('panel.temas.previsualizar', 'serena'))
            ->assertOk();

        // La misma portada de la web, con la barra de aviso encima.
        $respuesta->assertSee('aviso-previa', false)
            ->assertSee('themes/serena/css/theme.css', false)
            ->assertSee('Terapia individual')
            ->assertSee('Un espacio seguro para volver a encontrarte');

        // Y no ha cambiado el tema activo.
        $this->assertSame(TemaService::TEMA_POR_DEFECTO, $this->temas()->slugActivo());
    }

    public function test_la_previsualizacion_respeta_el_modo_pedido(): void
    {
        $psicologa = Psicologa::create([
            'nombre' => 'Ana',
            'apellidos' => 'Ruiz',
            'email' => 'ana@ejemplo.test',
            'telefono' => '600111000',
            'password' => 'secreto-largo',
        ]);

        $this->temas()->guardarModo('landing');

        // Aunque la web este en landing, se puede ver como quedaria en multipagina.
        $this->actingAs($psicologa)
            ->get(route('panel.temas.previsualizar', ['slug' => 'nube', 'modo' => 'multipagina']))
            ->assertOk()
            ->assertSee(route('publico.servicios'), false);

        $this->assertSame('landing', $this->temas()->modoActivo());
    }
}
