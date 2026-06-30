<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('estudiantes', function (Blueprint $table) {
            $table->string('estado', 20)->default('pendiente')->after('carrera_id');
            $table->dateTime('fecha_solicitud')->useCurrent()->after('estado');
            $table->dateTime('fecha_envio')->nullable()->after('fecha_solicitud');
        });
    }

    public function down(): void
    {
        Schema::table('estudiantes', function (Blueprint $table) {
            $table->dropColumn(['estado', 'fecha_solicitud', 'fecha_envio']);
        });
    }
};
