<?php

namespace Tests\Feature;

use App\Http\Middleware\VerificarInstalacion;
use App\Models\Articulo;
use App\Models\Categoria;
use App\Models\Psicologa;
use App\Services\SanitizadorHtml;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BlogTest extends TestCase
{
    use RefreshDatabase;

    private Categoria $categoria;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(VerificarInstalacion::class);

        $this->categoria = Categoria::create([
            'nombre' => 'Ansiedad',
            'slug' => 'ansiedad',
            'orden' => 0,
        ]);

        $this->actingAs(Psicologa::create([
            'nombre' => 'Ana',
            'apellidos' => 'Ruiz',
            'email' => 'ana@ejemplo.test',
            'telefono' => '600111000',
            'password' => 'secreto-largo',
        ]));
    }

    private function datosArticulo(array $extra = []): array
    {
        return array_merge([
            'titulo' => 'Como manejar la ansiedad',
            'slug' => '',
            'categoria_id' => $this->categoria->id,
            'extracto' => 'Una guia practica.',
            'contenido' => '<p>Contenido del articulo.</p>',
            'estado' => 'publicado',
            'publicado_at' => '',
            'meta_title' => '',
            'meta_description' => '',
        ], $extra);
    }

    public function test_el_blog_requiere_autenticacion(): void
    {
        Auth::logout();

        $this->get('/panel-psicologa/blog')->assertRedirect('/acceso-psicologa');
        $this->get('/panel-psicologa/categorias')->assertRedirect('/acceso-psicologa');
    }

    public function test_las_pantallas_del_blog_se_muestran(): void
    {
        $this->get('/panel-psicologa/blog')->assertOk()->assertSee('Gestion de articulos');
        $this->get('/panel-psicologa/blog/nuevo')->assertOk()->assertSee('vendor/jodit/jodit.min.js');
        $this->get('/panel-psicologa/categorias')->assertOk()->assertSee('Ansiedad');
    }

    public function test_crear_un_articulo_genera_el_slug_y_lo_publica(): void
    {
        $this->post('/panel-psicologa/blog', $this->datosArticulo())
            ->assertRedirect();

        $articulo = Articulo::first();

        $this->assertSame('como-manejar-la-ansiedad', $articulo->slug);
        $this->assertTrue($articulo->publicado);
        $this->assertNotNull($articulo->publicado_at);
        $this->assertSame('publicado', $articulo->estado);
    }

    public function test_los_slugs_repetidos_se_numeran(): void
    {
        $this->post('/panel-psicologa/blog', $this->datosArticulo());
        $this->post('/panel-psicologa/blog', $this->datosArticulo());

        $this->assertSame(
            ['como-manejar-la-ansiedad', 'como-manejar-la-ansiedad-2'],
            Articulo::orderBy('id')->pluck('slug')->all()
        );
    }

    public function test_el_contenido_se_sanitiza_antes_de_guardar(): void
    {
        $this->post('/panel-psicologa/blog', $this->datosArticulo([
            'contenido' => '<p onclick="robar()">Hola</p><script>alert(1)</script><a href="javascript:mal()">enlace</a>',
        ]));

        $contenido = Articulo::first()->contenido;

        $this->assertStringNotContainsString('<script', $contenido);
        $this->assertStringNotContainsString('onclick', $contenido);
        $this->assertStringNotContainsString('javascript:', $contenido);
        $this->assertStringContainsString('Hola', $contenido);
    }

    public function test_una_fecha_futura_deja_el_articulo_programado(): void
    {
        $this->post('/panel-psicologa/blog', $this->datosArticulo([
            'publicado_at' => Carbon::tomorrow()->setTime(9, 0)->toDateTimeString(),
        ]));

        $articulo = Articulo::first();

        $this->assertSame('programado', $articulo->estado);
        $this->assertSame(0, Articulo::visibles()->count());
    }

    public function test_un_borrador_no_es_visible(): void
    {
        $this->post('/panel-psicologa/blog', $this->datosArticulo(['estado' => 'borrador']));

        $this->assertSame('borrador', Articulo::first()->estado);
        $this->assertSame(0, Articulo::visibles()->count());
    }

    public function test_la_imagen_destacada_se_guarda_y_se_borra_con_el_articulo(): void
    {
        Storage::fake('public');

        $this->post('/panel-psicologa/blog', $this->datosArticulo([
            'imagen' => UploadedFile::fake()->create('portada.jpg', 120, 'image/jpeg'),
        ]));

        $articulo = Articulo::first();

        $this->assertNotNull($articulo->imagen);
        Storage::disk('public')->assertExists($articulo->imagen);

        $this->delete("/panel-psicologa/blog/{$articulo->id}")->assertRedirect();

        Storage::disk('public')->assertMissing($articulo->imagen);
        $this->assertDatabaseMissing('articulos', ['id' => $articulo->id]);
    }

    public function test_editar_un_articulo_conserva_su_imagen(): void
    {
        Storage::fake('public');

        $this->post('/panel-psicologa/blog', $this->datosArticulo([
            'imagen' => UploadedFile::fake()->create('portada.jpg', 120, 'image/jpeg'),
        ]));

        $articulo = Articulo::first();
        $imagen = $articulo->imagen;

        $this->put("/panel-psicologa/blog/{$articulo->id}", $this->datosArticulo([
            'titulo' => 'Titulo cambiado',
            'slug' => $articulo->slug,
        ]))->assertRedirect();

        $this->assertSame($imagen, $articulo->fresh()->imagen);
        $this->assertSame('Titulo cambiado', $articulo->fresh()->titulo);
    }

    public function test_la_vista_previa_muestra_el_articulo(): void
    {
        $this->post('/panel-psicologa/blog', $this->datosArticulo([
            'contenido' => '<h2>Un titular</h2><p>El cuerpo del articulo.</p>',
        ]));

        $articulo = Articulo::first();

        $this->get("/panel-psicologa/blog/{$articulo->id}/previsualizar")
            ->assertOk()
            ->assertSee('Como manejar la ansiedad')
            ->assertSee('<h2>Un titular</h2>', false)
            ->assertSee('Una guia practica.')
            ->assertSee('Asi se veria en Google');
    }

    public function test_el_boton_de_vista_previa_guarda_antes_de_mostrarla(): void
    {
        $this->post('/panel-psicologa/blog', $this->datosArticulo());
        $articulo = Articulo::first();

        $this->put("/panel-psicologa/blog/{$articulo->id}", $this->datosArticulo([
            'slug' => $articulo->slug,
            'contenido' => '<p>Texto recien escrito y sin guardar.</p>',
            'accion' => 'previsualizar',
        ]))->assertRedirect(route('panel.blog.previsualizar', $articulo));

        $this->assertStringContainsString('Texto recien escrito', $articulo->fresh()->contenido);
    }

    public function test_la_vista_previa_vuelve_a_la_edicion_del_articulo(): void
    {
        $this->post('/panel-psicologa/blog', $this->datosArticulo());
        $articulo = Articulo::first();

        $this->get("/panel-psicologa/blog/{$articulo->id}/previsualizar")
            ->assertOk()
            ->assertSee(route('panel.blog.edit', $articulo), false);
    }

    public function test_la_vista_previa_avisa_de_que_es_un_borrador(): void
    {
        $this->post('/panel-psicologa/blog', $this->datosArticulo(['estado' => 'borrador']));

        $this->get('/panel-psicologa/blog/' . Articulo::first()->id . '/previsualizar')
            ->assertOk()
            ->assertSee('Asi se vera cuando lo publiques')
            ->assertSee('todavia no es visible en tu web');
    }

    public function test_la_vista_previa_requiere_autenticacion(): void
    {
        $this->post('/panel-psicologa/blog', $this->datosArticulo());
        $articulo = Articulo::first();

        Auth::logout();

        $this->get("/panel-psicologa/blog/{$articulo->id}/previsualizar")
            ->assertRedirect('/acceso-psicologa');
    }

    public function test_el_articulo_necesita_titulo(): void
    {
        $this->post('/panel-psicologa/blog', $this->datosArticulo(['titulo' => '']))
            ->assertSessionHasErrors('titulo');

        $this->assertSame(0, Articulo::count());
    }

    public function test_crear_y_borrar_categorias(): void
    {
        $this->post('/panel-psicologa/categorias', ['nombre' => 'Mindfulness', 'orden' => 3])
            ->assertRedirect();

        $categoria = Categoria::where('slug', 'mindfulness')->firstOrFail();

        $this->delete("/panel-psicologa/categorias/{$categoria->id}")->assertRedirect();

        $this->assertDatabaseMissing('categorias', ['id' => $categoria->id]);
    }

    public function test_borrar_una_categoria_deja_sus_articulos_sin_categoria(): void
    {
        $this->post('/panel-psicologa/blog', $this->datosArticulo());

        $this->delete("/panel-psicologa/categorias/{$this->categoria->id}")->assertRedirect();

        $articulo = Articulo::first();

        $this->assertNotNull($articulo);
        $this->assertNull($articulo->categoria_id);
    }

    public function test_el_sanitizador_conserva_el_formato_permitido(): void
    {
        $limpio = app(SanitizadorHtml::class)->limpiar(
            '<h2>Titulo</h2><p><strong>Negrita</strong> y <em>cursiva</em></p><ul><li>Uno</li></ul>'
            . '<a href="https://ejemplo.test" target="_blank">enlace</a>'
        );

        $this->assertStringContainsString('<h2>Titulo</h2>', $limpio);
        $this->assertStringContainsString('<strong>Negrita</strong>', $limpio);
        $this->assertStringContainsString('<li>Uno</li>', $limpio);
        $this->assertStringContainsString('rel="noopener noreferrer"', $limpio);
    }
}
