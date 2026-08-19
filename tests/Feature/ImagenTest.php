<?php

namespace Tests\Feature;

use App\Http\Middleware\VerificarInstalacion;
use App\Models\Imagen;
use App\Models\Psicologa;
use App\Services\ImagenService;
use App\Services\TemaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImagenTest extends TestCase
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

        $this->servicio()->sincronizar();
    }

    private function servicio(): ImagenService
    {
        return app(ImagenService::class);
    }

    private function slot(string $clave = 'hero_principal'): Imagen
    {
        return Imagen::where('clave_slot', $clave)->firstOrFail();
    }

    private function archivo(string $nombre = 'foto.jpg'): UploadedFile
    {
        return UploadedFile::fake()->create($nombre, 120, 'image/jpeg');
    }

    /* ---------- Proteccion ---------- */

    public function test_las_imagenes_requieren_autenticacion(): void
    {
        $slot = $this->slot();

        Auth::logout();

        $this->get('/panel-psicologa/imagenes')->assertRedirect('/acceso-psicologa');
        $this->post(route('panel.imagenes.update', $slot))->assertRedirect('/acceso-psicologa');
        $this->delete(route('panel.imagenes.restaurar', $slot))->assertRedirect('/acceso-psicologa');
    }

    /* ---------- Catalogo ---------- */

    public function test_el_catalogo_crea_sus_huecos_solo(): void
    {
        Imagen::query()->delete();

        $this->get('/panel-psicologa/imagenes')->assertOk();

        $this->assertSame(count($this->servicio()->claves()), Imagen::count());
    }

    public function test_sincronizar_dos_veces_no_duplica_nada(): void
    {
        $antes = Imagen::count();

        $this->servicio()->sincronizar();

        $this->assertSame($antes, Imagen::count());
    }

    public function test_cada_hueco_apunta_a_una_imagen_de_stock_que_existe(): void
    {
        foreach ($this->servicio()->slots() as $clave => [$nombre, $stock, $descripcion]) {
            $this->assertFileExists(
                public_path('themes/base/img/' . $stock),
                "El hueco {$clave} apunta a una imagen de stock que no existe: {$stock}"
            );
        }
    }

    public function test_la_pantalla_agrupa_los_huecos_por_seccion(): void
    {
        $respuesta = $this->get('/panel-psicologa/imagenes')->assertOk();

        $this->assertSame(
            array_keys(ImagenService::CATALOGO),
            array_keys($respuesta->viewData('grupos'))
        );

        $respuesta->assertSee('Portada')->assertSee('Foto principal')->assertSee('Logotipo');
    }

    public function test_las_fotos_llenan_el_recuadro_y_los_png_se_centran(): void
    {
        $huecos = collect($this->servicio()->agrupadas())->flatten(1)->keyBy('clave');

        // PNG con el fondo recortado: se ven enteros sobre el fondo de cuadros.
        $this->assertTrue($huecos['hero_principal']['transparente']);
        $this->assertTrue($huecos['logo']['transparente']);

        // Fotos: llenan su recuadro, sin fondo a la vista.
        $this->assertFalse($huecos['sobre_mi']['transparente']);
        $this->assertFalse($huecos['terapia_1']['transparente']);
    }

    public function test_cada_hueco_ofrece_verse_a_tamano_completo(): void
    {
        $respuesta = $this->get('/panel-psicologa/imagenes')->assertOk();

        $respuesta->assertSee('id="modal-imagen"', false)
            ->assertSee('data-ver-imagen', false);

        $this->assertSame(
            count($this->servicio()->claves()),
            substr_count($respuesta->getContent(), 'data-ver-imagen=')
        );
    }

    /* ---------- Prioridad de la imagen subida ---------- */

    public function test_sin_subir_nada_se_usa_la_imagen_del_tema(): void
    {
        $this->assertStringContainsString('themes/base/img/psicologa.png', imagen('hero_principal'));
    }

    public function test_un_tema_sin_imagenes_propias_hereda_las_del_base(): void
    {
        app(TemaService::class)->activar('serena');
        $this->servicio()->limpiarCache();

        // Serena no trae carpeta img, asi que tira de las del tema del que hereda.
        $this->assertStringContainsString('themes/base/img/psicologa.png', imagen('hero_principal'));
    }

    public function test_la_imagen_subida_tiene_prioridad(): void
    {
        Storage::fake('public');

        $this->post(route('panel.imagenes.update', $this->slot()), [
            'imagen' => $this->archivo(),
            'alt' => 'Ana en su consulta',
        ])->assertSessionHasNoErrors();

        $slot = $this->slot();

        $this->assertNotNull($slot->ruta);
        Storage::disk('public')->assertExists($slot->ruta);

        $this->assertStringNotContainsString('themes/base', imagen('hero_principal'));
        $this->assertStringContainsString(ImagenService::CARPETA, imagen('hero_principal'));
        $this->assertSame('Ana en su consulta', imagen_alt('hero_principal'));
    }

    public function test_se_puede_guardar_solo_la_descripcion(): void
    {
        $this->post(route('panel.imagenes.update', $this->slot()), ['alt' => 'Solo el texto'])
            ->assertSessionHasNoErrors();

        $this->assertSame('Solo el texto', $this->slot()->alt);
        $this->assertNull($this->slot()->ruta);
    }

    public function test_subir_otra_imagen_borra_la_anterior_del_disco(): void
    {
        Storage::fake('public');

        $this->post(route('panel.imagenes.update', $this->slot()), ['imagen' => $this->archivo('uno.jpg')]);
        $primera = $this->slot()->ruta;

        $this->post(route('panel.imagenes.update', $this->slot()), ['imagen' => $this->archivo('dos.jpg')]);
        $segunda = $this->slot()->ruta;

        $this->assertNotSame($primera, $segunda);
        Storage::disk('public')->assertMissing($primera);
        Storage::disk('public')->assertExists($segunda);
    }

    /* ---------- Restaurar ---------- */

    public function test_restaurar_vuelve_a_la_imagen_del_tema(): void
    {
        Storage::fake('public');

        $this->post(route('panel.imagenes.update', $this->slot()), ['imagen' => $this->archivo()]);
        $ruta = $this->slot()->ruta;

        $this->delete(route('panel.imagenes.restaurar', $this->slot()))
            ->assertRedirect()
            ->assertSessionHas('exito');

        $this->assertNull($this->slot()->ruta);
        Storage::disk('public')->assertMissing($ruta);
        $this->assertStringContainsString('themes/base/img/psicologa.png', imagen('hero_principal'));
    }

    public function test_el_boton_de_restaurar_solo_aparece_si_hay_imagen_propia(): void
    {
        Storage::fake('public');

        $this->get('/panel-psicologa/imagenes')
            ->assertOk()
            ->assertDontSee('Volver a la original')
            ->assertSee('Estas usando la imagen del tema');

        $this->post(route('panel.imagenes.update', $this->slot()), ['imagen' => $this->archivo()]);

        $this->get('/panel-psicologa/imagenes')
            ->assertOk()
            ->assertSee('Volver a la original')
            ->assertSee(route('panel.imagenes.restaurar', $this->slot()), false);
    }

    public function test_restaurar_un_hueco_sin_imagen_propia_no_rompe_nada(): void
    {
        $this->delete(route('panel.imagenes.restaurar', $this->slot()))->assertRedirect();

        $this->assertNull($this->slot()->ruta);
    }

    /* ---------- Validacion ---------- */

    public function test_un_fichero_que_no_es_imagen_se_rechaza(): void
    {
        Storage::fake('public');

        $this->post(route('panel.imagenes.update', $this->slot()), [
            'imagen' => UploadedFile::fake()->create('virus.exe', 20, 'application/x-msdownload'),
        ])->assertSessionHasErrors('imagen');

        $this->assertNull($this->slot()->ruta);
    }

    public function test_una_imagen_demasiado_grande_se_rechaza(): void
    {
        Storage::fake('public');

        $this->post(route('panel.imagenes.update', $this->slot()), [
            'imagen' => UploadedFile::fake()->create('enorme.jpg', 5000, 'image/jpeg'),
        ])->assertSessionHasErrors('imagen');
    }

    public function test_un_hueco_fuera_del_catalogo_da_404(): void
    {
        $intruso = Imagen::create(['clave_slot' => 'inventado', 'alt' => 'x']);

        $this->post(route('panel.imagenes.update', $intruso), ['alt' => 'y'])->assertNotFound();
        $this->delete(route('panel.imagenes.restaurar', $intruso))->assertNotFound();
    }

    /* ---------- Helpers ---------- */

    public function test_el_helper_devuelve_null_para_un_hueco_que_no_existe(): void
    {
        $this->assertNull(imagen('no_existe'));
        $this->assertSame('', imagen_alt('no_existe'));
    }

    public function test_el_resumen_cuenta_las_personalizadas(): void
    {
        Storage::fake('public');

        $total = count($this->servicio()->claves());

        $this->assertSame(
            ['total' => $total, 'personalizadas' => 0, 'de_stock' => $total],
            $this->servicio()->resumen()
        );

        $this->post(route('panel.imagenes.update', $this->slot()), ['imagen' => $this->archivo()]);

        $this->assertSame(
            ['total' => $total, 'personalizadas' => 1, 'de_stock' => $total - 1],
            $this->servicio()->resumen()
        );
    }

    public function test_la_previsualizacion_del_tema_usa_las_imagenes_gestionadas(): void
    {
        Storage::fake('public');

        $this->post(route('panel.imagenes.update', $this->slot('hero_forma_1')), [
            'imagen' => $this->archivo('forma.jpg'),
        ]);

        $this->get(route('panel.temas.previsualizar', 'base'))
            ->assertOk()
            ->assertSee(ImagenService::CARPETA, false);
    }
}
