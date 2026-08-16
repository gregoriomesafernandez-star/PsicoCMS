<?php

namespace Tests\Feature;

use App\Models\Cita;
use App\Models\ConfiguracionDisponibilidad;
use App\Models\FranjaHoraria;
use App\Models\Paciente;
use App\Models\PeriodoVacaciones;
use App\Services\DisponibilidadService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DisponibilidadTest extends TestCase
{
    use RefreshDatabase;

    private function configurar(array $valores): DisponibilidadService
    {
        ConfiguracionDisponibilidad::actual()->fill($valores + [
            'hora_inicio' => '09:00:00',
            'hora_fin' => '20:00:00',
        ])->save();

        return app(DisponibilidadService::class);
    }

    private function marcarSemana(string $modalidad, array $horas): void
    {
        foreach (range(0, 6) as $dia) {
            foreach ($horas as $hora) {
                FranjaHoraria::create([
                    'modalidad' => $modalidad,
                    'dia_semana' => $dia,
                    'hora_inicio' => $hora . ':00',
                ]);
            }
        }
    }

    public function test_online_de_50_minutos_con_10_de_descanso_genera_huecos_cada_hora(): void
    {
        $servicio = $this->configurar([
            'duracion_online' => 50,
            'descanso_online' => 10,
            'descanso_online_activo' => true,
            'descanso_presencial_activo' => true,
        ]);

        $huecos = $servicio->huecosPosibles('online');

        $this->assertSame(60, $servicio->paso('online'));
        $this->assertSame(['09:00', '10:00', '11:00'], array_slice($huecos, 0, 3));
    }

    public function test_presencial_de_50_minutos_sin_descanso_genera_huecos_cada_50_minutos(): void
    {
        $servicio = $this->configurar([
            'duracion_presencial' => 50,
            'descanso_presencial' => 0,
            'descanso_online_activo' => false,
            'descanso_presencial_activo' => false,
        ]);

        $huecos = $servicio->huecosPosibles('presencial');

        $this->assertSame(50, $servicio->paso('presencial'));
        $this->assertSame(['09:00', '09:50', '10:40'], array_slice($huecos, 0, 3));
    }

    public function test_se_aprovecha_el_ultimo_hueco_si_la_sesion_cabe_antes_de_la_hora_de_salida(): void
    {
        ConfiguracionDisponibilidad::actual()->fill([
            'duracion_presencial' => 45,
            'descanso_presencial' => 10,
            'descanso_presencial_activo' => true,
            'hora_inicio' => '09:00:00',
            'hora_fin' => '16:00:00',
        ])->save();

        $huecos = app(DisponibilidadService::class)->huecosPosibles('presencial');

        $this->assertSame('15:15', end($huecos));
        $this->assertContains('14:30', $huecos);
    }

    public function test_no_se_anade_un_ultimo_hueco_que_se_pase_de_la_hora_de_salida(): void
    {
        ConfiguracionDisponibilidad::actual()->fill([
            'duracion_presencial' => 45,
            'descanso_presencial' => 10,
            'descanso_presencial_activo' => true,
            'hora_inicio' => '09:00:00',
            'hora_fin' => '15:50:00',
        ])->save();

        $huecos = app(DisponibilidadService::class)->huecosPosibles('presencial');

        $this->assertSame('14:30', end($huecos));
    }

    public function test_una_cita_existente_elimina_su_hueco(): void
    {
        $servicio = $this->configurar([
            'duracion_online' => 50,
            'descanso_online' => 10,
            'descanso_online_activo' => true,
            'descanso_presencial_activo' => true,
        ]);

        $this->marcarSemana('online', ['09:00', '10:00', '11:00']);

        $manana = Carbon::tomorrow();
        $paciente = Paciente::create(['telefono' => '600111222', 'nombre' => 'Prueba']);

        Cita::create([
            'paciente_id' => $paciente->id,
            'modalidad' => 'online',
            'fecha' => $manana->toDateString(),
            'hora_inicio' => '10:00:00',
            'hora_fin' => '10:50:00',
            'duracion' => 50,
            'estado' => 'confirmada',
            'origen' => 'manual',
        ]);

        $huecos = $servicio->huecosDeDia($manana, 'online');

        $this->assertNotContains('10:00', $huecos);
        $this->assertContains('09:00', $huecos);
        $this->assertTrue($servicio->haySolapamiento($manana, '10:00', 'online'));
    }

    public function test_una_cita_presencial_bloquea_el_hueco_online_a_la_misma_hora(): void
    {
        $servicio = $this->configurar([
            'duracion_online' => 50,
            'duracion_presencial' => 50,
            'descanso_online_activo' => false,
            'descanso_presencial_activo' => false,
        ]);

        $this->marcarSemana('online', ['09:00']);
        $this->marcarSemana('presencial', ['09:00']);

        $manana = Carbon::tomorrow();
        $paciente = Paciente::create(['telefono' => '600111222', 'nombre' => 'Prueba']);

        Cita::create([
            'paciente_id' => $paciente->id,
            'modalidad' => 'presencial',
            'fecha' => $manana->toDateString(),
            'hora_inicio' => '09:00:00',
            'hora_fin' => '09:50:00',
            'duracion' => 50,
            'estado' => 'confirmada',
            'origen' => 'manual',
        ]);

        $this->assertNotContains('09:00', $servicio->huecosDeDia($manana, 'online'));
    }

    public function test_el_modo_vacaciones_deja_el_dia_sin_huecos(): void
    {
        $servicio = $this->configurar([
            'duracion_online' => 50,
            'modo_vacaciones' => true,
        ]);

        $this->marcarSemana('online', ['09:00', '10:00']);

        $this->assertSame([], $servicio->huecosDeDia(Carbon::tomorrow(), 'online'));
    }

    public function test_un_periodo_de_vacaciones_bloquea_sus_fechas(): void
    {
        $servicio = $this->configurar([
            'duracion_online' => 50,
            'descanso_online_activo' => false,
            'descanso_presencial_activo' => false,
        ]);

        $this->marcarSemana('online', ['09:00', '10:00']);

        $inicio = Carbon::tomorrow();
        $fin = $inicio->copy()->addDays(3);

        PeriodoVacaciones::create([
            'fecha_inicio' => $inicio->toDateString(),
            'fecha_fin' => $fin->toDateString(),
        ]);

        $this->assertTrue($servicio->enVacaciones($inicio));
        $this->assertSame([], $servicio->huecosDeDia($inicio, 'online'));
        $this->assertContains($inicio->toDateString(), $servicio->diasBloqueados($inicio, $fin));
    }

    public function test_los_huecos_pasados_no_se_ofrecen(): void
    {
        $servicio = $this->configurar([
            'duracion_online' => 50,
            'descanso_online_activo' => false,
            'descanso_presencial_activo' => false,
        ]);

        $this->marcarSemana('online', ['09:00', '10:00']);

        $this->assertSame([], $servicio->huecosDeDia(Carbon::yesterday(), 'online'));
    }

    public function test_al_borrar_una_cita_desaparece_de_la_tabla(): void
    {
        $paciente = Paciente::create(['telefono' => '600111222', 'nombre' => 'Prueba']);

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

        $cita->delete();

        $this->assertDatabaseMissing('citas', ['id' => $cita->id]);
        $this->assertSame(0, DB::table('citas')->count());
        $this->assertDatabaseHas('pacientes', ['id' => $paciente->id]);
    }

    public function test_el_telefono_del_paciente_se_normaliza(): void
    {
        $paciente = Paciente::create([
            'telefono' => ' 600 11 12 22 ',
            'nombre' => 'Prueba',
        ]);

        $this->assertSame('600111222', $paciente->fresh()->telefono);
    }
}
