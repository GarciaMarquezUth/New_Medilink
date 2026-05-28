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
            if (! Schema::hasColumn('citas', 'estado_pago')) {
                $table->string('estado_pago')->default('pendiente')->after('estado');
            }

            if (! Schema::hasColumn('citas', 'monto_pagado')) {
                $table->decimal('monto_pagado', 10, 2)->nullable()->after('estado_pago');
            }

            if (! Schema::hasColumn('citas', 'fecha_pago')) {
                $table->timestamp('fecha_pago')->nullable()->after('monto_pagado');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('citas', function (Blueprint $table) {
            foreach (['fecha_pago', 'monto_pagado', 'estado_pago'] as $column) {
                if (Schema::hasColumn('citas', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
