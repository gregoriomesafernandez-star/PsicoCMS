<?php

namespace App\Http\Controllers\Instalacion;

use App\Http\Controllers\Controller;
use App\Http\Requests\Instalacion\BaseDatosRequest;
use App\Http\Requests\Instalacion\CuentaRequest;
use App\Http\Requests\Instalacion\PerfilRequest;
use App\Http\Requests\Instalacion\ServiciosRequest;
use App\Http\Requests\Instalacion\TemaRequest;
use App\Models\ConfiguracionDisponibilidad;
use App\Models\Especialidad;
use App\Models\PerfilPublico;
use App\Models\Plan;
use App\Models\Psicologa;
use App\Models\Servicio;
use App\Services\AjustesService;
use App\Services\InstaladorService;
use App\Services\TemaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class InstalacionController extends Controller
{
    private const TOTAL_PASOS = 5;

    private const CLAVE_SESION = 'instalacion';

    public function __construct(
        private InstaladorService $instalador,
        private TemaService $temas,
        private AjustesService $ajustes
    ) {
    }

    public function inicio(): RedirectResponse
    {
        return redirect()->route('instalacion.paso', ['paso' => $this->ultimoPasoDisponible()]);
    }

    public function paso(int $paso): View|RedirectResponse
    {
        if ($paso < 1 || $paso > self::TOTAL_PASOS) {
            return redirect()->route('instalacion.paso', ['paso' => 1]);
        }

        $ultimoDisponible = $this->ultimoPasoDisponible();

        if ($paso > $ultimoDisponible) {
            return redirect()->route('instalacion.paso', ['paso' => $ultimoDisponible]);
        }

        return view('instalacion.pasos.paso' . $paso, [
            'paso' => $paso,
            'totalPasos' => self::TOTAL_PASOS,
            'datos' => $this->datos(),
            'temas' => $this->temas->disponibles(),
        ]);
    }

    public function probarConexion(BaseDatosRequest $request): JsonResponse
    {
        try {
            $this->instalador->probarConexion($request->validated());
        } catch (RuntimeException $e) {
            return response()->json(['ok' => false, 'mensaje' => $e->getMessage()], 422);
        }

        return response()->json(['ok' => true, 'mensaje' => 'Conexion correcta con el servidor MySQL.']);
    }

    public function guardarBaseDatos(BaseDatosRequest $request): RedirectResponse
    {
        $datos = $request->validated();

        try {
            $this->instalador->crearBaseDatos($datos);
            $this->instalador->aplicarConexionEnRuntime($datos);
            $this->instalador->migrar();
            $this->instalador->sembrar();
        } catch (RuntimeException $e) {
            return back()->withInput()->withErrors(['db_host' => $e->getMessage()]);
        } catch (Throwable $e) {
            return back()->withInput()->withErrors([
                'db_host' => 'No se han podido preparar las tablas: ' . $e->getMessage(),
            ]);
        }

        $this->guardarEnSesion(['base_datos' => $datos, 'bd_lista' => true]);
        $request->session()->save();

        $this->instalador->escribirEnv($datos);
        $this->instalador->limpiarCaches();

        return redirect()->route('instalacion.paso', ['paso' => 2]);
    }

    public function guardarCuenta(CuentaRequest $request): RedirectResponse
    {
        $datos = $request->validated();

        $this->guardarEnSesion([
            'cuenta' => [
                'nombre' => $datos['nombre'],
                'apellidos' => $datos['apellidos'] ?? null,
                'email' => $datos['email'],
                'telefono' => $this->limpiarTelefono($datos['telefono']),
                'password' => Hash::make($datos['password']),
            ],
        ]);

        return redirect()->route('instalacion.paso', ['paso' => 3]);
    }

    public function guardarPerfil(PerfilRequest $request): RedirectResponse
    {
        $this->guardarEnSesion(['perfil' => $request->validated()]);

        return redirect()->route('instalacion.paso', ['paso' => 4]);
    }

    public function guardarServicios(ServiciosRequest $request): RedirectResponse
    {
        $this->guardarEnSesion(['catalogo' => $request->validated()]);

        return redirect()->route('instalacion.paso', ['paso' => 5]);
    }

    public function finalizar(TemaRequest $request): RedirectResponse
    {
        $sesion = $this->datos();

        if (empty($sesion['bd_lista']) || empty($sesion['cuenta'])) {
            return redirect()->route('instalacion.paso', ['paso' => 1]);
        }

        $datos = $request->validated();
        $rutaFoto = null;

        if ($request->hasFile('foto')) {
            $rutaFoto = $request->file('foto')->store('perfil', 'public');
        }

        try {
            $psicologa = DB::transaction(function () use ($sesion, $datos, $rutaFoto) {
                $psicologa = Psicologa::create($sesion['cuenta']);

                $perfil = PerfilPublico::actual();
                $perfil->fill($sesion['perfil'] ?? []);
                $perfil->nombre_publico = trim($sesion['cuenta']['nombre'] . ' ' . ($sesion['cuenta']['apellidos'] ?? ''));
                $perfil->horarios = $sesion['catalogo']['horarios'] ?? null;
                $perfil->foto = $rutaFoto;
                $perfil->meta_title = $perfil->nombre_publico . ' | Psicologa';
                $perfil->meta_description = $sesion['perfil']['eslogan'] ?? null;
                $perfil->save();

                $this->crearCatalogo($sesion['catalogo'] ?? []);

                ConfiguracionDisponibilidad::actual();

                return $psicologa;
            });
        } catch (Throwable $e) {
            if ($rutaFoto) {
                Storage::disk('public')->delete($rutaFoto);
            }

            return back()->withErrors([
                'tema' => 'No se han podido guardar los datos: ' . $e->getMessage(),
            ]);
        }

        $this->ajustes->guardarVarios([
            'tema_activo' => $datos['tema'],
            'modo_web' => $datos['modo'],
        ], 'apariencia');

        $this->ajustes->guardarVarios([
            'modulo_blog' => true,
            'modulo_reservas' => true,
            'modulo_faq' => true,
            'modulo_especialidades' => true,
            'modulo_planes' => true,
            'modulo_mapa' => true,
            'modulo_redes' => true,
        ], 'modulos');

        $this->instalador->marcarInstalado();

        $request->session()->forget(self::CLAVE_SESION);

        Auth::login($psicologa, true);
        $request->session()->regenerate();

        return redirect('/panel-psicologa')->with('exito', 'Instalacion completada. Bienvenida a PsicoCMS.');
    }

    private function crearCatalogo(array $catalogo): void
    {
        foreach ($catalogo['servicios'] ?? [] as $orden => $servicio) {
            Servicio::create([
                'titulo' => $servicio['titulo'],
                'descripcion' => $servicio['descripcion'] ?? null,
                'orden' => $orden,
            ]);
        }

        foreach ($catalogo['especialidades'] ?? [] as $orden => $especialidad) {
            Especialidad::create([
                'nombre' => $especialidad['nombre'],
                'descripcion' => $especialidad['descripcion'] ?? null,
                'orden' => $orden,
            ]);
        }

        foreach ($catalogo['planes'] ?? [] as $orden => $plan) {
            Plan::create([
                'nombre' => $plan['nombre'],
                'modalidad' => $plan['modalidad'],
                'precio' => $plan['precio'],
                'descripcion' => $plan['descripcion'] ?? null,
                'orden' => $orden,
            ]);
        }
    }

    private function ultimoPasoDisponible(): int
    {
        $datos = $this->datos();

        if (empty($datos['bd_lista'])) {
            return 1;
        }

        if (empty($datos['cuenta'])) {
            return 2;
        }

        if (empty($datos['perfil'])) {
            return 3;
        }

        if (empty($datos['catalogo'])) {
            return 4;
        }

        return 5;
    }

    private function datos(): array
    {
        return session(self::CLAVE_SESION, []);
    }

    private function guardarEnSesion(array $valores): void
    {
        session([self::CLAVE_SESION => array_merge($this->datos(), $valores)]);
    }

    private function limpiarTelefono(string $telefono): string
    {
        return preg_replace('/\D/', '', $telefono);
    }
}
