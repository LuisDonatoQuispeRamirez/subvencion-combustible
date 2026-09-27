<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('estaciones', function (Blueprint $table) {
            $table->string('codigo')->unique()->nullable()->after('ubicacion');
            $table->string('password')->nullable()->after('codigo');
        });
    }

    public function down(): void
    {
        Schema::table('estaciones', function (Blueprint $table) {
            $table->dropColumn(['codigo', 'password']);
        });
    }
};