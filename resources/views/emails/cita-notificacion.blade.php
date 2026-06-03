@php
    $pacienteNombre = $pacienteNombre ?? 'Paciente';
    $titulo = $titulo ?? 'Actualizacion de tu cita';
    $mensaje = $mensaje ?? 'Te compartimos la informacion registrada para tu cita en MediLink.';
    $estado = $estado ?? 'Agendada';
    $estadoColor = $estadoColor ?? '#0f766e';
    $medicoNombre = $medicoNombre ?? 'Medico por confirmar';
    $servicioNombre = $servicioNombre ?? 'Servicio clinico';
    $fecha = $fecha ?? 'Fecha por confirmar';
    $hora = $hora ?? 'Hora por confirmar';
    $motivo = $motivo ?? 'Consulta medica';
    $portalUrl = $portalUrl ?? url('/portal-paciente/citas');
    $clinicaNombre = $clinicaNombre ?? config('app.name', 'MediLink');
@endphp

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $titulo }}</title>
</head>
<body style="margin:0; padding:0; background:#f1f5f9; color:#0f172a; font-family:Arial, Helvetica, sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f1f5f9; margin:0; padding:32px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:640px; background:#ffffff; border:1px solid #e2e8f0; border-radius:18px; overflow:hidden;">
                    <tr>
                        <td style="background:#0f172a; padding:28px 30px;">
                            <p style="margin:0; color:#99f6e4; font-size:12px; font-weight:700; letter-spacing:2px; text-transform:uppercase;">{{ $clinicaNombre }}</p>
                            <h1 style="margin:10px 0 0; color:#ffffff; font-size:28px; line-height:1.2; font-weight:800;">{{ $titulo }}</h1>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:30px;">
                            <p style="margin:0; color:#334155; font-size:16px; line-height:1.7;">Hola, <strong>{{ $pacienteNombre }}</strong>.</p>
                            <p style="margin:12px 0 0; color:#475569; font-size:15px; line-height:1.7;">{{ $mensaje }}</p>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin-top:24px; border:1px solid #e2e8f0; border-radius:14px; overflow:hidden;">
                                <tr>
                                    <td style="background:#f8fafc; padding:18px 20px; border-bottom:1px solid #e2e8f0;">
                                        <span style="display:inline-block; background:{{ $estadoColor }}; color:#ffffff; border-radius:999px; padding:7px 12px; font-size:12px; font-weight:700; text-transform:uppercase;">{{ $estado }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:20px;">
                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                            <tr>
                                                <td style="padding:0 0 14px; color:#64748b; font-size:12px; font-weight:700; text-transform:uppercase;">Medico</td>
                                                <td align="right" style="padding:0 0 14px; color:#0f172a; font-size:14px; font-weight:700;">{{ $medicoNombre }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:14px 0; border-top:1px solid #e2e8f0; color:#64748b; font-size:12px; font-weight:700; text-transform:uppercase;">Servicio</td>
                                                <td align="right" style="padding:14px 0; border-top:1px solid #e2e8f0; color:#0f172a; font-size:14px; font-weight:700;">{{ $servicioNombre }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:14px 0; border-top:1px solid #e2e8f0; color:#64748b; font-size:12px; font-weight:700; text-transform:uppercase;">Fecha</td>
                                                <td align="right" style="padding:14px 0; border-top:1px solid #e2e8f0; color:#0f172a; font-size:14px; font-weight:700;">{{ $fecha }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:14px 0; border-top:1px solid #e2e8f0; color:#64748b; font-size:12px; font-weight:700; text-transform:uppercase;">Hora</td>
                                                <td align="right" style="padding:14px 0; border-top:1px solid #e2e8f0; color:#0f172a; font-size:14px; font-weight:700;">{{ $hora }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:14px 0 0; border-top:1px solid #e2e8f0; color:#64748b; font-size:12px; font-weight:700; text-transform:uppercase;">Motivo</td>
                                                <td align="right" style="padding:14px 0 0; border-top:1px solid #e2e8f0; color:#0f172a; font-size:14px; font-weight:700;">{{ $motivo }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <table role="presentation" cellspacing="0" cellpadding="0" style="margin-top:26px;">
                                <tr>
                                    <td>
                                        <a href="{{ $portalUrl }}" style="display:inline-block; background:#0f766e; color:#ffffff; text-decoration:none; border-radius:12px; padding:13px 18px; font-size:14px; font-weight:800;">Ver portal de citas</a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:26px 0 0; color:#64748b; font-size:13px; line-height:1.7;">
                                Si necesitas cambiar o cancelar tu cita, comunicate con la clinica para validar disponibilidad.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="background:#f8fafc; border-top:1px solid #e2e8f0; padding:20px 30px;">
                            <p style="margin:0; color:#64748b; font-size:12px; line-height:1.6;">
                                Este correo fue enviado automaticamente por {{ $clinicaNombre }}. Por favor no respondas directamente a este mensaje.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
