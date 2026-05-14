<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medico_perfiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('centro_medico_id')->constrained('centros_medicos')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('nombre')->nullable();
            $table->string('apellido')->nullable();
            $table->string('especialidad')->nullable();
            $table->string('sub_especialidad')->nullable();
            $table->string('registro_medico')->nullable();
            $table->string('contacto')->nullable();
            $table->string('extension_interna')->nullable();
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medico_perfiles');
    }
};
