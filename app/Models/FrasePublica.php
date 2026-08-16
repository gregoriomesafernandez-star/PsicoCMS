<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FrasePublica extends Model
{
    protected $table = 'frases_publicas';

    protected $fillable = [
        'clave',
        'seccion',
        'texto_defecto',
        'texto_personalizado',
    ];

    public function getTextoAttribute(): ?string
    {
        return $this->texto_personalizado ?: $this->texto_defecto;
    }
}
