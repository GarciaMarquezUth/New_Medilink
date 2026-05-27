<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecutar la migración.
     */
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200);
            $table->text('description')->nullable();
            $table->integer('duration_minutes');
            $table->decimal('price', 10, 2)->default(0.00);
            $table->boolean('is_active')->default(true);
            $table->string('color', 7)->nullable();
            $table->string('category', 100)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Revertir la migración.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};