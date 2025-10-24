<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('consumes', function (Blueprint $table) {
            $table->id();

            // Llaves foráneas
            $table->foreignId('tratamiento_id')->constrained('tratamientos')->onDelete('cascade');
            $table->foreignId('alimentacion_id')->constrained('alimentacions')->onDelete('cascade');
            $table->foreignId('registrado_por')->constrained('users');

            // Campos de datos
            $table->enum('tiempo_comida', ['Desayuno', 'Merienda AM', 'Almuerzo', 'Merienda PM', 'Cena']);
            $table->date('fecha');
            $table->unsignedSmallInteger('porcentaje_consumido');
            $table->text('observaciones')->nullable();

            $table->timestamps();

            // --- ESTA LÍNEA ES LA QUE SE ELIMINA ---
            // Al quitarla, permites que haya múltiples registros
            // para el mismo día y tiempo de comida, creando tu "listita".
            // $table->unique(['alimentacion_id', 'fecha', 'tiempo_comida'], 'consumo_diario_unico');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('consumes');
    }
};
