<?php

namespace Tests\Feature;

use App\Http\Middleware\VerificarInstalacion;
use App\Models\Articulo;
use App\Models\Categoria;
use App\Models\PerfilPublico;
use App\Models\RedSocial;
use App\Services\BlogService;
use App\Services\ModuloService;
use App\Services\TemaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogPublicoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(VerificarInstalacion::class);

        PerfilPublico::actual()->update(['nombre_publico' => 'Ana Ruiz']);

        app(TemaService::class)->guardarModo('multipagina');
    }

    private function categoria(string $nombre): Categoria
    {
        return Categoria::create([
            'nombre' => $nombre,
            'slug' => Categoria::generarSlug($nombre),
            'orden' => 1,
        ]);
    }

    private function articulo(array $datos = []): Articulo
    {
        $titulo = $datos['titulo'] ?? 'Como dormir mejor';

        return Articulo::create(array_merge([
            'titulo' => $titulo,
            'slug' => Articulo::generarSlug($titulo),
            'extracto' => 'Cinco habitos sencillos para descansar.',
            'contenido' => '<p>Contenido del articulo.</p>',
            'publicado' => true,
            'publicado_at' => now()->subDay(),
        ], $datos));
    }

    /* ---------- Listado ---------- */

    public function test_el_listado_solo_muestra_articulos_publicados(): void
    {
        $this->articulo(['titulo' => 'Publicado de verdad']);
        $this->articulo(['titulo' => 'Un borrador', 'publicado' => false]);
        $this->articulo(['titulo' => 'Programado para manana', 'publicado_at' => now()->addWeek()]);

        $this->get(route('publico.blog'))
            ->assertOk()
            ->assertSee('Publicado de verdad')
            ->assertDontSee('Un borrador')
            ->assertDontSee('Programado para manana');
    }

    public function test_el_listado_pagina_los_articulos(): void
    {
        for ($i = 1; $i <= BlogService::POR_PAGINA + 2; $i++) {
            $this->articulo([
                'titulo' => 'Articulo numero ' . $i,
                'publicado_at' => now()->subDays($i),
            ]);
        }

        $primera = $this->get(route('publico.blog'))->assertOk();

        $primera->assertSee('Articulo numero 1')
            ->assertDontSee('Articulo numero ' . (BlogService::POR_PAGINA + 1))
            ->assertSee('page=2', false);

        $this->get(route('publico.blog', ['page' => 2]))
            ->assertOk()
            ->assertSee('Articulo numero ' . (BlogService::POR_PAGINA + 1));
    }

    public function test_un_articulo_programado_aparece_cuando_llega_su_fecha(): void
    {
        $articulo = $this->articulo(['titulo' => 'Sale luego', 'publicado_at' => now()->addHour()]);

        $this->get(route('publico.blog'))->assertDontSee('Sale luego');

        $articulo->update(['publicado_at' => now()->subMinute()]);

        $this->get(route('publico.blog'))->assertSee('Sale luego');
    }

    public function test_el_listado_vacio_muestra_un_mensaje_amable(): void
    {
        $this->get(route('publico.blog'))
            ->assertOk()
            ->assertSee('Aun no hay articulos publicados');
    }

    /* ---------- Categorias ---------- */

    public function test_se_puede_filtrar_por_categoria(): void
    {
        $ansiedad = $this->categoria('Ansiedad');
        $sueno = $this->categoria('Sueno');

        $this->articulo(['titulo' => 'Manejar la ansiedad', 'categoria_id' => $ansiedad->id]);
        $this->articulo(['titulo' => 'Higiene del sueno', 'categoria_id' => $sueno->id]);

        $this->get(route('publico.blog.categoria', $ansiedad->slug))
            ->assertOk()
            ->assertSee('Manejar la ansiedad')
            ->assertDontSee('Higiene del sueno');
    }

    public function test_solo_se_ofrecen_categorias_que_tienen_algo_publicado(): void
    {
        $conArticulos = $this->categoria('Ansiedad');
        $this->categoria('Categoria vacia');

        $this->articulo(['categoria_id' => $conArticulos->id]);

        $this->get(route('publico.blog'))
            ->assertOk()
            ->assertSee('Ansiedad')
            ->assertDontSee('Categoria vacia');
    }

    public function test_una_categoria_sin_articulos_visibles_da_su_propio_mensaje(): void
    {
        $categoria = $this->categoria('Mindfulness');
        $this->articulo(['categoria_id' => $categoria->id, 'publicado' => false]);

        $this->get(route('publico.blog.categoria', $categoria->slug))
            ->assertOk()
            ->assertSee('Aun no hay articulos en esta categoria');
    }

    /* ---------- Articulo ---------- */

    public function test_el_articulo_muestra_su_contenido_completo(): void
    {
        $articulo = $this->articulo(['contenido' => '<p>El cuerpo entero del articulo.</p>']);

        $this->get(route('publico.blog.articulo', $articulo->slug))
            ->assertOk()
            ->assertSee('Como dormir mejor')
            ->assertSee('El cuerpo entero del articulo.', false);
    }

    public function test_un_borrador_no_es_accesible_por_su_direccion(): void
    {
        $borrador = $this->articulo(['publicado' => false]);
        $programado = $this->articulo(['titulo' => 'Otro', 'publicado_at' => now()->addWeek()]);

        $this->get(route('publico.blog.articulo', $borrador->slug))->assertNotFound();
        $this->get(route('publico.blog.articulo', $programado->slug))->assertNotFound();
    }

    public function test_el_articulo_usa_su_propio_seo(): void
    {
        $articulo = $this->articulo([
            'meta_title' => 'Titulo pensado para Google',
            'meta_description' => 'Descripcion pensada para Google',
        ]);

        $this->get(route('publico.blog.articulo', $articulo->slug))
            ->assertOk()
            ->assertSee('<title>Titulo pensado para Google</title>', false)
            ->assertSee('Descripcion pensada para Google', false)
            ->assertSee('"@type":"BlogPosting"', false)
            ->assertSee('og:type" content="article', false);
    }

    public function test_las_visitas_se_cuentan_una_vez_por_sesion(): void
    {
        $articulo = $this->articulo();

        $this->get(route('publico.blog.articulo', $articulo->slug));
        $this->get(route('publico.blog.articulo', $articulo->slug));

        $this->assertSame(1, $articulo->fresh()->vistas);
    }

    public function test_un_articulo_sin_foto_usa_la_de_por_defecto(): void
    {
        $articulo = $this->articulo(['imagen' => null]);

        $porDefecto = imagen('blog_defecto');

        $this->get(route('publico.blog.articulo', $articulo->slug))
            ->assertOk()
            ->assertSee('articulo__portada', false)
            ->assertSee($porDefecto, false)
            ->assertSee('og:image" content="' . $porDefecto, false);
    }

    public function test_el_articulo_ofrece_botones_de_compartir(): void
    {
        $articulo = $this->articulo();

        $this->get(route('publico.blog.articulo', $articulo->slug))
            ->assertOk()
            ->assertSee('wa.me', false)
            ->assertSee('facebook.com/sharer', false)
            ->assertSee('linkedin.com/sharing', false);
    }

    public function test_los_relacionados_priorizan_la_misma_categoria(): void
    {
        $categoria = $this->categoria('Ansiedad');
        $articulo = $this->articulo(['titulo' => 'El principal', 'categoria_id' => $categoria->id]);

        $this->articulo(['titulo' => 'Hermano de categoria', 'categoria_id' => $categoria->id]);
        $this->articulo(['titulo' => 'De otro sitio']);

        $relacionados = app(BlogService::class)->relacionados($articulo);

        $this->assertSame('Hermano de categoria', $relacionados->first()->titulo);
        $this->assertFalse($relacionados->contains('id', $articulo->id));
    }

    public function test_los_relacionados_se_completan_con_los_mas_recientes(): void
    {
        $articulo = $this->articulo(['titulo' => 'El principal']);

        $this->articulo(['titulo' => 'Otro cualquiera']);

        $relacionados = app(BlogService::class)->relacionados($articulo);

        $this->assertCount(1, $relacionados);
        $this->assertSame('Otro cualquiera', $relacionados->first()->titulo);
    }

    /* ---------- SEO ---------- */

    public function test_el_sitemap_incluye_los_articulos_publicados(): void
    {
        $visible = $this->articulo(['titulo' => 'Sale en el sitemap']);
        $borrador = $this->articulo(['titulo' => 'No sale', 'publicado' => false]);

        $this->get(route('publico.sitemap'))
            ->assertOk()
            ->assertSee(route('publico.blog.articulo', $visible->slug), false)
            ->assertDontSee(route('publico.blog.articulo', $borrador->slug), false);
    }

    /* ---------- Redes en el pie ---------- */

    public function test_el_pie_muestra_las_redes_activas_con_url(): void
    {
        RedSocial::create(['red' => 'instagram', 'url' => 'https://instagram.com/ana', 'activa' => true, 'orden' => 1]);
        RedSocial::create(['red' => 'facebook', 'url' => 'https://facebook.com/ana', 'activa' => false, 'orden' => 2]);
        RedSocial::create(['red' => 'linkedin', 'url' => null, 'activa' => true, 'orden' => 3]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('https://instagram.com/ana', false)
            ->assertDontSee('https://facebook.com/ana', false)
            ->assertSee('fa-instagram', false);
    }

    public function test_con_el_modulo_de_redes_apagado_no_se_pintan(): void
    {
        RedSocial::create(['red' => 'instagram', 'url' => 'https://instagram.com/ana', 'activa' => true, 'orden' => 1]);

        $modulos = app(ModuloService::class);
        $modulos->guardar(array_diff($modulos->claves(), ['redes']));

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('https://instagram.com/ana', false);
    }

    /* ---------- Modulo apagado ---------- */

    public function test_con_el_blog_apagado_todo_el_blog_da_404(): void
    {
        $categoria = $this->categoria('Ansiedad');
        $articulo = $this->articulo(['categoria_id' => $categoria->id]);

        $modulos = app(ModuloService::class);
        $modulos->guardar(array_diff($modulos->claves(), ['blog']));

        $this->get(route('publico.blog'))->assertNotFound();
        $this->get(route('publico.blog.categoria', $categoria->slug))->assertNotFound();
        $this->get(route('publico.blog.articulo', $articulo->slug))->assertNotFound();

        $this->get(route('publico.sitemap'))
            ->assertOk()
            ->assertDontSee(route('publico.blog.articulo', $articulo->slug), false);
    }

    /* ---------- Landing ---------- */

    public function test_en_landing_el_blog_sigue_siendo_pagina_propia(): void
    {
        app(TemaService::class)->guardarModo('landing');

        $articulo = $this->articulo();

        $this->get(route('publico.blog'))->assertOk()->assertSee('Como dormir mejor');
        $this->get(route('publico.blog.articulo', $articulo->slug))->assertOk();
    }

    public function test_las_tarjetas_de_la_portada_enlazan_al_articulo(): void
    {
        $articulo = $this->articulo();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee(route('publico.blog.articulo', $articulo->slug), false);
    }
}
