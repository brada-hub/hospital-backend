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
            $table->foreignId('internacion_id')->constrained('internacions')->onDelete('cascade');
            $table->foreignId('tipo_dieta_id')->constrained('tipos_dieta');

            $table->enum('via_administracion', ['Oral', 'Enteral', 'Parenteral'])->default('Oral');

            $table->unsignedTinyInteger('frecuencia_tiempos'); // 1, 2, 3, 4, o 5

            // Ejemplo: [{"tiempo": "Desayuno", "descripcion": "Papilla de avena"}, ...]



            $table->text('restricciones')->nullable(); // Ej: "Sin azúcares añadidos, sin frituras"
            $table->text('descripcion'); // Observaciones médicas

            $table->dateTime('fecha_inicio');
            $table->dateTime('fecha_fin');
            $table->tinyInteger('estado')->default(0); // 0=Activo, 1=Suspendido, 2=Finalizado
            $table->text('motivo_suspension')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alimentacions');
    }
};
