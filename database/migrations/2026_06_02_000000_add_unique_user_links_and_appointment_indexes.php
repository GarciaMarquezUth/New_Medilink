<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('medicos', function (Blueprint $table) {
            $table->unique('user_id', 'medicos_user_id_unique');
        });

        Schema::table('pacientes', function (Blueprint $table) {
            $table->unique('user_id', 'pacientes_user_id_unique');
        });

        Schema::table('citas', function (Blueprint $table) {
            $table->index(['medico_id', 'fecha_hora', 'estado'], 'citas_medico_fecha_estado_index');
            $table->index(['paciente_id', 'fecha_hora'], 'citas_paciente_fecha_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('citas', function (Blueprint $table) {
            $table->dropIndex('citas_medico_fecha_estado_index');
            $table->dropIndex('citas_paciente_fecha_index');
        });

        Schema::table('pacientes', function (Blueprint $table) {
            $table->dropUnique('pacientes_user_id_unique');
        });

        Schema::table('medicos', function (Blueprint $table) {
            $table->dropUnique('medicos_user_id_unique');
        });
    }
};
