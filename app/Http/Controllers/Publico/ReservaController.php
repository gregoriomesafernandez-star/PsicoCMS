<?php

namespace App\Http\Controllers\Publico;

use App\Http\Controllers\Controller;
use App\Http\Requests\Publico\ReservaRequest;
use App\Models\Cita;
use App\Services\ReservaService;
use App\Services\SitioService;
use App\Support\HuecoOcupado;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class ReservaController extends Controller
{
    public function __construct(
        private ReservaService $reservas,
        private SitioService $sitio
    ) {
    }

    /** Dias del mes con hueco, para pintar el calendario. */
    public function disponibilidad(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'modalidad' => ['required', Rule::in(array_keys(Cita::MODALIDADES))],
            'mes' => ['required', 'date_format:Y-m'],
        ]);

        $mes = Carbon::createFromFormat('Y-m', $datos['mes'])->startOfMonth();

        return response()->json([
            'mes' => $mes->format('Y-m'),
            'titulo' => ucfirst($mes->translatedFormat('F Y')),
            'anterior' => $this->mesNavegable($mes->copy()->subMonth()),
            'siguiente' => $this->mesNavegable($mes->copy()->addMonth()),
            'dias' => $this->reservas->diasDelMes($datos['modalidad'], $mes),
        ]);
    }

    /** Horas libres de un dia concreto. */
    public function huecos(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'modalidad' => ['required', Rule::in(array_keys(Cita::MODALIDADES))],
            'fecha' => ['required', 'date_format:Y-m-d'],
        ]);

        $fecha = Carbon::createFromFormat('Y-m-d', $datos['fecha'])->startOfDay();

        return response()->json([
            'fecha' => $fecha->toDateString(),
            'titulo' => ucfirst($fecha->translatedFormat('l j \d\e F')),
            'duracion' => $this->reservas->configuracion()->{'duracion_' . $datos['modalidad']},
            'huecos' => $this->reservas->huecos($datos['modalidad'], $fecha),
        ]);
    }

    public function store(ReservaRequest $request): RedirectResponse
    {
        try {
            $cita = $this->reservas->reservar($request->validated());
        } catch (HuecoOcupado $e) {
            return back()
                ->withInput()
                ->withErrors(['hora' => $e->getMessage()]);
        }

        $this->reservas->avisar($cita);

        return redirect()
            ->to($this->destino())
            ->with('reserva', [
                'id' => $cita->id,
                'dia' => ucfirst($cita->fecha->translatedFormat('l j \d\e F')),
                'hora' => $cita->hora_corta,
                'modalidad' => $cita->modalidad_legible,
                'duracion' => $cita->duracion,
                'google' => $this->reservas->enlaceGoogleCalendar(
                    $cita,
                    $this->sitio->nombre(),
                    $this->sitio->perfil()->direccion
                ),
            ]);
    }

    /** En landing la reserva vive en la portada; en multipagina, en su pagina. */
    private function destino(): string
    {
        return $this->sitio->esLanding()
            ? route('home') . '#pide-cita'
            : route('publico.pide-cita');
    }

    /** null cuando el mes cae fuera de la ventana que se puede reservar. */
    private function mesNavegable(Carbon $mes): ?string
    {
        $clave = $mes->format('Y-m');

        if ($clave < now()->format('Y-m') || $clave > $this->reservas->mesLimite()) {
            return null;
        }

        return $clave;
    }
}
