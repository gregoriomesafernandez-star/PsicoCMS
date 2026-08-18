<?php

namespace Tests\Feature;

use App\Http\Middleware\VerificarInstalacion;
use App\Models\Cita;
use App\Models\ConfiguracionDisponibilidad;
use App\Models\FranjaHoraria;
use App\Models\Paciente;
use App\Models\Psicologa;
use App\Services\PacienteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PacienteTest extends TestCase
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

    private function datosPaciente(array $extra = []): array
    {
        return array_merge([
            'nombre' => 'Elena',
            'apellidos' => 'Martinez',
            'telefono' => '611 22 33 44',
            'email' => 'elena@ejemplo.test',
            'estado' => 'activo',
            'motivo_consulta' => '<p>Ansiedad laboral.</p>',
            'notas' => '',
        ], $extra);
    }

    private function crearPaciente(array $extra = []): Paciente
    {
        $this->post('/panel-psicologa/pacientes', $this->datosPaciente($extra));

        return Paciente::latest('id')->firstOrFail();
    }

    public function test_los_pacientes_requieren_autenticacion(): void
    {
        Auth::logout();

        $this->get('/panel-psicologa/pacientes')->assertRedirect('/acceso-psicologa');
        $this->getJson('/panel-psicologa/pacientes/buscar?q=ana')->assertUnauthorized();
    }

    public function test_las_pantallas_de_pacientes_se_muestran(): void
    {
        $paciente = $this->crearPaciente();

        $this->get('/panel-psicologa/pacientes')->assertOk()->assertSee('Gestion de pacientes');
        $this->get('/panel-psicologa/pacientes/nuevo')->assertOk()->assertSee('Nuevo paciente');
        $this->get("/panel-psicologa/pacientes/{$paciente->id}")->assertOk()->assertSee('Elena Martinez');
        $this->get("/panel-psicologa/pacientes/{$paciente->id}/editar")->assertOk();
    }

    public function test_el_telefono_se_normaliza_al_crear(): void
    {
        $paciente = $this->crearPaciente();

        $this->assertSame('611223344', $paciente->telefono);
        $this->assertSame('manual', $paciente->origen);
    }

    public function test_no_se_admiten_dos_pacientes_con_el_mismo_telefono(): void
    {
        $this->crearPaciente();

        $this->post('/panel-psicologa/pacientes', $this->datosPaciente(['nombre' => 'Otra']))
            ->assertSessionHasErrors('telefono');

        $this->assertSame(1, Paciente::count());
    }

    public function test_el_telefono_solo_admite_numeros(): void
    {
        $this->post('/panel-psicologa/pacientes', $this->datosPaciente(['telefono' => 'sin numeros']))
            ->assertSessionHasErrors('telefono');
    }

    public function test_el_motivo_de_consulta_se_sanitiza(): void
    {
        $paciente = $this->crearPaciente([
            'motivo_consulta' => '<p onclick="malo()">Ansiedad</p><script>alert(1)</script>',
        ]);

        $this->assertStringNotContainsString('<script', $paciente->motivo_consulta);
        $this->assertStringNotContainsString('onclick', $paciente->motivo_consulta);
        $this->assertStringContainsString('Ansiedad', $paciente->motivo_consulta);
    }

    public function test_editar_amplia_los_datos_de_la_ficha(): void
    {
        $paciente = $this->crearPaciente();

        $this->put("/panel-psicologa/pacientes/{$paciente->id}", $this->datosPaciente([
            'dni' => '12345678Z',
            'genero' => 'mujer',
            'fecha_nacimiento' => '1990-05-20',
            'estado' => 'pausado',
        ]))->assertRedirect(route('panel.pacientes.show', $paciente));

        $paciente->refresh();

        $this->assertSame('12345678Z', $paciente->dni);
        $this->assertSame('pausado', $paciente->estado);
        $this->assertSame('Mujer', $paciente->genero_legible);
    }

    public function test_eliminar_un_paciente_borra_tambien_sus_citas_e_historias(): void
    {
        $paciente = $this->crearPaciente();

        $cita = Cita::create([
            'paciente_id' => $paciente->id,
            'modalidad' => 'online',
            'fecha' => Carbon::tomorrow()->toDateString(),
            'hora_inicio' => '09:00:00',
            'hora_fin' => '09:50:00',
            'duracion' => 50,
            'estado' => 'confirmada',
            'origen' => 'manual',
        ]);

        $historia = DB::table('historias')->insertGetId([
            'paciente_id' => $paciente->id,
            'fecha_sesion' => Carbon::today()->toDateString(),
            'titulo' => 'Primera sesion',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->delete("/panel-psicologa/pacientes/{$paciente->id}")->assertRedirect();

        $this->assertDatabaseMissing('pacientes', ['id' => $paciente->id]);
        $this->assertDatabaseMissing('citas', ['id' => $cita->id]);
        $this->assertDatabaseMissing('historias', ['id' => $historia]);
        $this->assertSame(0, Paciente::withTrashed()->count());
    }

    public function test_el_aviso_de_borrado_avisa_de_lo_que_se_pierde(): void
    {
        $paciente = $this->crearPaciente();

        $this->get('/panel-psicologa/pacientes')
            ->assertOk()
            ->assertSee('No tiene citas ni historia clinica');

        Cita::create([
            'paciente_id' => $paciente->id,
            'modalidad' => 'online',
            'fecha' => Carbon::tomorrow()->toDateString(),
            'hora_inicio' => '09:00:00',
            'hora_fin' => '09:50:00',
            'duracion' => 50,
            'estado' => 'confirmada',
            'origen' => 'manual',
        ]);

        $this->get('/panel-psicologa/pacientes')
            ->assertOk()
            ->assertSee('TODO su historial')
            ->assertSee('1 cita(s)');
    }

    public function test_el_buscador_devuelve_sugerencias(): void
    {
        $this->crearPaciente();

        $this->getJson('/panel-psicologa/pacientes/buscar?q=Ele')
            ->assertOk()
            ->assertJsonFragment(['nombre' => 'Elena Martinez', 'telefono' => '611223344']);
    }

    public function test_el_buscador_solo_busca_por_nombre(): void
    {
        $this->crearPaciente();

        $this->getJson('/panel-psicologa/pacientes/buscar?q=Martinez')
            ->assertOk()
            ->assertJsonCount(1, 'pacientes');

        $this->getJson('/panel-psicologa/pacientes/buscar?q=Elena Mar')
            ->assertOk()
            ->assertJsonCount(1, 'pacientes');

        $this->getJson('/panel-psicologa/pacientes/buscar?q=elena@ejemplo.test')
            ->assertOk()
            ->assertJsonCount(0, 'pacientes');

        $this->getJson('/panel-psicologa/pacientes/buscar?q=611223344')
            ->assertOk()
            ->assertJsonCount(0, 'pacientes');
    }

    public function test_el_filtro_del_listado_si_busca_por_email_y_telefono(): void
    {
        $this->crearPaciente();

        $this->get('/panel-psicologa/pacientes?q=elena@ejemplo.test')->assertOk()->assertSee('Elena Martinez');
        $this->get('/panel-psicologa/pacientes?q=611223344')->assertOk()->assertSee('Elena Martinez');
    }

    public function test_el_buscador_ignora_busquedas_demasiado_cortas(): void
    {
        $this->crearPaciente();

        $this->getJson('/panel-psicologa/pacientes/buscar?q=E')
            ->assertOk()
            ->assertJsonCount(0, 'pacientes');
    }

    public function test_buscar_o_crear_reutiliza_el_paciente_por_telefono(): void
    {
        $paciente = $this->crearPaciente();
        $servicio = app(PacienteService::class);

        $encontrado = $servicio->buscarOCrear('611 223 344', ['nombre' => 'Elena M.']);

        $this->assertSame($paciente->id, $encontrado->id);
        $this->assertSame(1, Paciente::count());
        $this->assertSame('Elena M.', $encontrado->nombre);
    }

    public function test_buscar_o_crear_recupera_un_paciente_quitado(): void
    {
        $paciente = $this->crearPaciente();
        $paciente->delete();

        $recuperado = app(PacienteService::class)->buscarOCrear('611223344', ['nombre' => 'Elena']);

        $this->assertSame($paciente->id, $recuperado->id);
        $this->assertFalse($recuperado->trashed());
    }

    public function test_crear_una_cita_desde_la_ficha_vuelve_a_la_ficha(): void
    {
        ConfiguracionDisponibilidad::actual()->fill([
            'duracion_online' => 50,
            'descanso_online_activo' => false,
            'descanso_presencial_activo' => false,
            'hora_inicio' => '09:00:00',
            'hora_fin' => '20:00:00',
        ])->save();

        $manana = Carbon::tomorrow();

        FranjaHoraria::create([
            'modalidad' => 'online',
            'dia_semana' => $manana->dayOfWeek,
            'hora_inicio' => '09:00:00',
        ]);

        $paciente = $this->crearPaciente();

        $this->post('/panel-psicologa/citas', [
            'paciente_nombre' => $paciente->nombre,
            'paciente_apellidos' => $paciente->apellidos,
            'paciente_telefono' => $paciente->telefono,
            'paciente_email' => $paciente->email,
            'modalidad' => 'online',
            'fecha' => $manana->toDateString(),
            'hora_inicio' => '09:00',
            'estado' => 'pendiente',
            'volver' => 'paciente',
        ])->assertRedirect(route('panel.pacientes.show', $paciente));

        $this->assertSame(1, Paciente::count());
        $this->assertDatabaseHas('citas', ['paciente_id' => $paciente->id, 'hora_inicio' => '09:00:00']);
    }

    public function test_el_motivo_de_la_ficha_es_independiente_del_de_sus_citas(): void
    {
        $paciente = $this->crearPaciente();

        $cita = Cita::create([
            'paciente_id' => $paciente->id,
            'modalidad' => 'online',
            'fecha' => Carbon::tomorrow()->toDateString(),
            'hora_inicio' => '09:00:00',
            'hora_fin' => '09:50:00',
            'duracion' => 50,
            'estado' => 'confirmada',
            'origen' => 'manual',
            'motivo' => '<p>Sesion de seguimiento</p>',
            'notas' => '<p>Vino puntual</p>',
        ]);

        $this->put("/panel-psicologa/pacientes/{$paciente->id}", $this->datosPaciente([
            'motivo_consulta' => '<p>Ansiedad y estres laboral</p>',
            'notas' => '<p>Prefiere sesiones por la manana</p>',
        ]))->assertRedirect();

        $cita->refresh();

        $this->assertStringContainsString('Sesion de seguimiento', $cita->motivo);
        $this->assertStringContainsString('Vino puntual', $cita->notas);
        $this->assertStringContainsString('Ansiedad y estres laboral', $paciente->fresh()->motivo_consulta);
    }

    public function test_guardar_una_cita_no_toca_el_motivo_de_la_ficha(): void
    {
        ConfiguracionDisponibilidad::actual()->fill([
            'duracion_online' => 50,
            'descanso_online_activo' => false,
            'descanso_presencial_activo' => false,
            'hora_inicio' => '09:00:00',
            'hora_fin' => '20:00:00',
        ])->save();

        $manana = Carbon::tomorrow();

        FranjaHoraria::create([
            'modalidad' => 'online',
            'dia_semana' => $manana->dayOfWeek,
            'hora_inicio' => '09:00:00',
        ]);

        $paciente = $this->crearPaciente(['motivo_consulta' => '<p>Ansiedad laboral</p>']);

        $this->post('/panel-psicologa/citas', [
            'paciente_nombre' => $paciente->nombre,
            'paciente_apellidos' => $paciente->apellidos,
            'paciente_telefono' => $paciente->telefono,
            'modalidad' => 'online',
            'fecha' => $manana->toDateString(),
            'hora_inicio' => '09:00',
            'estado' => 'pendiente',
            'motivo' => '<p>Primera toma de contacto</p>',
            'notas' => '<p>Derivada por su medico</p>',
        ])->assertRedirect();

        $cita = Cita::firstOrFail();

        $this->assertStringContainsString('Primera toma de contacto', $cita->motivo);
        $this->assertStringContainsString('Ansiedad laboral', $paciente->fresh()->motivo_consulta);
        $this->assertStringNotContainsString('Primera toma', (string) $paciente->fresh()->motivo_consulta);
    }

    public function test_la_cita_guarda_nombre_y_apellidos_por_separado(): void
    {
        ConfiguracionDisponibilidad::actual()->fill([
            'duracion_online' => 50,
            'descanso_online_activo' => false,
            'descanso_presencial_activo' => false,
            'hora_inicio' => '09:00:00',
            'hora_fin' => '20:00:00',
        ])->save();

        $manana = Carbon::tomorrow();

        FranjaHoraria::create([
            'modalidad' => 'online',
            'dia_semana' => $manana->dayOfWeek,
            'hora_inicio' => '09:00:00',
        ]);

        $this->post('/panel-psicologa/citas', [
            'paciente_nombre' => 'Marta',
            'paciente_apellidos' => 'Gomez Ruiz',
            'paciente_telefono' => '699 88 77 66',
            'modalidad' => 'online',
            'fecha' => $manana->toDateString(),
            'hora_inicio' => '09:00',
            'estado' => 'pendiente',
        ])->assertRedirect();

        $paciente = Paciente::where('telefono', '699887766')->firstOrFail();

        $this->assertSame('Marta', $paciente->nombre);
        $this->assertSame('Gomez Ruiz', $paciente->apellidos);
        $this->assertSame('Marta Gomez Ruiz', $paciente->nombre_completo);
    }

    public function test_el_buscador_devuelve_el_nombre_partido_para_el_formulario(): void
    {
        $this->crearPaciente();

        $this->getJson('/panel-psicologa/pacientes/buscar?q=Elena')
            ->assertOk()
            ->assertJsonPath('pacientes.0.nombre_pila', 'Elena')
            ->assertJsonPath('pacientes.0.apellidos', 'Martinez')
            ->assertJsonPath('pacientes.0.nombre', 'Elena Martinez');
    }

    public function test_los_filtros_del_listado_funcionan(): void
    {
        $this->crearPaciente();
        $this->crearPaciente(['nombre' => 'Carlos', 'telefono' => '622334455', 'estado' => 'pausado']);

        $this->get('/panel-psicologa/pacientes?estado=pausado')
            ->assertOk()
            ->assertSee('Carlos')
            ->assertDontSee('Elena Martinez');

        $this->get('/panel-psicologa/pacientes?q=622')
            ->assertOk()
            ->assertSee('Carlos');
    }
}
