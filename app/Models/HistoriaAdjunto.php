<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistoriaAdjunto extends Model
{
    protected $table = 'historia_adjuntos';

    protected $fillable = [
        'historia_id',
        'ruta',
        'nombre_original',
        'mime',
        'tamano',
    ];

    protected $casts = [
        'tamano' => 'integer',
    ];

    public function historia(): BelongsTo
    {
        return $this->belongsTo(Historia::class);
    }

    public function esImagen(): bool
    {
        return str_starts_with($this->mime, 'image/');
    }

    public function esPdf(): bool
    {
        return $this->mime === 'application/pdf';
    }

    public function getIconoAttribute(): string
    {
        if ($this->esPdf()) {
            return 'fa-file-pdf';
        }

        return $this->esImagen() ? 'fa-file-image' : 'fa-file';
    }

    public function getTipoLegibleAttribute(): string
    {
        if ($this->esPdf()) {
            return 'PDF';
        }

        return $this->esImagen() ? 'Imagen' : 'Archivo';
    }

    public function getTamanoLegibleAttribute(): string
    {
        $kilobytes = $this->tamano / 1024;

        return $kilobytes < 1024
            ? round($kilobytes) . ' KB'
            : round($kilobytes / 1024, 1) . ' MB';
    }
}
