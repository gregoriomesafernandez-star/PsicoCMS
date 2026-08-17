<?php

namespace Tests\Feature;

use App\Http\Middleware\VerificarInstalacion;
use App\Models\Cita;
use App\Models\ConfiguracionDisponibilidad;
use App\Models\FranjaHoraria;
use App\Models\Paciente;
use App\Models\PeriodoVacaciones;
use App\Models\Psicologa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class CalendarioTest extends TestCase
{
    use RefreshDatabase;

    private Carbon $manana;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(VerificarInstalacion::class);

        $this->manana = Carbon::tomorrow();

        ConfiguracionDisponibilidad::actual()->fill([
            'duracion_online' => 50,
            'duracion_presencial' => 50,
            'descanso_online' => 10,
            'descanso_online_activo' => true,
            'descanso_presencial_activo' => false,
            'hora_inicio' => '09:00:00',
            'hora_fin' => '20:00:00',
        ])->save();

        foreach (['09:00', '10:00', '11:00'] as $hora) {
            FranjaHoraria::create([
                'modalidad' => 'online',
                'dia_semana' => $this->manana->dayOfWeek,
                'hora_inicio' => $hora . ':00',
            ]);
        }

        $this->actingAs(Psicologa::create([
            'nombre' => 'Ana',
            'apellidos' => 'Ruiz',
            'email' => 'ana@ejemplo.test',
            'telefono' => '600111000',
            'password' => 'secreto-largo',
        ]));
    }

    private function crearCita(string $hora = '09:00', string $modalidad = 'online'): Cita
    {
        $paciente = Paciente::firstOrCreate(
            ['telefono' => '600222333'],
            ['nombre' => 'Paciente', 'origen' => 'manual']
        );

        return Cita::create([
            'paciente_id' => $paciente->id,
            'modalidad' => $modalidad,
            'fecha' => $this->manana->toDateString(),
            'hora_inicio' => $hora . ':00',
            'hora_fin' => Carbon::createFromFormat('H:i', $hora)->addMinutes(50)->format('H:i:s'),
            'duracion' => 50,
            'estado' => 'confirmada',
            'origen' => 'manual',
        ]);
    }

    public function test_el_calendario_requiere_autenticacion(): void
    {
        Auth::logout();

        $this->get('/panel-psicologa/calendario')->assertRedirect('/acceso-psicologa');
        $this->getJson('/panel-psicologa/citas/eventos?desde=2026-01-01&hasta=2026-02-01')->assertUnauthorized();
    }

    public function test_la_pagina_del_calendario_se_muestra(): void
    {
        $this->get('/panel-psicologa/calendario')
            ->assertOk()
            ->assertSee('Agenda del dia')
            ->assertSee('vendor/fullcalendar/index.global.min.js');
    }

    public function test_el_endpoint_de_eventos_devuelve_las_citas_del_rango(): void
    {
        $cita = $this->crearCita('10:00');

        $respuesta = $this->getJson(sprintf(
            '/panel-psicologa/citas/eventos?desde=%s&hasta=%s',
            $this->manana->toDateString(),
            $this->manana->copy()->addDay()->toDateString()
        ));

        $respuesta->assertOk()
            ->assertJsonFragment(['id' => (string) $cita->id])
            ->assertJsonFragment(['start' => $this->manana->toDateString() . 'T10:00:00']);
    }

    public function test_los_periodos_de_vacaciones_se_devuelven_como_fondo_bloqueado(): void
    {
        PeriodoVacaciones::create([
            'fecha_inicio' => $this->manana->toDateString(),
            'fecha_fin' => $this->manana->toDateString(),
        ]);

        $this->getJson(sprintf(
            '/panel-psicologa/citas/eventos?desde=%s&hasta=%s',
            $this->manana->toDateString(),
            $this->manana->copy()->addDay()->toDateString()
        ))->assertOk()
            ->assertJsonFragment([
                'display' => 'background',
                'classNames' => ['dia-vacaciones'],
                'title' => 'Vacaciones',
                'start' => $this->manana->toDateString(),
                'allDay' => true,
            ])
            ->assertJsonFragment([
                'display' => 'background',
                'classNames' => ['dia-vacaciones'],
                'title' => 'Vacaciones',
                'start' => $this->manana->toDateString() . 'T00:00:00',
                'end' => $this->manana->toDateString() . 'T23:59:59',
                'allDay' => false,
            ]);
    }

    public function test_mover_una_cita_a_un_hueco_libre_la_actualiza(): void
    {
        $cita = $this->crearCita('09:00');

        $this->patchJson("/panel-psicologa/citas/{$cita->id}/mover", [
            'fecha' => $this->manana->toDateString(),
            'hora_inicio' => '11:00',
            'duracion' => 50,
        ])->assertOk()->assertJson(['ok' => true]);

        $this->assertDatabaseHas('citas', [
            'id' => $cita->id,
            'hora_inicio' => '11:00:00',
            'hora_fin' => '11:50:00',
            'duracion' => 50,
        ]);
    }

    public function test_mover_una_cita_a_una_hora_que_no_es_hueco_se_rechaza(): void
    {
        $cita = $this->crearCita('09:00');

        $this->patchJson("/panel-psicologa/citas/{$cita->id}/mover", [
            'fecha' => $this->manana->toDateString(),
            'hora_inicio' => '10:37',
            'duracion' => 50,
        ])->assertStatus(422)->assertJson(['ok' => false]);

        $this->assertDatabaseHas('citas', ['id' => $cita->id, 'hora_inicio' => '09:00:00']);
    }

    public function test_mover_una_cita_sobre_otra_se_rechaza(): void
    {
        $cita = $this->crearCita('09:00');
        $this->crearCita('11:00');

        $this->patchJson("/panel-psicologa/citas/{$cita->id}/mover", [
            'fecha' => $this->manana->toDateString(),
            'hora_inicio' => '11:00',
            'duracion' => 50,
        ])->assertStatus(422);

        $this->assertDatabaseHas('citas', ['id' => $cita->id, 'hora_inicio' => '09:00:00']);
    }

    public function test_alargar_una_cita_fuera_del_horario_se_rechaza(): void
    {
        $cita = $this->crearCita('09:00');

        $this->patchJson("/panel-psicologa/citas/{$cita->id}/mover", [
            'fecha' => $this->manana->toDateString(),
            'hora_inicio' => '16:00',
            'duracion' => 300,
        ])->assertStatus(422);

        $this->assertDatabaseHas('citas', ['id' => $cita->id, 'duracion' => 50]);
    }

    public function test_alargar_una_cita_dentro_del_horario_y_sin_solapes_se_guarda(): void
    {
        $cita = $this->crearCita('09:00');

        $this->patchJson("/panel-psicologa/citas/{$cita->id}/mover", [
            'fecha' => $this->manana->toDateString(),
            'hora_inicio' => '09:00',
            'duracion' => 90,
        ])->assertOk();

        $this->assertDatabaseHas('citas', [
            'id' => $cita->id,
            'hora_fin' => '10:30:00',
            'duracion' => 90,
        ]);
    }
}
