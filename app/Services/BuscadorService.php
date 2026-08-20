<?php

namespace App\Services;

use App\Models\Articulo;
use App\Models\Cita;
use App\Models\Especialidad;
use App\Models\Faq;
use App\Models\Historia;
use App\Models\Paciente;
use App\Models\Servicio;
use Illuminate\Support\Collection;

class BuscadorService
{
    public const MINIMO = 2;

    private const POR_TIPO = 8;

    /** tipo => [nombre en plural, icono] */
    public const TIPOS = [
        'pacientes' => ['Pacientes', 'fa-users'],
        'citas' => ['Citas', 'fa-calendar-check'],
        'historias' => ['Historias clinicas', 'fa-notes-medical'],
        'articulos' => ['Articulos', 'fa-newspaper'],
        'faqs' => ['Preguntas frecuentes', 'fa-circle-question'],
        'servicios' => ['Servicios', 'fa-hand-holding-heart'],
        'especialidades' => ['Especialidades', 'fa-brain'],
    ];

    public function buscar(?string $texto, ?string $tipo = null): array
    {
        if (! $this->valida($texto)) {
            return [];
        }

        $tipos = $tipo && isset(self::TIPOS[$tipo]) ? [$tipo] : array_keys(self::TIPOS);
        $resultados = [];

        foreach ($tipos as $clave) {
            $encontrados = $this->{'buscar' . ucfirst($clave)}($texto);

            if ($encontrados->isNotEmpty()) {
                $resultados[$clave] = $encontrados;
            }
        }

        return $resultados;
    }

    public function valida(?string $texto): bool
    {
        return filled($texto) && mb_strlen(trim($texto)) >= self::MINIMO;
    }

    public function total(array $resultados): int
    {
        return array_sum(array_map(fn (Collection $lista) => $lista->count(), $resultados));
    }

    /** Cuantos hay de cada tipo, para los filtros de la pantalla. */
    public function contadores(array $resultados): array
    {
        $contadores = [];

        foreach (self::TIPOS as $clave => [$nombre, $icono]) {
            $contadores[$clave] = isset($resultados[$clave]) ? $resultados[$clave]->count() : 0;
        }

        return $contadores;
    }

    private function buscarPacientes(string $texto): Collection
    {
        return Paciente::buscar($texto)->orderBy('nombre')->limit(self::POR_TIPO)->get()
            ->map(fn (Paciente $paciente) => [
                'titulo' => $paciente->nombre_completo ?? trim($paciente->nombre . ' ' . $paciente->apellidos),
                'detalle' => $paciente->telefono . ($paciente->email ? ' · ' . $paciente->email : ''),
                'url' => route('panel.pacientes.show', $paciente),
                'badge' => ucfirst((string) $paciente->estado),
            ]);
    }

    /** Una cita se encuentra por su paciente, por su fecha o por su motivo. */
    private function buscarCitas(string $texto): Collection
    {
        $fecha = $this->fechaDelTexto($texto);

        return Cita::with('paciente')
            ->where(function ($consulta) use ($texto, $fecha) {
                $consulta->whereHas('paciente', fn ($sub) => $sub->buscar($texto))
                    ->orWhere('motivo', 'like', "%{$texto}%")
                    ->orWhere('notas', 'like', "%{$texto}%");

                // whereDate y no una comparacion directa: cada motor guarda la
                // fecha a su manera (con hora o sin ella).
                if ($fecha) {
                    $consulta->orWhereDate('fecha', $fecha);
                }
            })
            ->orderByDesc('fecha')
            ->limit(self::POR_TIPO)
            ->get()
            ->map(fn (Cita $cita) => [
                'titulo' => $cita->paciente?->nombre ?? 'Cita',
                'detalle' => $this->detalleDeCita($cita, $texto),
                'url' => route('panel.citas.index', ['q' => $cita->paciente?->telefono]),
                'badge' => ucfirst(str_replace('_', ' ', (string) $cita->estado)),
                // Con esto el resultado abre el mismo modal de detalle que el
                // listado y el calendario, en vez de llevar a otra pantalla.
                'cita' => $this->datosDeCita($cita),
            ]);
    }

    /** Mismas claves que consume PsicoCitaDetalle desde FASE 7. */
    private function datosDeCita(Cita $cita): array
    {
        return [
            'id' => $cita->id,
            'paciente_id' => $cita->paciente_id,
            'paciente_nombre' => $cita->paciente?->nombre,
            'paciente_apellidos' => $cita->paciente?->apellidos,
            'paciente_completo' => $cita->paciente?->nombre_completo,
            'paciente_telefono' => $cita->paciente?->telefono,
            'paciente_email' => $cita->paciente?->email,
            'paciente_iniciales' => $cita->paciente?->iniciales,
            'modalidad' => $cita->modalidad,
            'modalidad_legible' => $cita->modalidad_legible,
            'fecha' => $cita->fecha->toDateString(),
            'hora_inicio' => $cita->hora_corta,
            'hora_fin' => $cita->hora_fin_corta,
            'duracion' => $cita->duracion,
            'estado' => $cita->estado,
            'estado_legible' => $cita->estado_legible,
            'tono' => $cita->tono_estado,
            'motivo' => $cita->motivo,
            'notas' => $cita->notas,
        ];
    }

    /** Si el motivo es lo que coincide, se ensena; si no, el dia y la hora. */
    private function detalleDeCita(Cita $cita, string $texto): string
    {
        $cuando = $cita->fecha->format('d/m/Y') . ' a las ' . substr((string) $cita->hora_inicio, 0, 5)
            . ' · ' . ucfirst((string) $cita->modalidad);

        $motivo = trim(strip_tags((string) $cita->motivo));

        if ($motivo !== '' && stripos($motivo, $texto) !== false) {
            return $cuando . ' · ' . $motivo;
        }

        return $cuando;
    }

    /**
     * Reconoce las formas habituales de escribir una fecha. Sin dia y mes
     * explicitos devuelve null, para no confundir un numero con una fecha.
     */
    public function fechaDelTexto(string $texto): ?string
    {
        $texto = trim($texto);

        if (! preg_match('#^(\d{1,4})[/\-. ](\d{1,2})(?:[/\-. ](\d{2,4}))?$#', $texto, $partes)) {
            return null;
        }

        [, $primero, $segundo, $tercero] = $partes + [3 => null];

        // 2026-09-24
        if (strlen($primero) === 4) {
            [$anio, $mes, $dia] = [$primero, $segundo, $tercero ?? 1];
        } else {
            // 24/09/2026, 24-09-26 o 24/09 (se asume el ano en curso)
            [$dia, $mes] = [$primero, $segundo];
            $anio = $tercero ?? date('Y');

            if (strlen((string) $anio) === 2) {
                $anio = '20' . $anio;
            }
        }

        return checkdate((int) $mes, (int) $dia, (int) $anio)
            ? sprintf('%04d-%02d-%02d', $anio, $mes, $dia)
            : null;
    }

    /**
     * El contenido de las historias esta cifrado, asi que solo se puede buscar
     * por titulo y por el paciente al que pertenecen.
     */
    private function buscarHistorias(string $texto): Collection
    {
        return Historia::with('paciente')
            ->where(function ($consulta) use ($texto) {
                $consulta->where('titulo', 'like', "%{$texto}%")
                    ->orWhereHas('paciente', fn ($sub) => $sub->buscar($texto));
            })
            ->orderByDesc('fecha_sesion')
            ->limit(self::POR_TIPO)
            ->get()
            ->map(fn (Historia $historia) => [
                'titulo' => $historia->titulo_legible,
                'detalle' => ($historia->paciente?->nombre ?? 'Sin paciente')
                    . ' · ' . $historia->fecha_sesion->format('d/m/Y'),
                'url' => route('panel.historias.show', $historia),
                'badge' => null,
            ]);
    }

    private function buscarArticulos(string $texto): Collection
    {
        return Articulo::where(function ($consulta) use ($texto) {
            $consulta->where('titulo', 'like', "%{$texto}%")
                ->orWhere('extracto', 'like', "%{$texto}%")
                ->orWhere('contenido', 'like', "%{$texto}%");
        })
            ->orderByDesc('publicado_at')
            ->limit(self::POR_TIPO)
            ->get()
            ->map(fn (Articulo $articulo) => [
                'titulo' => $articulo->titulo,
                'detalle' => $articulo->extracto ?: 'Sin extracto',
                'url' => route('panel.blog.edit', $articulo),
                'badge' => ucfirst((string) $articulo->estado),
            ]);
    }

    private function buscarFaqs(string $texto): Collection
    {
        return Faq::buscar($texto)->ordenadas()->limit(self::POR_TIPO)->get()
            ->map(fn (Faq $faq) => [
                'titulo' => $faq->pregunta,
                'detalle' => strip_tags((string) $faq->respuesta) ?: 'Sin respuesta',
                'url' => route('panel.faqs.index', ['q' => $faq->pregunta]),
                'badge' => $faq->activa ? 'Visible' : 'Oculta',
            ]);
    }

    private function buscarServicios(string $texto): Collection
    {
        return Servicio::buscar($texto)->ordenados()->limit(self::POR_TIPO)->get()
            ->map(fn (Servicio $servicio) => [
                'titulo' => $servicio->titulo,
                'detalle' => strip_tags((string) $servicio->descripcion) ?: 'Sin descripcion',
                'url' => route('panel.servicios.index', ['q' => $servicio->titulo]),
                'badge' => $servicio->activo ? 'Visible' : 'Oculto',
            ]);
    }

    private function buscarEspecialidades(string $texto): Collection
    {
        return Especialidad::buscar($texto)->ordenados()->limit(self::POR_TIPO)->get()
            ->map(fn (Especialidad $especialidad) => [
                'titulo' => $especialidad->nombre,
                'detalle' => strip_tags((string) $especialidad->descripcion) ?: 'Sin descripcion',
                'url' => route('panel.especialidades.index', ['q' => $especialidad->nombre]),
                'badge' => $especialidad->activo ? 'Visible' : 'Oculta',
            ]);
    }
}
