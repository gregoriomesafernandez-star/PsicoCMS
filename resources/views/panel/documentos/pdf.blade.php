<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $titulo }}</title>

    <style>
        @page {
            margin: 32mm 20mm 26mm 20mm;
        }

        body {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 10pt;
            line-height: 1.65;
            color: #26313d;
            margin: 0;
        }

        /* ---------- Cabecera y pie repetidos en cada pagina ---------- */

        .cabecera {
            position: fixed;
            top: -22mm;
            left: 0;
            right: 0;
            height: 14mm;
        }

        .cabecera__marca {
            font-size: 11pt;
            font-weight: bold;
            color: #3d5a80;
            letter-spacing: 0.2pt;
        }

        .cabecera__datos {
            font-size: 8pt;
            color: #7b8794;
        }

        .cabecera__regla {
            border-bottom: 1.6pt solid #3d5a80;
            margin-top: 2.5mm;
        }

        /* ---------- Contenido escrito por la profesional ---------- */

        .contenido h1 {
            font-size: 13pt;
            color: #2f4763;
            margin: 0 0 5mm 0;
            padding: 0 0 2mm 0;
            border-bottom: 1pt solid #dbe1e8;
            text-transform: uppercase;
            letter-spacing: 0.3pt;
        }

        .contenido h2 {
            font-size: 10.5pt;
            color: #3d5a80;
            margin: 6mm 0 1.5mm 0;
            padding-bottom: 1mm;
            border-bottom: 0.5pt solid #dbe1e8;
            text-transform: uppercase;
            letter-spacing: 0.3pt;
        }

        .contenido h3,
        .contenido h4 {
            font-size: 10pt;
            color: #3d5a80;
            margin: 4mm 0 1.5mm 0;
        }

        .contenido p {
            margin: 0 0 2.5mm 0;
            text-align: justify;
        }

        .contenido ul,
        .contenido ol {
            margin: 0 0 3mm 0;
            padding-left: 7mm;
        }

        .contenido li {
            margin-bottom: 1.2mm;
            text-align: justify;
        }

        .contenido table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 3mm;
        }

        .contenido th,
        .contenido td {
            border: 0.5pt solid #dbe1e8;
            padding: 2mm;
            text-align: left;
        }

        .contenido th {
            background-color: #f4f6fa;
            color: #2f4763;
        }

        .contenido blockquote {
            margin: 0 0 3mm 0;
            padding: 2mm 4mm;
            background-color: #f8fafc;
            border-left: 2pt solid #cbd4e1;
            color: #52606d;
        }

        .contenido img {
            max-width: 100%;
        }

        .contenido strong {
            color: #1f2933;
        }

        /* ---------- Firmas ---------- */

        /* Van dentro de .contenido porque los escribe la profesional en la plantilla. */

        .contenido .firmas {
            margin-top: 10mm;
            page-break-inside: avoid;
        }

        .contenido .firmas__lugar {
            margin-bottom: 12mm;
            font-size: 10pt;
        }

        .contenido .firmas__tabla {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
        }

        .contenido .firmas__tabla td {
            width: 50%;
            border: none;
            padding: 0 4mm;
            vertical-align: top;
        }

        .contenido .firmas__hueco {
            height: 20mm;
        }

        .contenido .firmas__linea {
            border-top: 0.7pt solid #52606d;
            padding-top: 2mm;
        }

        .contenido .firmas__rol {
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3pt;
            color: #3d5a80;
            margin: 0 0 1mm 0;
        }

        .contenido .firmas__dato {
            font-size: 8.5pt;
            color: #52606d;
            margin: 0;
        }

        .contenido .aviso-legal {
            margin-top: 10mm;
            padding-top: 3mm;
            border-top: 0.5pt solid #dbe1e8;
            font-size: 7.5pt;
            color: #9aa5b1;
            text-align: justify;
        }
    </style>
</head>
<body>

    <header class="cabecera">
        <div class="cabecera__marca">{{ $profesional }}</div>
        <div class="cabecera__datos">
            Psicologia
            @if ($colegiado)
                &nbsp;|&nbsp; Colegiada n.&ordm; {{ $colegiado }}
            @endif
        </div>
        <div class="cabecera__regla"></div>
    </header>

    {{-- El pie lo dibuja el controlador con la API de dompdf: es la unica forma de saber
         el numero total de paginas (counter(pages) de CSS devuelve 0). --}}

    <main class="contenido">
        {!! $contenido !!}
    </main>

</body>
</html>
