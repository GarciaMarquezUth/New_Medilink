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
            if (! Schema::hasColumn('medicos', 'photo_path')) {
                $table->string('photo_path')->nullable()->after('user_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medicos', function (Blueprint $table) {
            if (Schema::hasColumn('medicos', 'photo_path')) {
                $table->dropColumn('photo_path');
            }
        });
    }
};
