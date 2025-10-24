<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
// Ya no necesitas 'use DB' aquí

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rangos_normales', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('signo_id');
            $table->double('valor_minimo');
            $table->double('valor_maximo');
            $table->timestamps();

            $table->foreign('signo_id')
                ->references('id')
                ->on('signos')
                ->onDelete('cascade');
        });

        // <-- ¡HEMOS QUITADO EL BLOQUE DB::table('rangos_normales')->insert(...) DE AQUÍ!
    }

    public function down(): void
    {
        Schema::dropIfExists('rangos_normales');
    }
};
