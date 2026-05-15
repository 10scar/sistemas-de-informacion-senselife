<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('centros_medicos', function (Blueprint $table): void {
            $table->foreignId('departamento_id')
                ->nullable()
                ->after('nombre')
                ->constrained('departamentos')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreignId('municipio_id')
                ->nullable()
                ->after('departamento_id')
                ->constrained('municipios')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->date('fecha_vinculacion')->nullable()->after('correo');
        });
    }

    public function down(): void
    {
        Schema::table('centros_medicos', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('municipio_id');
            $table->dropConstrainedForeignId('departamento_id');
            $table->dropColumn('fecha_vinculacion');
        });
    }
};
