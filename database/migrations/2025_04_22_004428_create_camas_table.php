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
            $table->string('nombre');
            $table->string('tipo');

            // CAMBIADO: 'estado' ahora es booleano (0 o 1) para activo/inactivo.
            $table->boolean('estado')->default(1);

            // AÑADIDO: 'disponibilidad' para manejar múltiples estados.
            // Usaremos un entero pequeño sin signo. Por defecto, una cama estará disponible.
            // 0: Ocupada, 1: Disponible, 2: Mantenimiento
            $table->tinyInteger('disponibilidad')->unsigned()->default(1);

            $table->foreignId('sala_id')->constrained('salas')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('camas');
    }
};
