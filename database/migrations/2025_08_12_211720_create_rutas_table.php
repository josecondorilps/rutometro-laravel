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
        Schema::create('rutas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->foreignId('proyecto_id')->constrained()->onDelete('cascade');
            $table->integer('total_equipos');
            $table->decimal('distancia_km', 8, 3);
            $table->integer('tiempo_estimado_minutos');
            $table->decimal('centro_lat', 10, 8);
            $table->decimal('centro_lng', 11, 8);
            $table->string('estado');
            $table->foreignId('asignado_a')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('fecha_asignacion')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rutas');
    }
};
