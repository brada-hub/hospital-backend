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
            $table->dateTime('hora_programada');

            // ✅ CORRECCIÓN CRÍTICA: Debe ser nullable para las dosis pendientes
            $table->foreignId('user_id')->nullable()->constrained('users');

            // ✅ CORRECCIÓN CRÍTICA: Debe ser nullable porque aún no se ha administrado
            $table->dateTime('fecha')->nullable();

            // Usaremos: 0=Pendiente, 1=Cumplida
            $table->unsignedTinyInteger('estado')->default(0);

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
