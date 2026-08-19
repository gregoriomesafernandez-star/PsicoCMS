<?php

namespace Tests\Feature;

use App\Http\Middleware\VerificarInstalacion;
use App\Models\Cita;
use App\Models\Historia;
use App\Models\HistoriaAdjunto;
use App\Models\Paciente;
use App\Models\Psicologa;
use App\Services\HistoriaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HistoriaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(VerificarInstalacion::class);
        Storage::fake(HistoriaService::DISCO);

        $this->actingAs(Psicologa::create([
            'nombre' => 'Ana',
            'apellidos' => 'Ruiz',
            'email' => 'ana@ejemplo.test',
            'telefono' => '600111000',
            'password' => 'secreto-largo',
        ]));
    }

    private function crearPaciente(array $extra = []): Paciente
    {
        return Paciente::create(array_merge([
            'nombre' => 'Elena',
            'apellidos' => 'Martinez',
            'telefono' => '611223344',
            'estado' => 'activo',
        ], $extra));
    }

    private function crearCita(Paciente $paciente): Cita
    {
        return Cita::create([
            'paciente_id' => $paciente->id,
            'modalidad' => 'online',
            'fecha' => now()->toDateString(),
            'hora_inicio' => '10:00:00',
            'hora_fin' => '10:50:00',
            'duracion' => 50,
            'estado' => 'completada',
            'origen' => 'manual',
        ]);
    }

    private function datosEntrada(Paciente $paciente, array $extra = []): array
    {
        return array_merge([
            'paciente_id' => $paciente->id,
            'fecha_sesion' => now()->toDateString(),
            'titulo' => 'Primera sesion',
            'contenido' => '<p>Hemos trabajado la respiracion.</p>',
        ], $extra);
    }

    private function pdf(string $nombre = 'anotaciones.pdf'): UploadedFile
    {
        return UploadedFile::fake()->create($nombre, 120, 'application/pdf');
    }

    private function imagen(string $nombre = 'pizarra.jpg'): UploadedFile
    {
        return UploadedFile::fake()->create($nombre, 90, 'image/jpeg');
    }

    public function test_las_historias_requieren_autenticacion(): void
    {
        Auth::logout();

        $this->get('/panel-psicologa/historias')->assertRedirect('/acceso-psicologa');
        $this->get('/panel-psicologa/historias/nueva')->assertRedirect('/acceso-psicologa');
    }

    public function test_los_adjuntos_no_son_accesibles_sin_sesion(): void
    {
        $historia = Historia::create($this->datosEntrada($this->crearPaciente()));

        $adjunto = HistoriaAdjunto::create([
            'historia_id' => $historia->id,
            'ruta' => 'historias/1/archivo.pdf',
            'nombre_original' => 'anotaciones.pdf',
            'mime' => 'application/pdf',
            'tamano' => 1024,
        ]);

        Auth::logout();

        $this->get(route('panel.historias.adjuntos.ver', $adjunto))->assertRedirect('/acceso-psicologa');
        $this->get(route('panel.historias.adjuntos.descargar', $adjunto))->assertRedirect('/acceso-psicologa');
    }

    public function test_no_existe_una_ruta_publica_sobre_el_disco_privado(): void
    {
        $this->assertFalse(
            Route::has('storage.local'),
            'El disco privado no puede exponer una ruta /storage/{path} sin autenticacion.'
        );

        $this->assertStringEndsWith('/storage/blog/foto.jpg', Storage::disk('public')->url('blog/foto.jpg'));
    }

    public function test_la_vista_general_lista_un_registro_por_paciente(): void
    {
        $elena = $this->crearPaciente();
        $luis = $this->crearPaciente(['telefono' => '699887766', 'nombre' => 'Luis', 'apellidos' => 'Vera']);

        Historia::create($this->datosEntrada($elena, ['titulo' => 'Primera de Elena']));
        Historia::create($this->datosEntrada($elena, [
            'titulo' => 'Segunda de Elena',
            'fecha_sesion' => now()->subWeek()->toDateString(),
        ]));
        Historia::create($this->datosEntrada($luis, ['titulo' => 'Unica de Luis']));

        $respuesta = $this->get('/panel-psicologa/historias')->assertOk();

        // Una fila por paciente, nunca los titulos de sus sesiones.
        $respuesta->assertSee('Elena Martinez')
            ->assertSee('Luis Vera')
            ->assertSee('Ver historia')
            ->assertDontSee('Primera de Elena')
            ->assertDontSee('Unica de Luis');

        $pacientes = $respuesta->viewData('pacientes');

        $this->assertCount(2, $pacientes);
        $this->assertSame(2, $pacientes->firstWhere('id', $elena->id)->historias_count);
        $this->assertSame(1, $pacientes->firstWhere('id', $luis->id)->historias_count);
        $this->assertSame(
            now()->toDateString(),
            substr($pacientes->firstWhere('id', $elena->id)->ultima_sesion, 0, 10)
        );
    }

    public function test_un_paciente_sin_historia_no_aparece_en_la_vista_general(): void
    {
        $this->crearPaciente(['telefono' => '699887766', 'nombre' => 'Luis', 'apellidos' => 'Vera']);

        $this->get('/panel-psicologa/historias')
            ->assertOk()
            ->assertDontSee('Luis Vera')
            ->assertSee('Todavia no has escrito ninguna sesion');
    }

    public function test_la_linea_de_tiempo_vive_en_la_ficha_del_paciente(): void
    {
        $paciente = $this->crearPaciente();
        Historia::create($this->datosEntrada($paciente));

        $this->get(route('panel.historias.index', ['paciente' => $paciente->id]))
            ->assertOk()
            ->assertViewIs('panel.historias.paciente')
            ->assertSee('Historia de Elena Martinez')
            ->assertSee('Linea de tiempo')
            ->assertSee('Primera sesion');
    }

    public function test_todas_las_pantallas_de_historias_se_muestran(): void
    {
        $paciente = $this->crearPaciente();
        $cita = $this->crearCita($paciente);

        $this->post(route('panel.historias.store'), $this->datosEntrada($paciente, [
            'cita_id' => $cita->id,
            'adjuntos' => [$this->pdf(), $this->imagen()],
        ]));

        $historia = Historia::first();

        $this->get(route('panel.historias.create'))->assertOk()->assertSee('Elige un paciente');

        $this->get(route('panel.historias.create', ['paciente' => $paciente->id, 'cita' => $cita->id]))
            ->assertOk()
            ->assertSee('Nueva entrada de sesion');

        $this->get(route('panel.historias.show', $historia))
            ->assertOk()
            ->assertSee('Primera sesion')
            ->assertSee('anotaciones.pdf')
            ->assertSee('pizarra.jpg');

        $this->get(route('panel.historias.edit', $historia))
            ->assertOk()
            ->assertSee('Editar entrada de sesion')
            ->assertSee('Quitar');
    }

    public function test_nueva_entrada_desde_un_paciente_llega_con_sus_datos_puestos(): void
    {
        $elena = $this->crearPaciente();
        $luis = $this->crearPaciente(['telefono' => '699887766', 'nombre' => 'Luis', 'apellidos' => 'Vera']);
        $cita = $this->crearCita($elena);

        $respuesta = $this->get(route('panel.historias.create', ['paciente' => $elena->id]))->assertOk();

        $historia = $respuesta->viewData('historia');

        $this->assertSame($elena->id, $historia->paciente_id);
        $this->assertSame(now()->toDateString(), $historia->fecha_sesion->toDateString());

        // El paciente llega ya elegido en el selector, no hay que buscarlo.
        $respuesta->assertSee('<option value="' . $elena->id . '" selected>', false)
            ->assertDontSee('<option value="' . $luis->id . '" selected>', false);

        // Y solo se ofrecen las citas de esa persona.
        $this->assertSame([$cita->id], array_keys($respuesta->viewData('citas')));
    }

    public function test_la_tabla_general_enlaza_a_nueva_entrada_de_cada_paciente(): void
    {
        $paciente = $this->crearPaciente();
        Historia::create($this->datosEntrada($paciente));

        $this->get('/panel-psicologa/historias')
            ->assertOk()
            ->assertSee(route('panel.historias.create', ['paciente' => $paciente->id]), false)
            ->assertSee('Nueva entrada');
    }

    public function test_se_crea_una_entrada_con_contenido_saneado(): void
    {
        $paciente = $this->crearPaciente();

        $respuesta = $this->post(route('panel.historias.store'), $this->datosEntrada($paciente, [
            'contenido' => '<p onclick="robar()">Sesion <strong>productiva</strong></p><script>alert(1)</script>',
        ]));

        $historia = Historia::first();

        $respuesta->assertRedirect(route('panel.historias.show', $historia));

        $this->assertStringContainsString('<strong>productiva</strong>', $historia->contenido);
        $this->assertStringNotContainsString('onclick', $historia->contenido);
        $this->assertStringNotContainsString('<script', $historia->contenido);
    }

    public function test_el_contenido_se_guarda_cifrado_en_la_base_de_datos(): void
    {
        $paciente = $this->crearPaciente();

        $this->post(route('panel.historias.store'), $this->datosEntrada($paciente, [
            'contenido' => '<p>Informacion clinica muy sensible.</p>',
        ]));

        $crudo = DB::table('historias')->value('contenido');

        $this->assertStringNotContainsString('muy sensible', $crudo);
        $this->assertSame('<p>Informacion clinica muy sensible.</p>', Historia::first()->contenido);
    }

    public function test_se_suben_adjuntos_de_imagen_y_pdf(): void
    {
        $paciente = $this->crearPaciente();

        $this->post(route('panel.historias.store'), $this->datosEntrada($paciente, [
            'adjuntos' => [$this->pdf(), $this->imagen()],
        ]));

        $historia = Historia::first();

        $this->assertCount(2, $historia->adjuntos);

        foreach ($historia->adjuntos as $adjunto) {
            $this->assertStringStartsWith('historias/' . $paciente->id . '/', $adjunto->ruta);
            Storage::disk(HistoriaService::DISCO)->assertExists($adjunto->ruta);
        }

        $this->assertNotSame(
            'anotaciones.pdf',
            basename($historia->adjuntos->first()->ruta),
            'El nombre del fichero en disco debe generarse, no venir del cliente.'
        );
    }

    public function test_se_rechaza_un_adjunto_de_tipo_no_permitido(): void
    {
        $paciente = $this->crearPaciente();

        $this->post(route('panel.historias.store'), $this->datosEntrada($paciente, [
            'adjuntos' => [UploadedFile::fake()->create('malicioso.exe', 10, 'application/octet-stream')],
        ]))->assertSessionHasErrors('adjuntos.0');

        $this->assertSame(0, Historia::count());
    }

    public function test_la_fecha_de_sesion_no_puede_ser_futura(): void
    {
        $paciente = $this->crearPaciente();

        $this->post(route('panel.historias.store'), $this->datosEntrada($paciente, [
            'fecha_sesion' => now()->addDay()->toDateString(),
        ]))->assertSessionHasErrors('fecha_sesion');
    }

    public function test_no_se_puede_vincular_una_cita_de_otro_paciente(): void
    {
        $paciente = $this->crearPaciente();
        $otro = $this->crearPaciente(['telefono' => '699887766', 'nombre' => 'Luis']);

        $this->post(route('panel.historias.store'), $this->datosEntrada($paciente, [
            'cita_id' => $this->crearCita($otro)->id,
        ]))->assertSessionHasErrors('cita_id');
    }

    public function test_se_vincula_una_cita_del_propio_paciente(): void
    {
        $paciente = $this->crearPaciente();
        $cita = $this->crearCita($paciente);

        $this->post(route('panel.historias.store'), $this->datosEntrada($paciente, [
            'cita_id' => $cita->id,
        ]))->assertSessionHasNoErrors();

        $this->assertSame($cita->id, Historia::first()->cita_id);
    }

    public function test_al_editar_se_pueden_quitar_adjuntos_y_anadir_otros(): void
    {
        $paciente = $this->crearPaciente();

        $this->post(route('panel.historias.store'), $this->datosEntrada($paciente, [
            'adjuntos' => [$this->pdf('vieja.pdf')],
        ]));

        $historia = Historia::first();
        $viejo = $historia->adjuntos->first();

        $this->put(route('panel.historias.update', $historia), $this->datosEntrada($paciente, [
            'titulo' => 'Segunda revision',
            'eliminar_adjuntos' => [$viejo->id],
            'adjuntos' => [$this->pdf('nueva.pdf')],
        ]))->assertRedirect(route('panel.historias.show', $historia));

        Storage::disk(HistoriaService::DISCO)->assertMissing($viejo->ruta);
        $this->assertDatabaseMissing('historia_adjuntos', ['id' => $viejo->id]);

        $historia->refresh()->load('adjuntos');

        $this->assertSame('Segunda revision', $historia->titulo);
        $this->assertCount(1, $historia->adjuntos);
        $this->assertSame('nueva.pdf', $historia->adjuntos->first()->nombre_original);
    }

    public function test_borrar_una_entrada_borra_sus_adjuntos_y_sus_ficheros(): void
    {
        $paciente = $this->crearPaciente();

        $this->post(route('panel.historias.store'), $this->datosEntrada($paciente, [
            'adjuntos' => [$this->pdf(), $this->imagen()],
        ]));

        $historia = Historia::with('adjuntos')->first();
        $rutas = $historia->adjuntos->pluck('ruta');

        $this->delete(route('panel.historias.destroy', $historia))
            ->assertRedirect(route('panel.historias.index', ['paciente' => $paciente->id]));

        $this->assertDatabaseMissing('historias', ['id' => $historia->id]);
        $this->assertSame(0, DB::table('historia_adjuntos')->count());

        $rutas->each(fn (string $ruta) => Storage::disk(HistoriaService::DISCO)->assertMissing($ruta));
    }

    public function test_se_puede_borrar_un_adjunto_suelto(): void
    {
        $paciente = $this->crearPaciente();

        $this->post(route('panel.historias.store'), $this->datosEntrada($paciente, [
            'adjuntos' => [$this->pdf(), $this->imagen()],
        ]));

        $historia = Historia::with('adjuntos')->first();
        $adjunto = $historia->adjuntos->first();

        $this->delete(route('panel.historias.adjuntos.destroy', $adjunto))
            ->assertRedirect(route('panel.historias.show', $historia->id));

        Storage::disk(HistoriaService::DISCO)->assertMissing($adjunto->ruta);
        $this->assertCount(1, $historia->refresh()->load('adjuntos')->adjuntos);
    }

    public function test_el_adjunto_se_sirve_con_su_nombre_original(): void
    {
        $paciente = $this->crearPaciente();

        $this->post(route('panel.historias.store'), $this->datosEntrada($paciente, [
            'adjuntos' => [$this->pdf('informe escaneado.pdf')],
        ]));

        $adjunto = Historia::first()->adjuntos->first();

        $this->get(route('panel.historias.adjuntos.descargar', $adjunto))
            ->assertOk()
            ->assertHeader('content-disposition', 'attachment; filename="informe escaneado.pdf"');
    }

    public function test_borrar_un_paciente_borra_los_ficheros_de_su_historia(): void
    {
        $paciente = $this->crearPaciente();

        $this->post(route('panel.historias.store'), $this->datosEntrada($paciente, [
            'adjuntos' => [$this->pdf(), $this->imagen()],
        ]));

        $rutas = Historia::with('adjuntos')->first()->adjuntos->pluck('ruta');

        $this->delete(route('panel.pacientes.destroy', $paciente))
            ->assertRedirect(route('panel.pacientes.index'));

        $this->assertDatabaseMissing('pacientes', ['id' => $paciente->id]);
        $this->assertSame(0, DB::table('historias')->count());
        $this->assertSame(0, DB::table('historia_adjuntos')->count());

        $rutas->each(fn (string $ruta) => Storage::disk(HistoriaService::DISCO)->assertMissing($ruta));
    }

    public function test_el_filtro_por_paciente_solo_muestra_sus_entradas(): void
    {
        $elena = $this->crearPaciente();
        $luis = $this->crearPaciente(['telefono' => '699887766', 'nombre' => 'Luis', 'apellidos' => 'Vera']);

        Historia::create($this->datosEntrada($elena, ['titulo' => 'Sesion de Elena']));
        Historia::create($this->datosEntrada($luis, ['titulo' => 'Sesion de Luis']));

        $this->get(route('panel.historias.index', ['paciente' => $elena->id]))
            ->assertOk()
            ->assertSee('Sesion de Elena')
            ->assertDontSee('Sesion de Luis');
    }

    public function test_la_vista_general_busca_pacientes(): void
    {
        $elena = $this->crearPaciente();
        $luis = $this->crearPaciente(['telefono' => '699887766', 'nombre' => 'Luis', 'apellidos' => 'Vera']);

        Historia::create($this->datosEntrada($elena));
        Historia::create($this->datosEntrada($luis));

        $this->get(route('panel.historias.index', ['q' => 'Vera']))
            ->assertOk()
            ->assertSee('Luis Vera')
            ->assertDontSee('Elena Martinez');

        $this->get(route('panel.historias.index', ['q' => '699887766']))
            ->assertOk()
            ->assertSee('Luis Vera')
            ->assertDontSee('Elena Martinez');
    }

    public function test_la_linea_de_tiempo_busca_por_titulo_de_entrada(): void
    {
        $paciente = $this->crearPaciente();

        Historia::create($this->datosEntrada($paciente, ['titulo' => 'Trabajo de exposicion']));
        Historia::create($this->datosEntrada($paciente, ['titulo' => 'Cierre de proceso']));

        $this->get(route('panel.historias.index', ['paciente' => $paciente->id, 'q' => 'exposicion']))
            ->assertOk()
            ->assertSee('Trabajo de exposicion')
            ->assertDontSee('Cierre de proceso');
    }

    public function test_la_linea_de_tiempo_filtra_por_rango_de_fechas(): void
    {
        $paciente = $this->crearPaciente();

        Historia::create($this->datosEntrada($paciente, ['titulo' => 'Sesion reciente']));
        Historia::create($this->datosEntrada($paciente, [
            'titulo' => 'Sesion antigua',
            'fecha_sesion' => now()->subMonths(3)->toDateString(),
        ]));

        $this->get(route('panel.historias.index', [
            'paciente' => $paciente->id,
            'desde' => now()->subWeek()->toDateString(),
        ]))
            ->assertOk()
            ->assertSee('Sesion reciente')
            ->assertDontSee('Sesion antigua');
    }

    public function test_el_endpoint_de_citas_solo_devuelve_las_del_paciente(): void
    {
        $paciente = $this->crearPaciente();
        $otro = $this->crearPaciente(['telefono' => '699887766', 'nombre' => 'Luis']);

        $cita = $this->crearCita($paciente);
        $this->crearCita($otro);

        $this->getJson(route('panel.historias.citas', ['paciente' => $paciente->id]))
            ->assertOk()
            ->assertJsonCount(1, 'citas')
            ->assertJsonPath('citas.0.id', $cita->id);
    }

    public function test_la_ficha_del_paciente_muestra_sus_ultimas_entradas(): void
    {
        $paciente = $this->crearPaciente();
        Historia::create($this->datosEntrada($paciente, ['titulo' => 'Sesion de seguimiento']));

        $this->get(route('panel.pacientes.show', $paciente))
            ->assertOk()
            ->assertSee('Sesion de seguimiento')
            ->assertSee('Nueva entrada');
    }

    public function test_una_entrada_sin_titulo_se_nombra_con_su_fecha(): void
    {
        $paciente = $this->crearPaciente();

        $historia = Historia::create($this->datosEntrada($paciente, ['titulo' => null]));

        $this->assertSame('Sesion del ' . now()->format('d/m/Y'), $historia->titulo_legible);
    }
}
