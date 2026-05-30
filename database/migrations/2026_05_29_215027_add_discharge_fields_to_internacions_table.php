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
        Schema::table('internacions', function (Blueprint $table) {
            $table->string('tipo_alta', 100)->nullable()->after('fecha_alta');
            $table->text('observaciones_alta')->nullable()->after('tipo_alta');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('internacions', function (Blueprint $table) {
            $table->dropColumn(['tipo_alta', 'observaciones_alta']);
        });
    }
};
