<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consentimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('paciente_id')->constrained('pacientes')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('tutor_identificacion');
            $table->string('hash_documento')->nullable();
            $table->timestampTz('fecha_creacion')->useCurrent();
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consentimientos');
    }
};
