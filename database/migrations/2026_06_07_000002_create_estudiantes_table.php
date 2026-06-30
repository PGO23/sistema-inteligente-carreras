<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('estudiantes')) {
            return;
        }

        Schema::create('estudiantes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('correo', 100);
            $table->foreignId('carrera_id')->constrained('carreras');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estudiantes');
    }
};
