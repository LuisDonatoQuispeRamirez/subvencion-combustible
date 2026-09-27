<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehiculos', function (Blueprint $table) {
            $table->string('marca')->nullable()->after('tipo_combustible');
            $table->string('modelo')->nullable()->after('marca');
            $table->string('foto_url')->nullable()->after('modelo');
        });
    }

    public function down(): void
    {
        Schema::table('vehiculos', function (Blueprint $table) {
            $table->dropColumn(['marca', 'modelo', 'foto_url']);
        });
    }
};