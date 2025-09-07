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
        Schema::create('internacions', function (Blueprint $table) {
            $table->id();
            $table->dateTime('fecha_ingreso');
            $table->dateTime('fecha_alta')->nullable();
            $table->string('motivo');
            $table->string('diagnostico');
            $table->string('observaciones')->nullable();
            $table->foreignId('paciente_id')->constrained('pacientes')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('internacions');
    }
};
