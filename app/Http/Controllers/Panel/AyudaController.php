<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class AyudaController extends Controller
{
    /** Cada apartado del tutorial: ancla => [titulo, icono, ruta de la seccion]. */
    public const APARTADOS = [
        'primeros-pasos' => ['Primeros pasos', 'fa-flag-checkered', null],
        'disponibilidad' => ['Tu disponibilidad', 'fa-clock', 'panel.disponibilidad'],
        'citas' => ['Citas y calendario', 'fa-calendar-check', 'panel.citas.index'],
        'pacientes' => ['Pacientes', 'fa-users', 'panel.pacientes.index'],
        'historias' => ['Historias clinicas', 'fa-notes-medical', 'panel.historias.index'],
        'documentos' => ['Proteccion de datos', 'fa-file-shield', 'panel.documentos'],
        'blog' => ['Blog', 'fa-newspaper', 'panel.blog.index'],
        'web' => ['El contenido de tu web', 'fa-globe', 'panel.contenido'],
        'apariencia' => ['Temas, imagenes y apariencia', 'fa-palette', 'panel.temas.index'],
        'configuracion' => ['Avisos por email y tu perfil', 'fa-gear', 'panel.notificaciones'],
    ];

    public function __invoke(): View
    {
        return view('panel.ayuda.index', ['apartados' => self::APARTADOS]);
    }
}
