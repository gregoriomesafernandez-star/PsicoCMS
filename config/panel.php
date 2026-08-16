<?php

return [

    'menu' => [

        'directos' => [
            ['ruta' => 'panel.inicio', 'titulo' => 'Inicio', 'icono' => 'fa-house'],
            ['ruta' => 'panel.citas.index', 'titulo' => 'Citas', 'icono' => 'fa-calendar-check'],
            ['ruta' => 'panel.calendario', 'titulo' => 'Calendario', 'icono' => 'fa-calendar-days'],
            ['ruta' => 'panel.pacientes.index', 'titulo' => 'Pacientes', 'icono' => 'fa-users'],
            ['ruta' => 'panel.historias.index', 'titulo' => 'Historias', 'icono' => 'fa-notes-medical'],
            ['ruta' => 'panel.disponibilidad', 'titulo' => 'Disponibilidad', 'icono' => 'fa-clock'],
        ],

        'grupos' => [
            [
                'titulo' => 'Gestion web',
                'icono' => 'fa-globe',
                'elementos' => [
                    ['ruta' => 'panel.blog.index', 'titulo' => 'Blog', 'icono' => 'fa-newspaper'],
                    ['ruta' => 'panel.categorias.index', 'titulo' => 'Categorias', 'icono' => 'fa-tags'],
                    ['ruta' => 'panel.faqs.index', 'titulo' => 'Preguntas frecuentes', 'icono' => 'fa-circle-question'],
                    ['ruta' => 'panel.servicios.index', 'titulo' => 'Servicios', 'icono' => 'fa-hand-holding-heart'],
                    ['ruta' => 'panel.especialidades.index', 'titulo' => 'Especialidades', 'icono' => 'fa-brain'],
                    ['ruta' => 'panel.contenido', 'titulo' => 'Contenido de la web', 'icono' => 'fa-file-lines'],
                    ['ruta' => 'panel.frases', 'titulo' => 'Frases publicas', 'icono' => 'fa-quote-left'],
                    ['ruta' => 'panel.imagenes', 'titulo' => 'Imagenes', 'icono' => 'fa-images'],
                    ['ruta' => 'panel.temas.index', 'titulo' => 'Temas', 'icono' => 'fa-palette'],
                ],
            ],
            [
                'titulo' => 'Configuracion',
                'icono' => 'fa-gear',
                'elementos' => [
                    ['ruta' => 'panel.redes', 'titulo' => 'Redes sociales', 'icono' => 'fa-share-nodes'],
                    ['ruta' => 'panel.notificaciones', 'titulo' => 'Email y notificaciones', 'icono' => 'fa-envelope'],
                    ['ruta' => 'panel.documentos', 'titulo' => 'Documentos', 'icono' => 'fa-file-shield'],
                    ['ruta' => 'panel.modulos', 'titulo' => 'Modulos', 'icono' => 'fa-toggle-on'],
                    ['ruta' => 'panel.perfil', 'titulo' => 'Mi perfil', 'icono' => 'fa-user-gear'],
                ],
            ],
        ],

    ],

];
