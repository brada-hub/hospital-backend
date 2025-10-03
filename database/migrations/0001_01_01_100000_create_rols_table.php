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
        Schema::create('rols', function (Blueprint $table) {
            $table->id(); // Columna de ID auto-incremental (bigint, unsigned)

            // Basado en tus validaciones: 'required|string|max:50|unique'
            $table->string('nombre', 50)->unique();

            // Basado en tus validaciones: 'nullable|string|max:255'
            $table->string('descripcion', 255)->nullable();

            // El nuevo campo 'estado' con valor por defecto '1' (activo)
            $table->boolean('estado')->default(true);

            $table->timestamps(); // Columnas created_at y updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rols');
    }
};
