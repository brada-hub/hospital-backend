<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notificaciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('Doctor que recibe la notificación');
            $table->unsignedBigInteger('internacion_id');
            $table->unsignedBigInteger('control_id')->nullable()->comment('Control que generó la alerta');
            $table->string('tipo', 50)->comment('critica, advertencia, recordatorio');
            $table->string('titulo', 191);
            $table->text('mensaje');
            $table->boolean('leida')->default(false);
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('internacion_id')
                ->references('id')
                ->on('internacions')
                ->onDelete('cascade');

            $table->foreign('control_id')
                ->references('id')
                ->on('controls')
                ->onDelete('set null');

            $table->index(['user_id', 'leida']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificaciones');
    }
};
