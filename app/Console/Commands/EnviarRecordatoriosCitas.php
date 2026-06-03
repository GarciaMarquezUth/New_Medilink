<?php

namespace App\Console\Commands;

use App\Models\Cita;
use App\Services\AppointmentEmailService;
use Illuminate\Console\Command;

class EnviarRecordatoriosCitas extends Command
{
    protected $signature = 'citas:enviar-recordatorios {--hours= : Horas antes de la cita para enviar recordatorios}';

    protected $description = 'Envia recordatorios automaticos de citas por Gmail.';

    public function handle(AppointmentEmailService $emails): int
    {
        if (! $emails->canSend()) {
            $this->warn('Gmail no esta configurado para enviar recordatorios.');

            return self::SUCCESS;
        }

        $hours = (int) ($this->option('hours') ?: config('services.gmail.reminder_hours', 24));

        if ($hours < 1) {
            $this->error('El valor de horas debe ser mayor a cero.');

            return self::FAILURE;
        }

        $sent = 0;
        $now = now();
        $limit = $now->copy()->addHours($hours);

        Cita::with(['paciente', 'medico', 'servicio'])
            ->whereNull('recordatorio_enviado_at')
            ->whereIn('estado', Cita::estadosOcupantes())
            ->where('fecha_hora', '>=', $now)
            ->where('fecha_hora', '<=', $limit)
            ->orderBy('id')
            ->chunkById(50, function ($citas) use ($emails, &$sent) {
                foreach ($citas as $cita) {
                    if (! $emails->sendReminder($cita)) {
                        continue;
                    }

                    $cita->forceFill(['recordatorio_enviado_at' => now()])->save();
                    $sent++;
                }
            });

        $this->info("Recordatorios enviados: {$sent}");

        return self::SUCCESS;
    }
}
