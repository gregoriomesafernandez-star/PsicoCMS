<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Panel\PeriodoVacacionesRequest;
use App\Models\PeriodoVacaciones;
use Illuminate\Http\RedirectResponse;

class VacacionesController extends Controller
{
    public function store(PeriodoVacacionesRequest $request): RedirectResponse
    {
        PeriodoVacaciones::create($request->validated());

        return back()->with('exito', 'Periodo de vacaciones anadido.');
    }

    public function destroy(PeriodoVacaciones $periodo): RedirectResponse
    {
        $periodo->delete();

        return back()->with('exito', 'Periodo de vacaciones eliminado.');
    }
}
