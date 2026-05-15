<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dispositivos', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->foreignId('modelo_id')->constrained('modelos')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('numero_serie')->nullable();
            $table->foreignId('centro_medico_id')->nullable()->constrained('centros_medicos')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('estado')->default('activo');
            $table->string('ubicacion')->nullable();
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dispositivos');
    }
};
