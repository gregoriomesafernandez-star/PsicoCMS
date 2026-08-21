<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nueva cita reservada</title>
</head>
<body style="margin: 0; padding: 24px; background-color: #f4f6fa; font-family: Arial, Helvetica, sans-serif; color: #1f2933;">

    <table role="presentation" style="max-width: 560px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden;">
        <tr>
            <td style="padding: 24px 28px; background-color: #3d5a80; color: #ffffff;">
                <h1 style="margin: 0; font-size: 20px;">Nueva cita desde tu web</h1>
            </td>
        </tr>
        <tr>
            <td style="padding: 28px;">
                <p style="margin: 0 0 20px; line-height: 1.6;">
                    {{ $cita->paciente->nombre_completo }} acaba de reservar una cita.
                </p>

                <table role="presentation" style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 10px 0; border-bottom: 1px solid #e5e9f0; color: #6b7280;">Dia</td>
                        <td style="padding: 10px 0; border-bottom: 1px solid #e5e9f0; font-weight: bold;">
                            {{ ucfirst($cita->fecha->translatedFormat('l j \d\e F \d\e Y')) }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 0; border-bottom: 1px solid #e5e9f0; color: #6b7280;">Hora</td>
                        <td style="padding: 10px 0; border-bottom: 1px solid #e5e9f0; font-weight: bold;">
                            {{ $cita->hora_corta }} - {{ $cita->hora_fin_corta }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 0; border-bottom: 1px solid #e5e9f0; color: #6b7280;">Tipo</td>
                        <td style="padding: 10px 0; border-bottom: 1px solid #e5e9f0;">
                            {{ $cita->modalidad_legible }} · {{ $cita->duracion }} minutos
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px 0; border-bottom: 1px solid #e5e9f0; color: #6b7280;">Telefono</td>
                        <td style="padding: 10px 0; border-bottom: 1px solid #e5e9f0;">
                            <a href="tel:{{ $cita->paciente->telefono }}" style="color: #3d5a80;">
                                {{ $cita->paciente->telefono_legible }}
                            </a>
                        </td>
                    </tr>
                    @if ($cita->motivo)
                        <tr>
                            <td style="padding: 10px 0; border-bottom: 1px solid #e5e9f0; color: #6b7280;">Motivo</td>
                            <td style="padding: 10px 0; border-bottom: 1px solid #e5e9f0;">{{ $cita->motivo }}</td>
                        </tr>
                    @endif
                </table>

                <p style="margin: 24px 0 0;">
                    <a href="{{ route('panel.citas.index') }}"
                       style="display: inline-block; padding: 12px 22px; border-radius: 24px; background-color: #3d5a80; color: #ffffff; text-decoration: none; font-weight: bold;">
                        Ver la cita en tu panel
                    </a>
                </p>

                <p style="margin: 20px 0 0; font-size: 13px; color: #6b7280; line-height: 1.6;">
                    La cita ha entrado como <strong>pendiente</strong>. Confirmala desde el panel
                    cuando hables con la persona.
                </p>
            </td>
        </tr>
    </table>

</body>
</html>
