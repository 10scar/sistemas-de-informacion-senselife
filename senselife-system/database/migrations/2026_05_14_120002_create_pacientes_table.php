<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pacientes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('identificador_publico');
            $table->foreignId('centro_medico_id')->constrained('centros_medicos')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('nombre')->nullable();
            $table->string('apellidos')->nullable();
            $table->decimal('peso', 8, 2)->nullable();
            $table->decimal('altura', 8, 2)->nullable();
            $table->string('sexo');
            $table->timestampTz('fecha_alta')->nullable();
            $table->timestampsTz();

            $table->unique(['centro_medico_id', 'identificador_publico']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pacientes');
    }
};
