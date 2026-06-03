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
            'Te compartimos los detalles de tu cita:'
        );
    }

    public function sendCancellation(Cita $cita): bool
    {
        return $this->sendAppointmentEmail(
            $cita,
            'Cancelacion de cita',
            'Tu cita fue cancelada.',
            'Estos eran los detalles de la cita cancelada:'
        );
    }

    public function sendReminder(Cita $cita): bool
    {
        return $this->sendAppointmentEmail(
            $cita,
            'Recordatorio de cita',
            'Tienes una cita programada proximamente.',
            'Recuerda estos detalles:'
        );
    }

    private function sendAppointmentEmail(Cita $cita, string $subject, string $title, string $intro): bool
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
                $this->htmlBody($cita, $title, $intro),
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

    private function htmlBody(Cita $cita, string $title, string $intro): string
    {
        $details = $this->details($cita);
        $items = '';

        foreach ($details as $label => $value) {
            $items .= '<tr><th style="padding:8px 12px;text-align:left;background:#f3f4f6;border:1px solid #e5e7eb;">'.$this->escape($label).'</th>'
                .'<td style="padding:8px 12px;border:1px solid #e5e7eb;">'.$this->escape($value).'</td></tr>';
        }

        return '<!doctype html><html><body style="font-family:Arial,sans-serif;color:#111827;line-height:1.5;">'
            .'<h1 style="font-size:22px;">'.$this->escape($title).'</h1>'
            .'<p>'.$this->escape($intro).'</p>'
            .'<table style="border-collapse:collapse;margin-top:16px;">'.$items.'</table>'
            .'<p style="margin-top:20px;color:#6b7280;">Este correo fue enviado automaticamente por la clinica.</p>'
            .'</body></html>';
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
