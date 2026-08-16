<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\AccesoRequest;
use App\Models\PerfilPublico;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AccesoController extends Controller
{
    public function mostrar(): View
    {
        return view('auth.acceso', [
            'perfil' => PerfilPublico::first(),
        ]);
    }

    public function acceder(AccesoRequest $request): RedirectResponse
    {
        $request->autenticar();

        $request->session()->regenerate();

        return redirect()->intended('/panel-psicologa');
    }

    public function salir(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('acceso')->with('exito', 'Has cerrado sesion correctamente.');
    }
}
