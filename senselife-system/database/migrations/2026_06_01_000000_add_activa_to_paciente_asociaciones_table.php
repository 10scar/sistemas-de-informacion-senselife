<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('paciente_asociaciones', function (Blueprint $table): void {
            $table->boolean('activa')->default(true)->after('fecha_retiro');
        });

        DB::table('paciente_asociaciones')
            ->whereNotNull('fecha_retiro')
            ->update(['activa' => false]);
    }

    public function down(): void
    {
        Schema::table('paciente_asociaciones', function (Blueprint $table): void {
            $table->dropColumn('activa');
        });
    }
};
