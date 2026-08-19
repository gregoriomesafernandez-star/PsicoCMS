<?php

namespace App\Services;

use App\Models\Historia;
use App\Models\HistoriaAdjunto;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class HistoriaService
{
    public const DISCO = 'local';
    public const CARPETA = 'historias';

    public function guardarAdjuntos(Historia $historia, array $archivos): int
    {
        $guardados = 0;

        foreach ($archivos as $archivo) {
            if (! $archivo instanceof UploadedFile || ! $archivo->isValid()) {
                continue;
            }

            $historia->adjuntos()->create([
                'ruta' => $archivo->storeAs(
                    $this->carpetaDe($historia->paciente_id),
                    Str::uuid() . '.' . strtolower($archivo->getClientOriginalExtension()),
                    self::DISCO
                ),
                'nombre_original' => mb_substr($archivo->getClientOriginalName(), 0, 255),
                'mime' => $archivo->getMimeType() ?: $archivo->getClientMimeType(),
                'tamano' => $archivo->getSize() ?: 0,
            ]);

            $guardados++;
        }

        return $guardados;
    }

    public function borrarAdjunto(HistoriaAdjunto $adjunto): void
    {
        $this->borrarArchivo($adjunto->ruta);
        $adjunto->delete();
    }

    public function borrarAdjuntosPorId(Historia $historia, array $ids): int
    {
        $adjuntos = $historia->adjuntos()->whereIn('id', $ids)->get();

        $adjuntos->each(fn (HistoriaAdjunto $adjunto) => $this->borrarAdjunto($adjunto));

        return $adjuntos->count();
    }

    public function borrarHistoria(Historia $historia): void
    {
        $historia->adjuntos->each(fn (HistoriaAdjunto $adjunto) => $this->borrarArchivo($adjunto->ruta));

        $historia->adjuntos()->delete();
        $historia->forceDelete();
    }

    public function borrarArchivosDelPaciente(int $pacienteId): void
    {
        $carpeta = $this->carpetaDe($pacienteId);

        if (Storage::disk(self::DISCO)->exists($carpeta)) {
            Storage::disk(self::DISCO)->deleteDirectory($carpeta);
        }
    }

    public function carpetaDe(int $pacienteId): string
    {
        return self::CARPETA . '/' . $pacienteId;
    }

    private function borrarArchivo(?string $ruta): void
    {
        if ($ruta && Storage::disk(self::DISCO)->exists($ruta)) {
            Storage::disk(self::DISCO)->delete($ruta);
        }
    }
}
