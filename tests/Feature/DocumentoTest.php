<?php

namespace Tests\Feature;

use App\Http\Middleware\VerificarInstalacion;
use App\Models\Paciente;
use App\Models\PerfilPublico;
use App\Models\PlantillaDocumento;
use App\Models\Psicologa;
use App\Services\DocumentoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class DocumentoTest extends TestCase
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

        PerfilPublico::actual()->update([
            'nombre_publico' => 'Ana Ruiz',
            'num_colegiado' => 'M-12345',
        ]);
    }

    private function crearPaciente(array $extra = []): Paciente
    {
        return Paciente::create(array_merge([
            'nombre' => 'Elena',
            'apellidos' => 'Martinez',
            'telefono' => '611223344',
            'dni' => '12345678Z',
            'estado' => 'activo',
        ], $extra));
    }

    private function servicio(): DocumentoService
    {
        return app(DocumentoService::class);
    }

    public function test_los_documentos_requieren_autenticacion(): void
    {
        $paciente = $this->crearPaciente();

        Auth::logout();

        $this->get('/panel-psicologa/documentos')->assertRedirect('/acceso-psicologa');
        $this->get(route('panel.documentos.plantilla'))->assertRedirect('/acceso-psicologa');
        $this->get(route('panel.pacientes.proteccion-datos', $paciente))->assertRedirect('/acceso-psicologa');
        $this->put(route('panel.documentos.update'), ['contenido' => 'x'])->assertRedirect('/acceso-psicologa');
    }

    public function test_la_pantalla_muestra_la_plantilla_y_sus_campos(): void
    {
        $this->get('/panel-psicologa/documentos')
            ->assertOk()
            ->assertSee('Plantilla de proteccion de datos')
            ->assertSee('{{paciente_nombre}}')
            ->assertSee('{{num_colegiado}}')
            ->assertSee('Descargar plantilla vacia');
    }

    public function test_todas_las_acciones_estan_en_la_cabecera(): void
    {
        $respuesta = $this->get('/panel-psicologa/documentos')->assertOk();

        // Las tres acciones viven arriba: no hay que bajar hasta el pie de la pagina.
        $respuesta->assertSee('Restaurar plantilla')
            ->assertSee('Descargar plantilla vacia')
            ->assertSee('Guardar plantilla')
            ->assertSee(route('panel.documentos.restaurar'), false)
            ->assertSee(route('panel.documentos.plantilla'), false);
    }

    public function test_la_plantilla_se_crea_sola_la_primera_vez(): void
    {
        $this->assertSame(0, PlantillaDocumento::count());

        $this->get('/panel-psicologa/documentos')->assertOk();

        $this->assertSame(1, PlantillaDocumento::count());
        $this->assertStringContainsString('{{paciente_nombre}}', PlantillaDocumento::first()->contenido);
    }

    public function test_guardar_la_plantilla_la_sanitiza(): void
    {
        $this->put(route('panel.documentos.update'), [
            'contenido' => '<h2 onclick="robar()">Consentimiento</h2><script>alert(1)</script><p>Texto</p>',
        ])->assertRedirect(route('panel.documentos'));

        $contenido = PlantillaDocumento::proteccionDatos()->contenido;

        $this->assertStringContainsString('<h2>Consentimiento</h2>', $contenido);
        $this->assertStringNotContainsString('onclick', $contenido);
        $this->assertStringNotContainsString('<script', $contenido);
    }

    public function test_el_titulo_del_documento_sobrevive_al_guardar(): void
    {
        // El h1 es el titulo del documento: si el sanitizador lo tira, la plantilla pierde su encabezado.
        $this->put(route('panel.documentos.update'), [
            'contenido' => PlantillaDocumento::contenidoPorDefecto(),
        ])->assertRedirect(route('panel.documentos'));

        $this->assertStringContainsString(
            '<h1>Consentimiento informado y proteccion de datos</h1>',
            PlantillaDocumento::proteccionDatos()->contenido
        );
    }

    public function test_no_se_puede_guardar_una_plantilla_vacia(): void
    {
        $this->put(route('panel.documentos.update'), ['contenido' => ''])
            ->assertSessionHasErrors('contenido');
    }

    public function test_se_puede_restaurar_la_plantilla_original(): void
    {
        $this->put(route('panel.documentos.update'), ['contenido' => '<p>La he destrozado</p>']);
        $this->assertStringContainsString('destrozado', PlantillaDocumento::proteccionDatos()->contenido);

        $this->put(route('panel.documentos.restaurar'))->assertRedirect(route('panel.documentos'));

        $this->assertSame(
            PlantillaDocumento::contenidoPorDefecto(),
            PlantillaDocumento::proteccionDatos()->contenido
        );
    }

    public function test_la_plantilla_vacia_se_descarga_como_pdf(): void
    {
        $respuesta = $this->get(route('panel.documentos.plantilla'))->assertOk();

        $this->assertSame('application/pdf', $respuesta->headers->get('content-type'));
        $this->assertStringContainsString('proteccion-datos-plantilla.pdf', $respuesta->headers->get('content-disposition'));
        $this->assertStringStartsWith('%PDF-', $respuesta->getContent());
    }

    public function test_el_pdf_del_paciente_se_descarga_con_su_nombre(): void
    {
        $paciente = $this->crearPaciente();

        $respuesta = $this->get(route('panel.pacientes.proteccion-datos', $paciente))->assertOk();

        $this->assertSame('application/pdf', $respuesta->headers->get('content-type'));
        $this->assertStringContainsString(
            'proteccion-datos-elena-martinez.pdf',
            $respuesta->headers->get('content-disposition')
        );
        $this->assertStringStartsWith('%PDF-', $respuesta->getContent());
    }

    public function test_el_documento_del_paciente_lleva_sus_datos_reales(): void
    {
        $paciente = $this->crearPaciente();

        $resuelto = $this->servicio()->resolver(PlantillaDocumento::contenidoPorDefecto(), $paciente);

        $this->assertStringContainsString('Elena Martinez', $resuelto);
        $this->assertStringContainsString('12345678Z', $resuelto);
        $this->assertStringContainsString('611223344', $resuelto);
        $this->assertStringContainsString('Ana Ruiz', $resuelto);
        $this->assertStringContainsString('M-12345', $resuelto);
    }

    private function maqueta(?Paciente $paciente): string
    {
        $servicio = $this->servicio();

        return view('panel.documentos.pdf', [
            'titulo' => 'Proteccion de datos',
            'contenido' => $servicio->resolver(PlantillaDocumento::contenidoPorDefecto(), $paciente),
            'profesional' => $servicio->nombreProfesional(),
            'colegiado' => $servicio->colegiado(),
        ])->render();
    }

    public function test_el_bloque_de_firmas_vive_en_la_plantilla_editable(): void
    {
        // Si estuviera fijo en la vista del PDF, la psicologa no podria cambiarlo.
        $plantilla = PlantillaDocumento::contenidoPorDefecto();

        $this->assertStringContainsString('Firma del paciente', $plantilla);
        $this->assertStringContainsString('Firma de la profesional', $plantilla);
        $this->assertStringContainsString('{{paciente_dni}}', $plantilla);
        $this->assertStringContainsString('{{num_colegiado}}', $plantilla);

        // Y sobrevive al sanitizador, que es por donde pasa al guardarla.
        $this->put(route('panel.documentos.update'), ['contenido' => $plantilla]);

        $guardada = PlantillaDocumento::proteccionDatos()->contenido;

        $this->assertStringContainsString('Firma de la profesional', $guardada);
        $this->assertStringContainsString('class="firmas__tabla"', $guardada);
    }

    public function test_el_documento_lleva_las_dos_firmas(): void
    {
        $html = $this->maqueta($this->crearPaciente());

        $this->assertStringContainsString('Firma del paciente', $html);
        $this->assertStringContainsString('Firma de la profesional', $html);

        // Cada firma va identificada debajo de su linea.
        $this->assertStringContainsString('Elena Martinez', $html);
        $this->assertStringContainsString('12345678Z', $html);
        $this->assertStringContainsString('Ana Ruiz', $html);
        $this->assertStringContainsString('M-12345', $html);
    }

    public function test_la_plantilla_en_blanco_deja_las_firmas_sin_rellenar(): void
    {
        $html = $this->maqueta(null);

        $this->assertStringContainsString('Firma del paciente', $html);
        $this->assertStringContainsString('Firma de la profesional', $html);
        $this->assertStringContainsString('________________', $html);
    }

    public function test_los_datos_de_firma_tambien_se_escapan(): void
    {
        $paciente = $this->crearPaciente(['nombre' => '<b>Elena</b>', 'apellidos' => null]);

        $this->assertStringNotContainsString('<b>Elena</b>', $this->maqueta($paciente));
    }

    public function test_no_queda_ningun_campo_sin_resolver(): void
    {
        $paciente = $this->crearPaciente();
        $plantilla = '';

        foreach ($this->servicio()->claves() as $clave) {
            $plantilla .= '<p>{{' . $clave . '}}</p>';
        }

        $this->assertStringNotContainsString('{{', $this->servicio()->resolver($plantilla, $paciente));
        $this->assertStringNotContainsString('{{', $this->servicio()->resolver($plantilla));
    }

    public function test_los_campos_vacios_dejan_una_linea_para_rellenar_a_mano(): void
    {
        $sinPaciente = $this->servicio()->resolver('<p>{{paciente_nombre}}</p>');

        $this->assertStringContainsString('________________', $sinPaciente);

        // Un paciente sin DNI tambien deja el hueco, en vez de un espacio en blanco confuso.
        $conHueco = $this->servicio()->resolver('<p>{{paciente_dni}}</p>', $this->crearPaciente(['dni' => null]));

        $this->assertStringContainsString('________________', $conHueco);
    }

    public function test_los_datos_del_paciente_se_escapan_en_el_documento(): void
    {
        $paciente = $this->crearPaciente(['nombre' => '<script>alert(1)</script>', 'apellidos' => null]);

        $resuelto = $this->servicio()->resolver('<p>{{paciente_nombre}}</p>', $paciente);

        $this->assertStringNotContainsString('<script>', $resuelto);
        $this->assertStringContainsString('&lt;script&gt;', $resuelto);
    }

    public function test_la_ficha_del_paciente_ofrece_su_documento(): void
    {
        $paciente = $this->crearPaciente();

        $this->get(route('panel.pacientes.show', $paciente))
            ->assertOk()
            ->assertSee('Proteccion de datos')
            ->assertSee(route('panel.pacientes.proteccion-datos', $paciente), false);
    }
}
