<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Panel\FaqRequest;
use App\Models\Faq;
use App\Services\SanitizadorHtml;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FaqController extends Controller
{
    private const EJEMPLOS = [
        ['¿Cuanto dura una sesion?', '<p>Cada sesion dura aproximadamente 50 minutos. En la primera cita dedicamos algo mas de tiempo a conocernos y a entender que te trae a consulta.</p>'],
        ['¿Cada cuanto tiempo hay que venir?', '<p>Lo habitual es una sesion semanal al principio. A medida que avanza el proceso, las sesiones se van espaciando segun como te vayas encontrando.</p>'],
        ['¿La terapia online funciona igual que la presencial?', '<p>Si. La investigacion muestra resultados equivalentes en la mayoria de los casos. Lo importante es que puedas conectarte desde un sitio tranquilo donde te sientas comoda o comodo para hablar.</p>'],
        ['¿Lo que cuente en consulta es confidencial?', '<p>Absolutamente. Todo lo que se habla en sesion esta protegido por el secreto profesional. Solo puede romperse en los casos que marca la ley, como un riesgo grave para tu vida o la de otra persona.</p>'],
        ['¿Que pasa si no puedo acudir a una cita?', '<p>Avisame con al menos 24 horas de antelacion y buscamos otro hueco sin coste. Las cancelaciones con menos margen no siempre se pueden reprogramar.</p>'],
        ['¿Cuanto dura el proceso completo?', '<p>Depende de cada persona y de lo que quiera trabajar. Tras las primeras sesiones de evaluacion podremos hacernos una idea mas realista y la iremos revisando juntos.</p>'],
    ];

    public function __construct(private SanitizadorHtml $sanitizador)
    {
    }

    public function index(Request $request): View
    {
        $filtros = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $faqs = Faq::buscar($filtros['q'] ?? null)->ordenadas()->get();

        return view('panel.faqs.index', [
            'faqs' => $faqs,
            'filtros' => $filtros,
            'previsualizacion' => Faq::activas()->ordenadas()->get(),
            'resumen' => [
                'total' => Faq::count(),
                'activas' => Faq::activas()->count(),
                'ocultas' => Faq::where('activa', false)->count(),
            ],
        ]);
    }

    public function store(FaqRequest $request): RedirectResponse
    {
        Faq::create($this->campos($request) + ['orden' => Faq::siguienteOrden()]);

        return back()->with('exito', 'Pregunta creada correctamente.');
    }

    public function update(FaqRequest $request, Faq $faq): RedirectResponse
    {
        $faq->update($this->campos($request));

        return back()->with('exito', 'Pregunta actualizada correctamente.');
    }

    public function activa(Faq $faq): RedirectResponse
    {
        $faq->update(['activa' => ! $faq->activa]);

        return back()->with('exito', $faq->activa
            ? 'La pregunta ya se ve en tu web.'
            : 'La pregunta se ha ocultado de tu web.');
    }

    public function mover(Faq $faq, string $direccion): RedirectResponse
    {
        Faq::renumerar();
        $faq->refresh();

        $vecina = Faq::where('orden', $direccion === 'arriba' ? '<' : '>', $faq->orden)
            ->when($direccion === 'arriba',
                fn ($q) => $q->orderByDesc('orden'),
                fn ($q) => $q->orderBy('orden'))
            ->first();

        if (! $vecina) {
            return back();
        }

        $posicion = $faq->orden;
        $faq->update(['orden' => $vecina->orden]);
        $vecina->update(['orden' => $posicion]);

        return back()->with('exito', 'Orden actualizado.');
    }

    public function destroy(Faq $faq): RedirectResponse
    {
        $faq->delete();
        Faq::renumerar();

        return back()->with('exito', 'Pregunta eliminada definitivamente.');
    }

    public function ejemplos(): RedirectResponse
    {
        if (Faq::exists()) {
            return back()->with('error', 'Solo se pueden cargar los ejemplos cuando no tienes ninguna pregunta.');
        }

        foreach (self::EJEMPLOS as $indice => [$pregunta, $respuesta]) {
            Faq::create([
                'pregunta' => $pregunta,
                'respuesta' => $respuesta,
                'orden' => $indice + 1,
                'activa' => true,
            ]);
        }

        return back()->with('exito', count(self::EJEMPLOS) . ' preguntas de ejemplo anadidas. Editalas a tu gusto.');
    }

    private function campos(FaqRequest $request): array
    {
        $datos = $request->validated();

        return [
            'pregunta' => $datos['pregunta'],
            'respuesta' => $this->sanitizador->limpiar($datos['respuesta'] ?? null) ?: null,
            'activa' => $request->boolean('activa'),
        ];
    }
}
