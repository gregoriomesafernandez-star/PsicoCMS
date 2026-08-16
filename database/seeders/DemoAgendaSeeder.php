<?php

namespace Database\Seeders;

use App\Models\Cita;
use App\Models\ConfiguracionDisponibilidad;
use App\Models\FranjaHoraria;
use App\Models\Paciente;
use App\Services\DisponibilidadService;
use Illuminate\Database\Seeder;

class DemoAgendaSeeder extends Seeder
{
    public function run(): void
    {
        $config = ConfiguracionDisponibilidad::actual();
        $config->fill([
            'duracion_online' => 50,
            'duracion_presencial' => 50,
            'descanso_online' => 10,
            'descanso_presencial' => 10,
            'descanso_online_activo' => true,
            'descanso_presencial_activo' => true,
            'hora_inicio' => '09:00:00',
            'hora_fin' => '20:00:00',
        ])->save();

        $disponibilidad = app(DisponibilidadService::class);

        FranjaHoraria::truncate();

        foreach (['online', 'presencial'] as $modalidad) {
            $huecos = array_slice($disponibilidad->huecosPosibles($modalidad), 0, 8);

            foreach ([1, 2, 3, 4, 5] as $dia) {
                foreach ($huecos as $hora) {
                    FranjaHoraria::create([
                        'modalidad' => $modalidad,
                        'dia_semana' => $dia,
                        'hora_inicio' => $hora . ':00',
                    ]);
                }
            }
        }

        $pacientes = [
            ['nombre' => 'Carlos', 'apellidos' => 'Mendoza', 'telefono' => '600111222'],
            ['nombre' => 'Laura', 'apellidos' => 'Sanchez', 'telefono' => '600333444'],
            ['nombre' => 'Miguel', 'apellidos' => 'Rojas', 'telefono' => '600555666'],
        ];

        $creados = [];

        foreach ($pacientes as $datos) {
            $creados[] = Paciente::firstOrCreate(
                ['telefono' => $datos['telefono']],
                $datos + ['origen' => 'manual', 'estado' => 'activo']
            );
        }

        $hoy = now();
        $agenda = [
            ['paciente' => 0, 'dias' => 0, 'hora' => '10:00', 'modalidad' => 'presencial', 'estado' => 'confirmada'],
            ['paciente' => 1, 'dias' => 0, 'hora' => '12:00', 'modalidad' => 'online', 'estado' => 'confirmada'],
            ['paciente' => 2, 'dias' => 2, 'hora' => '11:00', 'modalidad' => 'presencial', 'estado' => 'pendiente'],
            ['paciente' => 0, 'dias' => 5, 'hora' => '09:00', 'modalidad' => 'online', 'estado' => 'pendiente'],
        ];

        foreach ($agenda as $entrada) {
            $fecha = $hoy->copy()->addDays($entrada['dias']);
            $duracion = $disponibilidad->duracion($entrada['modalidad']);

            Cita::firstOrCreate(
                [
                    'paciente_id' => $creados[$entrada['paciente']]->id,
                    'fecha' => $fecha->toDateString(),
                    'hora_inicio' => $entrada['hora'] . ':00',
                ],
                [
                    'modalidad' => $entrada['modalidad'],
                    'hora_fin' => $disponibilidad->horaFin($entrada['hora'], $entrada['modalidad']),
                    'duracion' => $duracion,
                    'estado' => $entrada['estado'],
                    'motivo' => 'Cita de ejemplo generada para probar el panel.',
                    'origen' => 'manual',
                ]
            );
        }
    }
}
