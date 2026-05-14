<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paciente_asociaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dispositivo_id')->constrained('dispositivos')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignUuid('paciente_id')->constrained('pacientes')->cascadeOnUpdate()->restrictOnDelete();
            $table->timestampTz('fecha_retiro')->nullable();
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paciente_asociaciones');
    }
};
