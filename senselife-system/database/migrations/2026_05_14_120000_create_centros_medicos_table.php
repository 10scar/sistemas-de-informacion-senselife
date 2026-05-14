<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('centros_medicos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('registro_medico')->nullable();
            $table->string('direccion')->nullable();
            $table->string('contacto_celular')->nullable();
            $table->string('correo')->nullable();
            $table->string('estado')->default('activo');
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('centros_medicos');
    }
};
