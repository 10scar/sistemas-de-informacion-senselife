<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alertas', function (Blueprint $table): void {
            $table->decimal('frecuencia_cardiaca', 8, 2)->nullable()->after('id_telemetria');
            $table->decimal('frecuencia_respiratoria', 8, 2)->nullable()->after('frecuencia_cardiaca');
        });
    }

    public function down(): void
    {
        Schema::table('alertas', function (Blueprint $table): void {
            $table->dropColumn(['frecuencia_cardiaca', 'frecuencia_respiratoria']);
        });
    }
};
