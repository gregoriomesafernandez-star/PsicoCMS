<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Panel\PlanRequest;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;

class PlanController extends Controller
{
    public function store(PlanRequest $request): RedirectResponse
    {
        Plan::create($this->campos($request) + ['orden' => Plan::siguienteOrden()]);

        return $this->vuelta('Plan creado correctamente.');
    }

    public function update(PlanRequest $request, Plan $plan): RedirectResponse
    {
        $plan->update($this->campos($request));

        return $this->vuelta('Plan actualizado correctamente.');
    }

    public function mover(Plan $plan, string $direccion): RedirectResponse
    {
        if (! Plan::intercambiar($plan, $direccion)) {
            return back()->with('pestana', 'planes');
        }

        return $this->vuelta('Orden actualizado.');
    }

    public function destroy(Plan $plan): RedirectResponse
    {
        $plan->delete();
        Plan::renumerar();

        return $this->vuelta('Plan eliminado definitivamente.');
    }

    private function campos(PlanRequest $request): array
    {
        $datos = $request->validated();

        return [
            'nombre' => $datos['nombre'],
            'modalidad' => $datos['modalidad'],
            'precio' => $datos['precio'],
            'descripcion' => $datos['descripcion'] ?? null,
            'caracteristicas' => Plan::caracteristicasDesdeTexto($datos['caracteristicas'] ?? null),
            'destacado' => $request->boolean('destacado'),
        ];
    }

    private function vuelta(string $mensaje): RedirectResponse
    {
        return back()->with('exito', $mensaje)->with('pestana', 'planes');
    }
}
