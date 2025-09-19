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
        Schema::create('especialidads', function (Blueprint $table) {
            $table->id();
            // CAMBIADO: Límite de caracteres añadido
            $table->string('nombre', 100);
            // CAMBIADO: Límite de caracteres añadido
            $table->string('descripcion', 255)->nullable();
            $table->boolean('estado')->default(1);
            $table->foreignId('hospital_id')->constrained('hospitals')->onDelete('cascade');
            $table->timestamps();

            // AÑADIDO: Índice único compuesto.
            // Esto asegura que no se pueda repetir un 'nombre' para el mismo 'hospital_id'.
            $table->unique(['nombre', 'hospital_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('especialidads');
    }
};
