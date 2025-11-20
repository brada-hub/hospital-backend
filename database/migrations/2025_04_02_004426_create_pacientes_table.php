<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pacientes', function (Blueprint $table) {
            $table->id();
            $table->string('ci')->unique();
            $table->string('nombre');
            $table->string('apellidos');
            $table->date('fecha_nacimiento');
            $table->string('genero');
            $table->integer('telefono');
            $table->string('direccion');

            // ✅ NUEVOS CAMPOS: Contacto de referencia
            $table->string('nombre_referencia')->nullable();
            $table->string('apellidos_referencia')->nullable();
            $table->string('celular_referencia')->nullable();

            // Usuario asociado (se creará automáticamente)
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');

            $table->boolean('estado')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pacientes');
    }
};
