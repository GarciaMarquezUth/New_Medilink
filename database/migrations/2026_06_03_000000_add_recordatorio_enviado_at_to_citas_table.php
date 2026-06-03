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
        Schema::table('citas', function (Blueprint $table) {
            if (! Schema::hasColumn('citas', 'recordatorio_enviado_at')) {
                $table->timestamp('recordatorio_enviado_at')->nullable()->after('estado');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('citas', function (Blueprint $table) {
            if (Schema::hasColumn('citas', 'recordatorio_enviado_at')) {
                $table->dropColumn('recordatorio_enviado_at');
            }
        });
    }
};
