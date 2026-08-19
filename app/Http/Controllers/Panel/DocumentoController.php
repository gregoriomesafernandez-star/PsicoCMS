<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Panel\PlantillaDocumentoRequest;
use App\Models\Paciente;
use App\Models\PlantillaDocumento;
use App\Services\DocumentoService;
use App\Services\SanitizadorHtml;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DocumentoPdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DocumentoController extends Controller
{
    public function __construct(
        private DocumentoService $documentos,
        private SanitizadorHtml $sanitizador
    ) {
    }

    public function index(): View
    {
        return view('panel.documentos.index', [
            'plantilla' => PlantillaDocumento::proteccionDatos(),
            'grupos' => $this->documentos->placeholders(),
            'porDefecto' => PlantillaDocumento::contenidoPorDefecto(),
        ]);
    }

    public function update(PlantillaDocumentoRequest $request): RedirectResponse
    {
        PlantillaDocumento::proteccionDatos()->update([
            'contenido' => $this->sanitizador->limpiar($request->validated()['contenido']),
        ]);

        return redirect()
            ->route('panel.documentos')
            ->with('exito', 'Plantilla guardada correctamente.');
    }

    public function restaurar(): RedirectResponse
    {
        PlantillaDocumento::proteccionDatos()->update([
            'contenido' => PlantillaDocumento::contenidoPorDefecto(),
        ]);

        return redirect()
            ->route('panel.documentos')
            ->with('exito', 'Se ha restaurado la plantilla original.');
    }

    public function plantillaEnBlanco(): Response
    {
        return $this->pdf('Plantilla de proteccion de datos', null, 'proteccion-datos-plantilla.pdf');
    }

    public function documentoDelPaciente(Paciente $paciente): Response
    {
        return $this->pdf(
            'Proteccion de datos',
            $paciente,
            'proteccion-datos-' . Str::slug($paciente->nombre_completo) . '.pdf'
        );
    }

    private function pdf(string $titulo, ?Paciente $paciente, string $fichero): Response
    {
        $plantilla = PlantillaDocumento::proteccionDatos()->contenido;

        $documento = Pdf::loadView('panel.documentos.pdf', [
            'titulo' => $titulo,
            'contenido' => $this->documentos->resolver($plantilla, $paciente),
            'profesional' => $this->documentos->nombreProfesional(),
            'colegiado' => $this->documentos->colegiado(),
        ])->setPaper('a4');

        $this->dibujarPie($documento);

        return $documento->download($fichero);
    }

    /**
     * El pie va dibujado sobre el lienzo y no en el HTML: es la unica forma de saber cuantas
     * paginas tiene el documento. El `counter(pages)` de CSS que entiende dompdf devuelve 0.
     */
    private function dibujarPie(DocumentoPdf $documento): void
    {
        $documento->render();

        $dompdf = $documento->getDomPDF();
        $lienzo = $dompdf->getCanvas();
        $metricas = $dompdf->getFontMetrics();

        $fuente = $metricas->getFont('DejaVu Sans', 'normal');
        $tamano = 7.5;
        $gris = [0.60, 0.65, 0.69];
        $margen = 56.7;
        $base = $lienzo->get_height() - 52;
        $derecha = $lienzo->get_width() - $margen;

        $lienzo->page_line($margen, $base - 7, $derecha, $base - 7, [0.86, 0.88, 0.91], 0.5);

        $lienzo->page_text(
            $margen,
            $base,
            'Documento generado el ' . now()->format('d/m/Y'),
            $fuente,
            $tamano,
            $gris
        );

        $paginas = 'Pagina {PAGE_NUM} de {PAGE_COUNT}';
        $ancho = $metricas->getTextWidth('Pagina 99 de 99', $fuente, $tamano);

        $lienzo->page_text($derecha - $ancho, $base, $paginas, $fuente, $tamano, $gris);
    }
}
