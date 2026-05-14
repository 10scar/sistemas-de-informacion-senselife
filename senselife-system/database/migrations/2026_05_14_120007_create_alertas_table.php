<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alertas', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('paciente_id')->constrained('pacientes')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('telemetry_component')->nullable();
            $table->uuid('telemetry_reading_id')->nullable();
            $table->string('estado')->default('pendiente');
            $table->timestampsTz();

            $table->index(['telemetry_component', 'telemetry_reading_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alertas');
    }
};
