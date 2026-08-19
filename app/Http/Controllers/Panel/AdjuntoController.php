<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\HistoriaAdjunto;
use App\Services\HistoriaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdjuntoController extends Controller
{
    public function __construct(private HistoriaService $historias)
    {
    }

    public function ver(HistoriaAdjunto $adjunto): StreamedResponse
    {
        $this->comprobarQueExiste($adjunto);

        return Storage::disk(HistoriaService::DISCO)->response(
            $adjunto->ruta,
            $adjunto->nombre_original,
            ['Content-Type' => $adjunto->mime]
        );
    }

    public function descargar(HistoriaAdjunto $adjunto): StreamedResponse
    {
        $this->comprobarQueExiste($adjunto);

        return Storage::disk(HistoriaService::DISCO)->download($adjunto->ruta, $adjunto->nombre_original);
    }

    public function destroy(HistoriaAdjunto $adjunto): RedirectResponse
    {
        $historia = $adjunto->historia_id;
        $nombre = $adjunto->nombre_original;

        $this->historias->borrarAdjunto($adjunto);

        return redirect()
            ->route('panel.historias.show', $historia)
            ->with('exito', sprintf('Se ha eliminado "%s".', $nombre));
    }

    private function comprobarQueExiste(HistoriaAdjunto $adjunto): void
    {
        abort_unless(Storage::disk(HistoriaService::DISCO)->exists($adjunto->ruta), 404);
    }
}
