<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departamentos', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre');
            $table->string('code', 8)->unique();
            $table->string('abbr', 8);
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('departamentos');
    }
};
