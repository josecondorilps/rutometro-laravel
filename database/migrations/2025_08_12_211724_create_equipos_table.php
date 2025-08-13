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


            // SOLO rutas por ahora (clusters viene después)
            $table->foreignId('ruta_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('orden_en_ruta')->nullable();
            $table->string('qr_code_path')->nullable();

            // TRABAJO DE CAMPO
            $table->boolean('inspeccionado')->default(false);
            $table->timestamp('fecha_inspeccion')->nullable();
            $table->foreignId('inspeccionado_por')->nullable()->constrained('users');
            $table->text('observaciones_campo')->nullable();

            $table->timestamps();

            // ÍNDICES
            $table->index(['latitud', 'longitud']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipos');
    }
};
