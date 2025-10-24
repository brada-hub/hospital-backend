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
        Schema::create('alimentacion_tiempos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alimentacion_id')
                ->constrained('alimentacions')
                ->onDelete('cascade');
            $table->enum('tiempo_comida', [
                'Desayuno',
                'Merienda AM',
                'Almuerzo',
                'Merienda PM',
                'Cena'
            ]);
            $table->text('descripcion')->nullable();
            $table->integer('orden')->default(0); // Para ordenar los tiempos
            $table->timestamps();

            // Índice para mejorar consultas
            $table->index(['alimentacion_id', 'tiempo_comida']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alimentacion_tiempos');
    }
};
