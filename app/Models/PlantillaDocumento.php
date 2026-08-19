<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlantillaDocumento extends Model
{
    public const PROTECCION_DATOS = 'Proteccion de datos';

    protected $table = 'plantillas_documento';

    protected $fillable = [
        'nombre',
        'contenido',
    ];

    public static function proteccionDatos(): self
    {
        return static::firstOrCreate(
            ['nombre' => self::PROTECCION_DATOS],
            ['contenido' => self::contenidoPorDefecto()]
        );
    }

    public static function contenidoPorDefecto(): string
    {
        return <<<'HTML'
        <h1>Consentimiento informado y proteccion de datos</h1>
        <p>En cumplimiento del Reglamento (UE) 2016/679 General de Proteccion de Datos y de la Ley
        Organica 3/2018, de 5 de diciembre, de Proteccion de Datos Personales y garantia de los derechos
        digitales, se informa a la persona interesada de lo que sigue.</p>
        <h2>1. Datos de la persona interesada</h2>
        <ul>
        <li>Nombre y apellidos: <strong>{{paciente_nombre}}</strong></li>
        <li>DNI / NIE: <strong>{{paciente_dni}}</strong></li>
        <li>Telefono: <strong>{{paciente_telefono}}</strong></li>
        <li>Correo electronico: <strong>{{paciente_email}}</strong></li>
        </ul>
        <h2>2. Responsable del tratamiento</h2>
        <ul>
        <li>Responsable: <strong>{{psicologa_nombre}}</strong>, colegiada n.o {{num_colegiado}}</li>
        <li>Lugar de consulta: {{consulta_lugar}}</li>
        <li>Direccion: {{consulta_direccion}}</li>
        <li>Contacto: {{consulta_telefono}} &ndash; {{consulta_email}}</li>
        </ul>
        <h2>3. Finalidad del tratamiento</h2>
        <p>Los datos facilitados se tratan con la unica finalidad de prestar el servicio de atencion
        psicologica solicitado: la gestion de las citas, la elaboracion y el mantenimiento de la historia
        clinica y el seguimiento de la evolucion a lo largo del proceso terapeutico.</p>
        <h2>4. Legitimacion</h2>
        <p>La base juridica del tratamiento es el consentimiento explicito de la persona interesada y la
        ejecucion de la relacion asistencial. Los datos de salud se consideran categoria especial y
        reciben la proteccion reforzada que les corresponde.</p>
        <h2>5. Confidencialidad y secreto profesional</h2>
        <p>Toda la informacion compartida durante las sesiones esta amparada por el secreto profesional
        recogido en el Codigo Deontologico de la profesion. Solo podra romperse en los supuestos
        legalmente previstos, en especial ante un riesgo grave para la vida o la integridad de la persona
        o de terceros, o por requerimiento judicial.</p>
        <h2>6. Conservacion</h2>
        <p>Los datos se conservaran mientras dure la relacion asistencial y, despues, durante el plazo
        legalmente exigido para la documentacion clinica. Transcurrido ese plazo se suprimiran de forma
        segura.</p>
        <h2>7. Destinatarios</h2>
        <p>No se cederan datos a terceros salvo obligacion legal. No se realizan transferencias
        internacionales ni decisiones automatizadas basadas en estos datos.</p>
        <h2>8. Derechos</h2>
        <p>La persona interesada puede ejercer en cualquier momento sus derechos de acceso,
        rectificacion, supresion, limitacion del tratamiento, portabilidad y oposicion, asi como retirar
        el consentimiento prestado, dirigiendose al responsable por los medios de contacto indicados en
        el apartado 2. Tambien puede presentar una reclamacion ante la Agencia Espanola de Proteccion de
        Datos (www.aepd.es).</p>
        <h2>9. Consentimiento</h2>
        <p>Declaro haber leido y comprendido la informacion anterior, haber podido resolver mis dudas y
        <strong>presto mi consentimiento</strong> para el tratamiento de mis datos personales y de salud
        con las finalidades descritas.</p>
        <div class="firmas">
        <p class="firmas__lugar">En <strong>{{consulta_lugar}}</strong>, a {{fecha}}.</p>
        <table class="firmas__tabla">
        <tr><td class="firmas__hueco"></td><td class="firmas__hueco"></td></tr>
        <tr>
        <td><div class="firmas__linea">
        <p class="firmas__rol">Firma del paciente</p>
        <p class="firmas__dato">{{paciente_nombre}}</p>
        <p class="firmas__dato">DNI / NIE: {{paciente_dni}}</p>
        </div></td>
        <td><div class="firmas__linea">
        <p class="firmas__rol">Firma de la profesional</p>
        <p class="firmas__dato">{{psicologa_nombre}}</p>
        <p class="firmas__dato">Colegiada n.o {{num_colegiado}}</p>
        </div></td>
        </tr>
        </table>
        </div>
        <p class="aviso-legal">Este documento se entrega por duplicado. Una copia queda en poder del
        paciente y otra se archiva en su historia clinica, conforme a la normativa vigente de proteccion
        de datos.</p>
        HTML;
    }
}
