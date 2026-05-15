<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('municipios', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('code', 8)->unique();
            $table->foreignId('id_departamento')
                ->constrained('departamentos')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->timestampsTz();

            $table->index(['id_departamento', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('municipios');
    }
};
