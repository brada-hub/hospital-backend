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
        Schema::create('ocupacions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('internacion_id')->constrained('internacions')->onDelete('cascade');
            $table->foreignId('cama_id')->constrained('camas')->onDelete('cascade');
            $table->dateTime('fecha_ocupacion');
            $table->dateTime('fecha_desocupacion')->nullable();
            $table->string('observaciones')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ocupacions');
    }
};
