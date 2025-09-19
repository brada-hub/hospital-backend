<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salas', function (Blueprint $table) {
            $table->id();
            // CAMBIADO: Límite de caracteres
            $table->string('nombre', 100);
            // CAMBIADO: Límite de caracteres
            $table->string('tipo', 50);
            $table->boolean('estado')->default(1);
            $table->foreignId('especialidad_id')->constrained('especialidads')->onDelete('cascade');
            $table->timestamps();

            // AÑADIDO: Índice único compuesto.
            // Una sala debe tener un nombre único DENTRO de una misma especialidad.
            $table->unique(['nombre', 'especialidad_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salas');
    }
};
