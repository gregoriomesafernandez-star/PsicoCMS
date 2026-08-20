<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Panel\PasswordRequest;
use App\Http\Requests\Panel\PerfilRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PerfilController extends Controller
{
    private const DISCO = 'public';

    private const CARPETA = 'avatares';

    public function index(): View
    {
        return view('panel.perfil.index', [
            'psicologa' => Auth::user(),
            'pestana' => session('pestana', request('pestana', 'datos')),
        ]);
    }

    public function update(PerfilRequest $request): RedirectResponse
    {
        $psicologa = $request->user();
        $psicologa->fill($request->safe()->except('avatar'));

        if ($request->hasFile('avatar')) {
            $anterior = $psicologa->avatar;
            $psicologa->avatar = $request->file('avatar')->store(self::CARPETA, self::DISCO);
            $this->borrarArchivo($anterior);
        }

        $psicologa->save();

        return back()->with('exito', 'Tus datos se han guardado.')->with('pestana', 'datos');
    }

    public function borrarAvatar(): RedirectResponse
    {
        $psicologa = Auth::user();
        $anterior = $psicologa->avatar;

        $psicologa->avatar = null;
        $psicologa->save();

        $this->borrarArchivo($anterior);

        return back()->with('exito', 'Foto eliminada.')->with('pestana', 'datos');
    }

    public function password(PasswordRequest $request): RedirectResponse
    {
        $psicologa = $request->user();
        $psicologa->password = Hash::make($request->validated('password'));
        $psicologa->save();

        // La sesion actual se mantiene; las demas dejan de ser validas.
        Auth::logoutOtherDevices($request->validated('password'));
        $request->session()->regenerate();

        return back()->with('exito', 'Contrasena cambiada. Usala la proxima vez que entres.')
            ->with('pestana', 'password');
    }

    private function borrarArchivo(?string $ruta): void
    {
        if ($ruta && Storage::disk(self::DISCO)->exists($ruta)) {
            Storage::disk(self::DISCO)->delete($ruta);
        }
    }
}
