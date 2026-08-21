<?php

namespace Tests\Feature;

use App\Http\Middleware\VerificarInstalacion;
use App\Models\Cita;
use App\Models\ConfiguracionDisponibilidad;
use App\Models\FranjaHoraria;
use App\Models\Paciente;
use App\Models\PerfilPublico;
use App\Models\PeriodoVacaciones;
use App\Services\ModuloService;
use App\Services\ReservaService;
use App\Services\TemaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ReservaPublicaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(VerificarInstalacion::class);

        PerfilPublico::actual()->update([
            'nombre_publico' => 'Ana Ruiz',
            'telefono_citas' => '600 11 22 33',
            'direccion' => 'Calle Mayor 12, Madrid',
        ]);

        ConfiguracionDisponibilidad::actual()->fill([
            'duracion_online' => 50,
            'descanso_online' => 10,
            'descanso_online_activo' => true,
            'duracion_presencial' => 45,
            'descanso_presencial_activo' => false,
            'hora_inicio' => '09:00:00',
            'hora_fin' => '20:00:00',
            'modo_vacaciones' => false,
        ])->save();

        app(TemaService::class)->guardarModo('multipagina');
    }

    /** Marca todas las horas de todos los dias para esa modalidad. */
    private function abrirAgenda(string $modalidad, array $horas): void
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

    private function manana(): Carbon
    {
        return now()->addDay()->startOfDay();
    }

    private function datosValidos(array $cambios = []): array
    {
        return array_merge([
            'modalidad' => 'online',
            'fecha' => $this->manana()->toDateString(),
            'hora' => '10:00',
            'nombre' => 'Marta Gil',
            'telefono' => '611 22 33 44',
            'motivo' => 'Ansiedad en el trabajo',
            'consentimiento' => '1',
        ], $cambios);
    }

    /* ---------- Endpoints de disponibilidad ---------- */

    public function test_el_calendario_devuelve_los_dias_con_hueco_del_mes(): void
    {
        $this->abrirAgenda('online', ['09:00', '10:00']);

        $respuesta = $this->getJson(route('publico.reservas.disponibilidad', [
            'modalidad' => 'online',
            'mes' => now()->format('Y-m'),
        ]));

        $respuesta->assertOk()
            ->assertJsonPath('mes', now()->format('Y-m'))
            ->assertJsonPath('anterior', null)
            ->assertJsonPath('dias.' . $this->manana()->toDateString(), true);
    }

    public function test_el_calendario_no_deja_ir_mas_alla_de_la_ventana_de_reserva(): void
    {
        $this->abrirAgenda('online', ['09:00']);

        $ultimo = app(ReservaService::class)->mesLimite();

        $this->getJson(route('publico.reservas.disponibilidad', ['modalidad' => 'online', 'mes' => $ultimo]))
            ->assertOk()
            ->assertJsonPath('siguiente', null);
    }

    public function test_los_dias_en_vacaciones_salen_bloqueados(): void
    {
        $this->abrirAgenda('online', ['09:00', '10:00']);

        PeriodoVacaciones::create([
            'fecha_inicio' => $this->manana()->toDateString(),
            'fecha_fin' => $this->manana()->addDays(3)->toDateString(),
        ]);

        $this->getJson(route('publico.reservas.disponibilidad', [
            'modalidad' => 'online',
            'mes' => now()->format('Y-m'),
        ]))->assertOk()->assertJsonPath('dias.' . $this->manana()->toDateString(), false);
    }

    public function test_las_horas_libres_respetan_el_descanso_entre_sesiones(): void
    {
        $this->abrirAgenda('online', ['09:00', '10:00', '11:00']);

        $this->getJson(route('publico.reservas.huecos', [
            'modalidad' => 'online',
            'fecha' => $this->manana()->toDateString(),
        ]))->assertOk()
            ->assertJsonPath('huecos', ['09:00', '10:00', '11:00'])
            ->assertJsonPath('duracion', 50);
    }

    public function test_una_hora_ya_reservada_desaparece_de_las_libres(): void
    {
        $this->abrirAgenda('online', ['09:00', '10:00']);

        $this->post(route('publico.reservas.store'), $this->datosValidos());

        $this->getJson(route('publico.reservas.huecos', [
            'modalidad' => 'online',
            'fecha' => $this->manana()->toDateString(),
        ]))->assertOk()->assertJsonPath('huecos', ['09:00']);
    }

    /* ---------- Reserva ---------- */

    public function test_una_reserva_crea_la_cita_y_la_ficha_del_paciente(): void
    {
        $this->abrirAgenda('online', ['09:00', '10:00']);

        $this->post(route('publico.reservas.store'), $this->datosValidos())
            ->assertRedirect(route('publico.pide-cita'))
            ->assertSessionHas('reserva');

        $paciente = Paciente::first();

        $this->assertSame('611223344', $paciente->telefono);
        $this->assertSame('Marta Gil', $paciente->nombre);
        $this->assertSame('web', $paciente->origen);

        $cita = Cita::first();

        $this->assertSame($paciente->id, $cita->paciente_id);
        $this->assertSame('10:00:00', $cita->hora_inicio);
        $this->assertSame('10:50:00', $cita->hora_fin);
        $this->assertSame(50, $cita->duracion);
        $this->assertSame('pendiente', $cita->estado);
        $this->assertSame('web', $cita->origen);
    }

    public function test_reservar_dos_veces_con_el_mismo_telefono_reutiliza_la_ficha(): void
    {
        $this->abrirAgenda('online', ['09:00', '10:00']);

        $this->post(route('publico.reservas.store'), $this->datosValidos());
        $this->post(route('publico.reservas.store'), $this->datosValidos(['hora' => '09:00']));

        $this->assertSame(1, Paciente::count());
        $this->assertSame(2, Cita::count());
    }

    public function test_no_se_puede_reservar_una_hora_ya_ocupada(): void
    {
        $this->abrirAgenda('online', ['09:00', '10:00']);

        $this->post(route('publico.reservas.store'), $this->datosValidos());

        $this->from(route('publico.pide-cita'))
            ->post(route('publico.reservas.store'), $this->datosValidos(['nombre' => 'Otra persona', 'telefono' => '699887766']))
            ->assertSessionHasErrors('hora');

        $this->assertSame(1, Cita::count());
    }

    public function test_no_se_puede_reservar_una_hora_fuera_de_la_disponibilidad(): void
    {
        $this->abrirAgenda('online', ['09:00']);

        $this->from(route('publico.pide-cita'))
            ->post(route('publico.reservas.store'), $this->datosValidos(['hora' => '17:00']))
            ->assertSessionHasErrors('hora');

        $this->assertSame(0, Cita::count());
    }

    public function test_no_se_puede_reservar_en_una_fecha_pasada(): void
    {
        $this->abrirAgenda('online', ['09:00']);

        $this->from(route('publico.pide-cita'))
            ->post(route('publico.reservas.store'), $this->datosValidos([
                'fecha' => now()->subDay()->toDateString(),
            ]))
            ->assertSessionHasErrors('fecha');

        $this->assertSame(0, Cita::count());
    }

    public function test_el_telefono_solo_admite_numeros(): void
    {
        $this->abrirAgenda('online', ['10:00']);

        $this->from(route('publico.pide-cita'))
            ->post(route('publico.reservas.store'), $this->datosValidos(['telefono' => 'no-es-un-telefono']))
            ->assertSessionHasErrors('telefono');

        $this->assertSame(0, Cita::count());
    }

    public function test_sin_consentimiento_no_hay_cita(): void
    {
        $this->abrirAgenda('online', ['10:00']);

        $this->from(route('publico.pide-cita'))
            ->post(route('publico.reservas.store'), $this->datosValidos(['consentimiento' => null]))
            ->assertSessionHasErrors('consentimiento');

        $this->assertSame(0, Cita::count());
    }

    public function test_el_modo_vacaciones_cierra_la_agenda(): void
    {
        $this->abrirAgenda('online', ['10:00']);

        ConfiguracionDisponibilidad::actual()->update(['modo_vacaciones' => true]);

        $this->from(route('publico.pide-cita'))
            ->post(route('publico.reservas.store'), $this->datosValidos())
            ->assertSessionHasErrors('fecha');

        $this->assertSame(0, Cita::count());
    }

    /* ---------- Pantalla ---------- */

    public function test_la_pagina_muestra_el_formulario_cuando_hay_agenda(): void
    {
        $this->abrirAgenda('online', ['09:00', '10:00']);

        $this->get(route('publico.pide-cita'))
            ->assertOk()
            ->assertSee('data-reserva', false)
            ->assertSee('data-calendario', false)
            ->assertSee('Confirmar mi cita');
    }

    public function test_sin_horario_marcado_se_ofrece_el_contacto_directo(): void
    {
        $this->get(route('publico.pide-cita'))
            ->assertOk()
            ->assertDontSee('data-reserva', false)
            ->assertSee('Todavia no hay horario publicado');
    }

    public function test_en_modo_vacaciones_se_muestra_el_mensaje_de_la_psicologa(): void
    {
        $this->abrirAgenda('online', ['10:00']);

        ConfiguracionDisponibilidad::actual()->update([
            'modo_vacaciones' => true,
            'mensaje_vacaciones' => 'Estoy de descanso hasta septiembre.',
        ]);

        $this->get(route('publico.pide-cita'))
            ->assertOk()
            ->assertDontSee('data-reserva', false)
            ->assertSee('Estoy de descanso hasta septiembre.');
    }

    public function test_la_confirmacion_ofrece_anadir_la_cita_a_google_calendar(): void
    {
        $this->abrirAgenda('online', ['10:00']);

        $respuesta = $this->post(route('publico.reservas.store'), $this->datosValidos());
        $reserva = session('reserva');

        $this->assertStringContainsString('calendar.google.com', $reserva['google']);
        $this->assertStringContainsString('action=TEMPLATE', $reserva['google']);

        $this->followRedirects($respuesta)->assertSee('Anadir a Google Calendar');
    }

    public function test_en_landing_la_reserva_vuelve_a_la_portada(): void
    {
        app(TemaService::class)->guardarModo('landing');
        $this->abrirAgenda('online', ['10:00']);

        $this->post(route('publico.reservas.store'), $this->datosValidos())
            ->assertRedirect(route('home') . '#pide-cita');
    }

    /* ---------- Modulo apagado ---------- */

    public function test_con_el_modulo_de_reservas_apagado_no_hay_ni_pagina_ni_endpoints(): void
    {
        $this->abrirAgenda('online', ['10:00']);

        $modulos = app(ModuloService::class);
        $modulos->guardar(array_diff($modulos->claves(), ['reservas']));

        $this->get(route('publico.pide-cita'))->assertNotFound();
        $this->getJson(route('publico.reservas.huecos', ['modalidad' => 'online', 'fecha' => $this->manana()->toDateString()]))->assertNotFound();
        $this->post(route('publico.reservas.store'), $this->datosValidos())->assertNotFound();

        $this->assertSame(0, Cita::count());
    }
}
