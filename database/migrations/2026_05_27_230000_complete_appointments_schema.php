<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('servicios', function (Blueprint $table) {
            if (! Schema::hasColumn('servicios', 'nombre')) {
                $table->string('nombre')->after('id');
            }

            if (! Schema::hasColumn('servicios', 'descripcion')) {
                $table->text('descripcion')->nullable()->after('nombre');
            }

            if (! Schema::hasColumn('servicios', 'duracion_minutos')) {
                $table->unsignedInteger('duracion_minutos')->default(30)->after('descripcion');
            }

            if (! Schema::hasColumn('servicios', 'precio')) {
                $table->decimal('precio', 10, 2)->nullable()->after('duracion_minutos');
            }

            if (! Schema::hasColumn('servicios', 'activo')) {
                $table->boolean('activo')->default(true)->after('precio');
            }
        });

        Schema::table('disponibilidades', function (Blueprint $table) {
            if (! Schema::hasColumn('disponibilidades', 'medico_id')) {
                $table->foreignId('medico_id')->after('id')->constrained('medicos')->cascadeOnDelete();
            }

            if (! Schema::hasColumn('disponibilidades', 'dia_semana')) {
                $table->unsignedTinyInteger('dia_semana')->after('medico_id');
            }

            if (! Schema::hasColumn('disponibilidades', 'hora_inicio')) {
                $table->time('hora_inicio')->after('dia_semana');
            }

            if (! Schema::hasColumn('disponibilidades', 'hora_fin')) {
                $table->time('hora_fin')->after('hora_inicio');
            }

            if (! Schema::hasColumn('disponibilidades', 'activo')) {
                $table->boolean('activo')->default(true)->after('hora_fin');
            }
        });

        Schema::table('citas', function (Blueprint $table) {
            if (! Schema::hasColumn('citas', 'servicio_id')) {
                $table->foreignId('servicio_id')->nullable()->after('paciente_id')->constrained('servicios')->nullOnDelete();
            }
        });

        DB::table('citas')->where('estado', 'pendiente')->update(['estado' => 'agendada']);
        DB::table('citas')->where('estado', 'no_presentada')->update(['estado' => 'no_show']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('citas', function (Blueprint $table) {
            if (Schema::hasColumn('citas', 'servicio_id')) {
                $table->dropConstrainedForeignId('servicio_id');
            }
        });

        Schema::table('disponibilidades', function (Blueprint $table) {
            if (Schema::hasColumn('disponibilidades', 'medico_id')) {
                $table->dropConstrainedForeignId('medico_id');
            }

            foreach (['dia_semana', 'hora_inicio', 'hora_fin', 'activo'] as $column) {
                if (Schema::hasColumn('disponibilidades', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('servicios', function (Blueprint $table) {
            foreach (['nombre', 'descripcion', 'duracion_minutos', 'precio', 'activo'] as $column) {
                if (Schema::hasColumn('servicios', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
