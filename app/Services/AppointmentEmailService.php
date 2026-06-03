<?php

namespace App\Services;

use App\Models\Cita;
use Illuminate\Support\Facades\Log;
use Throwable;

class AppointmentEmailService
{
    public function __construct(private GmailApiService $gmail) {}

    public function canSend(): bool
    {
        return (bool) config('services.gmail.enabled') && $this->gmail->configuredForSending();
    }

    public function sendConfirmation(Cita $cita): bool
    {
        return $this->sendAppointmentEmail(
            $cita,
            'Confirmacion de cita',
            'Tu cita fue agendada correctamente.',
            'Te compartimos los detalles de tu cita:',
            'confirmacion',
        );
    }

    public function sendCancellation(Cita $cita): bool
    {
        return $this->sendAppointmentEmail(
            $cita,
            'Cancelacion de cita',
            'Tu cita fue cancelada.',
            'Estos eran los detalles de la cita cancelada:',
            'cancelacion',
        );
    }

    public function sendReminder(Cita $cita): bool
    {
        return $this->sendAppointmentEmail(
            $cita,
            'Recordatorio de cita',
            'Tienes una cita programada proximamente.',
            'Recuerda estos detalles:',
            'recordatorio',
        );
    }

    private function sendAppointmentEmail(Cita $cita, string $subject, string $title, string $intro, string $type): bool
    {
        if (! (bool) config('services.gmail.enabled')) {
            return false;
        }

        if (! $this->gmail->configuredForSending()) {
            Log::warning('Gmail no esta configurado para enviar correos de citas.');

            return false;
        }

        $cita->loadMissing(['paciente', 'medico', 'servicio']);
        $recipient = $cita->paciente?->email;

        if (blank($recipient)) {
            Log::info('No se envio correo de cita porque el paciente no tiene email.', ['cita_id' => $cita->id]);

            return false;
        }

        try {
            $this->gmail->send(
                $recipient,
                $subject,
                $this->htmlBody($cita, $title, $intro, $type),
                $this->textBody($cita, $title, $intro),
            );

            return true;
        } catch (Throwable $exception) {
            Log::error('No se pudo enviar correo de cita por Gmail.', [
                'cita_id' => $cita->id,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    private function htmlBody(Cita $cita, string $title, string $intro, string $type): string
    {
        $details = $this->details($cita);
        $theme = $this->theme($type);
        $appName = (string) config('services.gmail.from_name', config('app.name', 'Clinica'));
        $date = $cita->fecha_hora?->format('d/m/Y') ?? 'Pendiente';
        $time = $cita->fecha_hora?->format('H:i') ?? '--:--';
        $cards = '';

        foreach ($details as $label => $value) {
            $cards .= $this->detailCard($label, $value);
        }

        return '<!doctype html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"></head>'
            .'<body style="margin:0;padding:0;background:#f1f5f9;font-family:Arial,Helvetica,sans-serif;color:#0f172a;">'
            .'<div style="display:none;max-height:0;overflow:hidden;color:#f1f5f9;opacity:0;">'.$this->escape($intro).'</div>'
            .'<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="width:100%;background:#f1f5f9;margin:0;padding:32px 12px;">'
            .'<tr><td align="center">'
            .'<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="width:100%;max-width:640px;background:#ffffff;border-radius:28px;overflow:hidden;box-shadow:0 20px 50px rgba(15,23,42,0.12);">'
            .'<tr><td style="padding:0;background:'.$theme['dark'].';">'
            .'<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:linear-gradient(135deg,'.$theme['dark'].','.$theme['main'].');">'
            .'<tr><td style="padding:30px 28px 26px 28px;color:#ffffff;">'
            .'<table role="presentation" width="100%" cellspacing="0" cellpadding="0"><tr>'
            .'<td style="vertical-align:top;">'
            .'<div style="display:inline-block;width:48px;height:48px;line-height:48px;text-align:center;border-radius:16px;background:rgba(255,255,255,0.16);font-size:20px;font-weight:900;color:#ffffff;">M</div>'
            .'<p style="margin:14px 0 0 0;font-size:12px;font-weight:800;letter-spacing:2px;text-transform:uppercase;color:rgba(255,255,255,0.78);">'.$this->escape($appName).'</p>'
            .'<h1 style="margin:8px 0 0 0;font-size:30px;line-height:1.12;font-weight:900;color:#ffffff;">'.$this->escape($title).'</h1>'
            .'<p style="margin:12px 0 0 0;font-size:15px;line-height:1.65;font-weight:600;color:rgba(255,255,255,0.86);">'.$this->escape($intro).'</p>'
            .'</td>'
            .'<td align="right" style="vertical-align:top;width:120px;">'
            .'<span style="display:inline-block;border-radius:999px;background:rgba(255,255,255,0.16);padding:8px 12px;font-size:12px;font-weight:900;color:#ffffff;">'.$this->escape($theme['badge']).'</span>'
            .'</td>'
            .'</tr></table>'
            .'</td></tr></table>'
            .'</td></tr>'
            .'<tr><td style="padding:28px;">'
            .'<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-radius:22px;background:#f8fafc;border:1px solid #e2e8f0;">'
            .'<tr><td style="padding:22px;">'
            .'<p style="margin:0 0 8px 0;font-size:12px;font-weight:900;letter-spacing:1.6px;text-transform:uppercase;color:'.$theme['main'].';">Fecha y hora</p>'
            .'<table role="presentation" width="100%" cellspacing="0" cellpadding="0"><tr>'
            .'<td style="vertical-align:middle;">'
            .'<p style="margin:0;font-size:28px;line-height:1.2;font-weight:900;color:#0f172a;">'.$this->escape($date).'</p>'
            .'<p style="margin:6px 0 0 0;font-size:15px;font-weight:700;color:#64748b;">Hora de inicio</p>'
            .'</td>'
            .'<td align="right" style="vertical-align:middle;">'
            .'<div style="display:inline-block;border-radius:18px;background:'.$theme['soft'].';padding:14px 18px;font-size:24px;font-weight:900;color:'.$theme['main'].';">'.$this->escape($time).'</div>'
            .'</td>'
            .'</tr></table>'
            .'</td></tr></table>'
            .'<h2 style="margin:26px 0 14px 0;font-size:18px;font-weight:900;color:#0f172a;">Detalles de la cita</h2>'
            .'<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="width:100%;">'.$cards.'</table>'
            .'<div style="margin-top:24px;border-radius:20px;background:'.$theme['soft'].';border:1px solid '.$theme['border'].';padding:18px 20px;">'
            .'<p style="margin:0;font-size:14px;line-height:1.65;font-weight:700;color:'.$theme['text'].';">'.$this->escape($theme['note']).'</p>'
            .'</div>'
            .'</td></tr>'
            .'<tr><td style="padding:20px 28px 28px 28px;background:#f8fafc;border-top:1px solid #e2e8f0;">'
            .'<p style="margin:0;font-size:12px;line-height:1.7;font-weight:700;color:#64748b;">Este correo fue enviado automaticamente por '.$this->escape($appName).'. Si no reconoces esta cita, contacta a la clinica.</p>'
            .'</td></tr>'
            .'</table>'
            .'</td></tr></table>'
            .'</body></html>';
    }

    private function detailCard(string $label, string $value): string
    {
        return '<tr><td style="padding:0 0 10px 0;">'
            .'<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-radius:16px;background:#ffffff;border:1px solid #e2e8f0;">'
            .'<tr><td style="padding:14px 16px;">'
            .'<p style="margin:0 0 5px 0;font-size:11px;font-weight:900;letter-spacing:1.2px;text-transform:uppercase;color:#94a3b8;">'.$this->escape($label).'</p>'
            .'<p style="margin:0;font-size:15px;line-height:1.45;font-weight:800;color:#0f172a;">'.$this->escape($value).'</p>'
            .'</td></tr></table>'
            .'</td></tr>';
    }

    private function theme(string $type): array
    {
        return match ($type) {
            'cancelacion' => [
                'main' => '#dc2626',
                'dark' => '#991b1b',
                'soft' => '#fef2f2',
                'border' => '#fecaca',
                'text' => '#991b1b',
                'badge' => 'Cancelada',
                'note' => 'Tu cita fue marcada como cancelada. Si necesitas reagendar, vuelve al portal o contacta a recepcion.',
            ],
            'recordatorio' => [
                'main' => '#d97706',
                'dark' => '#92400e',
                'soft' => '#fffbeb',
                'border' => '#fde68a',
                'text' => '#92400e',
                'badge' => 'Recordatorio',
                'note' => 'Llega unos minutos antes de tu cita y ten a la mano cualquier informacion medica relevante.',
            ],
            default => [
                'main' => '#7c3aed',
                'dark' => '#4c1d95',
                'soft' => '#f5f3ff',
                'border' => '#ddd6fe',
                'text' => '#4c1d95',
                'badge' => 'Confirmada',
                'note' => 'Tu cita quedo registrada. Te recomendamos revisar la fecha y guardar este correo como referencia.',
            ],
        };
    }

    private function textBody(Cita $cita, string $title, string $intro): string
    {
        $lines = [$title, '', $intro, ''];

        foreach ($this->details($cita) as $label => $value) {
            $lines[] = $label.': '.$value;
        }

        $lines[] = '';
        $lines[] = 'Este correo fue enviado automaticamente por la clinica.';

        return implode("\n", $lines);
    }

    private function details(Cita $cita): array
    {
        return [
            'Paciente' => trim(($cita->paciente?->nombre ?? '').' '.($cita->paciente?->apellido ?? '')) ?: 'Paciente',
            'Medico' => trim(($cita->medico?->nombre ?? '').' '.($cita->medico?->apellido ?? '')) ?: 'Medico asignado',
            'Servicio' => $cita->servicio?->nombre ?? 'Servicio asignado',
            'Fecha y hora' => $cita->fecha_hora?->format('d/m/Y H:i') ?? 'Pendiente',
            'Estado' => Cita::estados()[$cita->estado] ?? $cita->estado,
        ];
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
