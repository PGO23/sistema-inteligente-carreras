<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('carreras')) {
            return;
        }

        Schema::create('carreras', function (Blueprint $table) {
            $table->id();
            $table->string('carrera', 100);
            $table->text('descripcion')->nullable();
            $table->string('malla_curricular')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carreras');
    }
};
