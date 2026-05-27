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
        Schema::create('disponibilidades', function (Blueprint $table) {
            $table->id();
            // Relación con médicos (asegúrate de que tu tabla se llame 'medicos')
            $table->foreignId('medico_id')->constrained('medicos')->onDelete('cascade');
            
            $table->integer('dia_semana'); // 1 = Lunes, 2 = Martes, ..., 7 = Domingo
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disponibilidades');
    }
};
