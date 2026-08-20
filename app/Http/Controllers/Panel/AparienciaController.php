<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Panel\AparienciaRequest;
use App\Services\AparienciaService;
use Illuminate\Http\RedirectResponse;

class AparienciaController extends Controller
{
    public function __construct(private AparienciaService $apariencia)
    {
    }

    public function update(AparienciaRequest $request): RedirectResponse
    {
        $this->apariencia->guardar(
            $request->validated('tema'),
            $request->validated('color')
        );

        return back()->with('exito', 'Apariencia guardada. Se vera asi cada vez que entres.');
    }
}
