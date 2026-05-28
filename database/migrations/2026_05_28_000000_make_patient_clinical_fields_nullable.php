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
        Schema::table('pacientes', function (Blueprint $table) {
            $table->date('fecha_nacimiento')->nullable()->change();
            $table->string('genero', 20)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('pacientes')->whereNull('fecha_nacimiento')->update(['fecha_nacimiento' => '1900-01-01']);
        DB::table('pacientes')->whereNull('genero')->update(['genero' => 'N/A']);

        Schema::table('pacientes', function (Blueprint $table) {
            $table->date('fecha_nacimiento')->nullable(false)->change();
            $table->string('genero', 20)->nullable(false)->change();
        });
    }
};
