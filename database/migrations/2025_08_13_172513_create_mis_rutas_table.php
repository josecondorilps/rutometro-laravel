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
        Schema::create('mis_rutas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('ruta_id')->constrained('rutas')->onDelete('cascade');
            $table->string('nombre');
            $table->enum('estado', ['asignado', 'en_progreso', 'completado', 'pausado'])->default('asignado');
            $table->timestamp('fecha_asignacion')->nullable();
            $table->timestamp('fecha_inicio')->nullable();
            $table->timestamp('fecha_completado')->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();

            // Evitar duplicados: un usuario no puede tener la misma ruta dos veces
            $table->unique(['user_id', 'ruta_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mis_rutas');
    }
};
