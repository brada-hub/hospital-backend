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
        Schema::create('hospitals', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100)->unique(); // Nombre único y con límite
            $table->string('departamento', 50);
            $table->string('direccion', 255);
            $table->string('nivel', 50);
            $table->string('tipo', 50);
            $table->string('telefono', 15)->unique();  // String es mejor para teléfonos
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hospitals');
    }
};
