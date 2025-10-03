// database/migrations/xxxx_xx_xx_xxxxxx_create_recetas_table.php

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
        Schema::create('recetas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tratamiento_id')->constrained('tratamientos')->onDelete('cascade');
            $table->foreignId('medicamento_id')->constrained('medicamentos')->onDelete('cascade');
            $table->string('dosis'); // Ejemplo: "500mg" o "1 tableta"
            $table->integer('frecuencia_horas'); // Ejemplo: 8 (cada 8 horas)
            $table->integer('duracion_dias'); // Ejemplo: 7 (por 7 días)
            $table->string('via_administracion');
            $table->text('indicaciones')->nullable(); // Instrucciones adicionales, ej: "Tomar con alimentos"
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recetas');
    }
};
