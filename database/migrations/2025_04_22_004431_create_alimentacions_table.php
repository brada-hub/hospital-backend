<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alimentacions', function (Blueprint $table) {
            $table->id();

            // --- AÑADE ESTA LÍNEA ---
            // Esto crea la columna 'internacion_id' y la enlaza con la tabla 'internacions'
            $table->foreignId('internacion_id')->constrained('internacions')->onDelete('cascade');

            $table->foreignId('tipo_dieta_id')->constrained('tipos_dieta');
            $table->string('frecuencia');
            $table->dateTime('fecha_inicio');
            $table->dateTime('fecha_fin');
            $table->string('descripcion');
            $table->tinyInteger('estado')->default(0);
            $table->text('motivo_suspension')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alimentacions');
    }
};
