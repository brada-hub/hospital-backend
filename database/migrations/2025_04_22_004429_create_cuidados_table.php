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
        Schema::create('cuidados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('internacion_id')->constrained('internacions')->onDelete('cascade');
            $table->string('tipo');
            $table->string('descripcion');
            $table->dateTime('fecha_inicio');

            // ✅ CORRECCIÓN 1: 'fecha_fin' debe ser NULLABLE para planes activos.
            $table->dateTime('fecha_fin')->nullable();

            $table->string('frecuencia');

            // ✅ CORRECCIÓN 2: Usar tinyInteger para estados 0 y 1.
            $table->tinyInteger('estado')->default(0)->comment('0: Activo, 1: Finalizado');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cuidados');
    }
};
