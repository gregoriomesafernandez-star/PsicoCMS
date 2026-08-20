<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Panel\NotificacionRequest;
use App\Services\NotificacionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NotificacionController extends Controller
{
    public function __construct(private NotificacionService $notificaciones)
    {
    }

    public function index(): View
    {
        return view('panel.notificaciones.index', [
            'configuracion' => $this->notificaciones->configuracion(),
            'configurado' => $this->notificaciones->configurado(),
        ]);
    }

    public function update(NotificacionRequest $request): RedirectResponse
    {
        $this->notificaciones->guardar(
            $request->validated('email'),
            $request->validated('password'),
            $request->validated('nombre'),
            $request->boolean('avisar_citas')
        );

        return back()->with('exito', 'Configuracion guardada. Prueba a enviarte un email.');
    }

    public function probar(): RedirectResponse
    {
        [$correcto, $mensaje] = $this->notificaciones->probar();

        return back()->with($correcto ? 'exito' : 'error', $mensaje);
    }

    public function olvidar(): RedirectResponse
    {
        $this->notificaciones->olvidar();

        return back()->with('exito', 'Datos borrados. Ya no se enviaran avisos por email.');
    }
}
