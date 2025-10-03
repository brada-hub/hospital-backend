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
        Schema::create('administras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('receta_id')->constrained('recetas')->onDelete('cascade');
            // ✅ Estandarizando el nombre del campo para la relación a 'users'
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->dateTime('fecha');

            // ❌ CAMBIO CLAVE: Se elimina 'dosis' ya que se obtiene de 'recetas'
            // $table->string('dosis');

            // ✅ CAMBIO CLAVE: Se cambia a integer para usar códigos de estado (ej: 1=Administrado, 2=Retrasado)
            $table->unsignedTinyInteger('estado');

            // ✅ OBSERVACIONES: Mantenido como nullable, útil para registrar incidentes o notas
            $table->string('observaciones')->nullable();

            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('administras');
    }
};
