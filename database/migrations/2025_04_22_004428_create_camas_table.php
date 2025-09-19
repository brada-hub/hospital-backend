<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('camas', function (Blueprint $table) {
            $table->id();
            // CAMBIADO: Límite de caracteres
            $table->string('nombre', 100);
            // CAMBIADO: Límite de caracteres
            $table->string('tipo', 50);
            $table->boolean('estado')->default(1);
            // 0: Ocupada, 1: Disponible, 2: Mantenimiento
            $table->tinyInteger('disponibilidad')->unsigned()->default(1);
            $table->foreignId('sala_id')->constrained('salas')->onDelete('cascade');
            $table->timestamps();

            // AÑADIDO: Índice único compuesto.
            // Una cama debe tener un nombre único DENTRO de una misma sala.
            $table->unique(['nombre', 'sala_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('camas');
    }
};
