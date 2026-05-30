<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('antropometrias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('internacion_id')->unique()->constrained('internacions')->onDelete('cascade');
            $table->decimal('peso', 5, 2); // en kilogramos, ej: 75.50
            $table->decimal('altura', 5, 2); // en centímetros, ej: 175.00
            $table->decimal('imc', 4, 1); // Índice de Masa Corporal, ej: 24.5
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('antropometrias');
    }
};
