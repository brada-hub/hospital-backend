<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consumes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tratamiento_id')->constrained('tratamientos')->onDelete('cascade');
            $table->foreignId('alimentacion_id')->constrained('alimentacions')->onDelete('cascade');
            $table->string('observaciones')->nullable();
            $table->dateTime('fecha');
            // CAMBIO: Se reemplaza el booleano 'estado' por un entero para el porcentaje.
            $table->unsignedTinyInteger('porcentaje_consumido')->default(0); // Valor de 0 a 100
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consumes');
    }
};
