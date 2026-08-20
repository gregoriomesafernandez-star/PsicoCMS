<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Prueba de PsicoCMS</title>
</head>
<body style="margin: 0; padding: 24px; background-color: #f4f6fa; font-family: Arial, Helvetica, sans-serif; color: #1f2933;">

    <table role="presentation" style="max-width: 560px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden;">
        <tr>
            <td style="padding: 24px 28px; background-color: #3d5a80; color: #ffffff;">
                <h1 style="margin: 0; font-size: 20px;">PsicoCMS</h1>
            </td>
        </tr>
        <tr>
            <td style="padding: 28px;">
                <h2 style="margin: 0 0 12px; font-size: 18px;">Todo funciona</h2>

                <p style="margin: 0 0 16px; line-height: 1.6;">
                    Si estas leyendo esto, los avisos por email de tu web ya estan configurados
                    correctamente{{ $nombre ? ', ' . $nombre : '' }}.
                </p>

                <p style="margin: 0 0 16px; line-height: 1.6;">
                    A partir de ahora recibiras un correo como este cada vez que un paciente reserve
                    una cita desde tu web, con su nombre, su telefono y el dia y la hora elegidos.
                </p>

                <p style="margin: 0; font-size: 13px; color: #6b7785;">
                    Este mensaje se ha enviado desde tu panel de PsicoCMS a peticion tuya.
                </p>
            </td>
        </tr>
    </table>

</body>
</html>
