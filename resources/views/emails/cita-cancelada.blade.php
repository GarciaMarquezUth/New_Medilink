@php
    $pacienteNombre = $pacienteNombre ?? 'Paciente';
    $titulo = $titulo ?? 'Tu cita fue cancelada';
    $mensaje = $mensaje ?? 'Te informamos que la cita registrada en MediLink fue cancelada. Revisa los detalles y agenda una nueva fecha si lo necesitas.';
    $medicoNombre = $medicoNombre ?? 'Medico por confirmar';
    $servicioNombre = $servicioNombre ?? 'Servicio clinico';
    $fecha = $fecha ?? 'Fecha por confirmar';
    $hora = $hora ?? 'Hora por confirmar';
    $motivo = $motivo ?? 'Consulta medica';
    $motivoCancelacion = $motivoCancelacion ?? 'Cancelacion registrada por la clinica.';
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
<body style="margin:0; padding:0; background:#fff1f2; color:#0f172a; font-family:Arial, Helvetica, sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#fff1f2; margin:0; padding:32px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:640px; background:#ffffff; border:1px solid #fecdd3; border-radius:18px; overflow:hidden;">
                    <tr>
                        <td style="background:#881337; padding:28px 30px;">
                            <p style="margin:0; color:#ffe4e6; font-size:12px; font-weight:700; letter-spacing:2px; text-transform:uppercase;">{{ $clinicaNombre }}</p>
                            <h1 style="margin:10px 0 0; color:#ffffff; font-size:28px; line-height:1.2; font-weight:800;">{{ $titulo }}</h1>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:30px;">
                            <p style="margin:0; color:#334155; font-size:16px; line-height:1.7;">Hola, <strong>{{ $pacienteNombre }}</strong>.</p>
                            <p style="margin:12px 0 0; color:#475569; font-size:15px; line-height:1.7;">{{ $mensaje }}</p>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin-top:24px; border:1px solid #fecdd3; border-radius:14px; overflow:hidden;">
                                <tr>
                                    <td style="background:#fff1f2; padding:18px 20px; border-bottom:1px solid #fecdd3;">
                                        <span style="display:inline-block; background:#e11d48; color:#ffffff; border-radius:999px; padding:7px 12px; font-size:12px; font-weight:700; text-transform:uppercase;">Cancelada</span>
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
                                                <td style="padding:14px 0; border-top:1px solid #e2e8f0; color:#64748b; font-size:12px; font-weight:700; text-transform:uppercase;">Fecha original</td>
                                                <td align="right" style="padding:14px 0; border-top:1px solid #e2e8f0; color:#0f172a; font-size:14px; font-weight:700;">{{ $fecha }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:14px 0; border-top:1px solid #e2e8f0; color:#64748b; font-size:12px; font-weight:700; text-transform:uppercase;">Hora original</td>
                                                <td align="right" style="padding:14px 0; border-top:1px solid #e2e8f0; color:#0f172a; font-size:14px; font-weight:700;">{{ $hora }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:14px 0 0; border-top:1px solid #e2e8f0; color:#64748b; font-size:12px; font-weight:700; text-transform:uppercase;">Motivo de cita</td>
                                                <td align="right" style="padding:14px 0 0; border-top:1px solid #e2e8f0; color:#0f172a; font-size:14px; font-weight:700;">{{ $motivo }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin-top:20px; border:1px solid #fecdd3; background:#fff1f2; border-radius:14px;">
                                <tr>
                                    <td style="padding:18px 20px;">
                                        <p style="margin:0; color:#9f1239; font-size:12px; font-weight:800; letter-spacing:1px; text-transform:uppercase;">Motivo de cancelacion</p>
                                        <p style="margin:8px 0 0; color:#7f1d1d; font-size:14px; line-height:1.7;">{{ $motivoCancelacion }}</p>
                                    </td>
                                </tr>
                            </table>

                            <table role="presentation" cellspacing="0" cellpadding="0" style="margin-top:26px;">
                                <tr>
                                    <td>
                                        <a href="{{ $portalUrl }}" style="display:inline-block; background:#be123c; color:#ffffff; text-decoration:none; border-radius:12px; padding:13px 18px; font-size:14px; font-weight:800;">Agendar nueva cita</a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:26px 0 0; color:#64748b; font-size:13px; line-height:1.7;">
                                Si consideras que esta cancelacion fue un error, comunicate directamente con la clinica para recibir apoyo.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="background:#fff7f8; border-top:1px solid #fecdd3; padding:20px 30px;">
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
