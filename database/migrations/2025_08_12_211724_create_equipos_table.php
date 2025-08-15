<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipos', function (Blueprint $table) {
            $table->id();
            $table->string('identificador')->unique();
            $table->decimal('latitud', 10, 8);
            $table->decimal('longitud', 11, 8);
            $table->decimal('altitud', 8, 3)->nullable();
            $table->string('tipo')->nullable();
            $table->string('direccion')->nullable();
            $table->string('area')->nullable();
            $table->string('estado')->nullable();

            // Campos para panoramas
            $table->string('panorama_filename')->nullable();
            $table->string('panorama_thumbnail')->nullable();
            $table->text('panorama_description')->nullable();

            // Relación con rutas
            $table->foreignId('ruta_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('orden_en_ruta')->nullable();
            $table->string('qr_code_path')->nullable();

            // Trabajo de campo
            $table->boolean('inspeccionado')->default(false);
            $table->timestamp('fecha_inspeccion')->nullable();
            $table->foreignId('inspeccionado_por')->nullable()->constrained('users');
            $table->text('observaciones_campo')->nullable();

            $table->timestamps();

            // Índices
            $table->index(['latitud', 'longitud']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipos');
    }
};
