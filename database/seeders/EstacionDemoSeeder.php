<?php

namespace Database\Seeders;

use App\Models\Estacion;
use Illuminate\Database\Seeder;

class EstacionDemoSeeder extends Seeder
{
    public function run(): void
    {
        Estacion::firstOrCreate(
            ['codigo' => 'EST-001'],
            ['nombre' => 'Surtidor Central', 'ubicacion' => 'La Paz', 'password' => 'clave123']
        );
    }
}