<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Panel\ConfiguracionDisponibilidadRequest;
use App\Http\Requests\Panel\FranjasRequest;
use App\Http\Requests\Panel\ModoVacacionesRequest;
use App\Models\ConfiguracionDisponibilidad;
use App\Models\FranjaHoraria;
use App\Models\PeriodoVacaciones;
use App\Services\DisponibilidadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DisponibilidadController extends Controller
{
    public function __construct(private DisponibilidadService $disponibilidad)
    {
    }

    public function index(): View
    {
        return view('panel.disponibilidad.index', [
            'config' => $this->disponibilidad->configuracion(),
            'dias' => DisponibilidadService::DIAS,
            'huecosOnline' => $this->disponibilidad->huecosPosibles('online'),
            'huecosPresencial' => $this->disponibilidad->huecosPosibles('presencial'),
            'franjasOnline' => $this->disponibilidad->franjasPorDia('online'),
            'franjasPresencial' => $this->disponibilidad->franjasPorDia('presencial'),
            'pasoOnline' => $this->disponibilidad->paso('online'),
            'pasoPresencial' => $this->disponibilidad->paso('presencial'),
            'periodos' => PeriodoVacaciones::orderBy('fecha_inicio')->get(),
        ]);
    }

    public function guardarConfiguracion(ConfiguracionDisponibilidadRequest $request): RedirectResponse
    {
        $config = ConfiguracionDisponibilidad::actual();
        $datos = $request->validated();
        $datos['descanso_online_activo'] = $request->boolean('descanso_online_activo');
        $datos['descanso_presencial_activo'] = $request->boolean('descanso_presencial_activo');

        $cambiaOnline = $this->cambia($config, $datos, 'online');
        $cambiaPresencial = $this->cambia($config, $datos, 'presencial');

        $config->fill($datos);

        if ($cambiaOnline) {
            $config->requiere_reconfig_online = true;
        }

        if ($cambiaPresencial) {
            $config->requiere_reconfig_presencial = true;
        }

        $config->save();

        $mensaje = ($cambiaOnline || $cambiaPresencial)
            ? 'Configuracion guardada. Revisa tus horarios semanales: los huecos han cambiado.'
            : 'Configuracion guardada correctamente.';

        return back()->with('exito', $mensaje);
    }

    public function guardarFranjas(FranjasRequest $request): RedirectResponse
    {
        $modalidad = $request->input('modalidad');
        $franjas = $request->franjasNormalizadas();

        DB::transaction(function () use ($modalidad, $franjas) {
            FranjaHoraria::modalidad($modalidad)->delete();

            foreach (array_chunk($franjas, 200) as $lote) {
                FranjaHoraria::insert(array_map(
                    fn (array $franja) => $franja + ['created_at' => now(), 'updated_at' => now()],
                    $lote
                ));
            }

            $config = ConfiguracionDisponibilidad::actual();
            $config->{'requiere_reconfig_' . $modalidad} = false;
            $config->save();
        });

        $etiqueta = $modalidad === 'online' ? 'online' : 'presencial';

        return back()->with('exito', 'Tu disponibilidad ' . $etiqueta . ' se ha guardado (' . count($franjas) . ' huecos).');
    }

    public function modoVacaciones(ModoVacacionesRequest $request): RedirectResponse
    {
        $config = ConfiguracionDisponibilidad::actual();
        $datos = $request->validated();

        $config->modo_vacaciones = $request->boolean('modo_vacaciones');
        $config->mensaje_vacaciones = $datos['mensaje_vacaciones'] ?: null;
        $config->vuelvo_el = $datos['vuelvo_el'] ?: null;
        $config->save();

        $mensaje = $config->modo_vacaciones
            ? 'Modo vacaciones activado: se han pausado las nuevas reservas.'
            : 'Modo vacaciones desactivado: tus pacientes ya pueden reservar.';

        return back()->with('exito', $mensaje);
    }

    private function cambia(ConfiguracionDisponibilidad $config, array $datos, string $modalidad): bool
    {
        $duracionCambia = (int) $config->{'duracion_' . $modalidad} !== (int) $datos['duracion_' . $modalidad];
        $descansoCambia = (int) $config->{'descanso_' . $modalidad} !== (int) $datos['descanso_' . $modalidad];
        $activoCambia = (bool) $config->{'descanso_' . $modalidad . '_activo'} !== (bool) $datos['descanso_' . $modalidad . '_activo'];

        return $duracionCambia || $descansoCambia || $activoCambia;
    }
}
