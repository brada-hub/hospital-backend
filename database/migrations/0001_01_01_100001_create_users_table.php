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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('apellidos');
            $table->integer('telefono');
            $table->string('email')->unique();
            $table->string('password');
            $table->boolean('estado')->default(1);
            $table->foreignId('rol_id')->constrained('rols')->onDelete('cascade');


           // $table->string('name');
           // $table->string('email')->unique();
           // $table->timestamp('email_verified_at')->nullable();
           // $table->string('password');
            $table->rememberToken();
            $table->timestamps();
           // $table->foreignId('role_id')->nullable()->constrained('roles')->onDelete('cascade'); // role_id opcional
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
