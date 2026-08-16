<?php

namespace Database\Seeders;

use App\Models\PlantillaDocumento;
use Illuminate\Database\Seeder;

class PlantillaDocumentoSeeder extends Seeder
{
    public function run(): void
    {
        $contenido = <<<'HTML'
<h1>Consentimiento informado y proteccion de datos</h1>
<p>En cumplimiento del Reglamento (UE) 2016/679 de Proteccion de Datos, se informa a
<strong>{{paciente_nombre}}</strong>, con DNI <strong>{{paciente_dni}}</strong> y telefono
<strong>{{paciente_telefono}}</strong>, de lo siguiente:</p>
<h2>Responsable del tratamiento</h2>
<p>{{psicologa_nombre}}, colegiada n.o {{num_colegiado}}.</p>
<h2>Finalidad</h2>
<p>Los datos recogidos se utilizan unicamente para la prestacion del servicio de atencion
psicologica y el seguimiento clinico de las sesiones.</p>
<h2>Conservacion</h2>
<p>Los datos se conservaran durante el tiempo legalmente exigido para la documentacion clinica.</p>
<h2>Derechos</h2>
<p>Puede ejercer sus derechos de acceso, rectificacion, supresion, limitacion, portabilidad y
oposicion dirigiendose al responsable del tratamiento.</p>
<p>Fecha: {{fecha}}</p>
<p>Firma del paciente:</p>
HTML;

        PlantillaDocumento::firstOrCreate(
            ['nombre' => 'Proteccion de datos'],
            ['contenido' => $contenido]
        );
    }
}
