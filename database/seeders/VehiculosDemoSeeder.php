<?php

namespace Database\Seeders;

use App\Models\Vehiculo;
use App\Models\Cupo;
use Illuminate\Database\Seeder;

class VehiculosDemoSeeder extends Seeder
{
    public function run(): void
    {
        $vehiculos = [
            ['placa' => '1234ABC', 'tipo_uso' => 'particular', 'tipo_combustible' => 'gasolina', 'marca' => 'Toyota', 'modelo' => 'Corolla 2015', 'foto_url' => 'https://upload.wikimedia.org/wikipedia/commons/4/4f/2015_Toyota_Corolla_%28ZRE172R%29_Ascent_sedan_%282015-11-11%29_01.jpg', 'litros_asignados' => 100],
            ['placa' => '5678XYZ', 'tipo_uso' => 'particular', 'tipo_combustible' => 'gasolina', 'marca' => 'Suzuki', 'modelo' => 'Grand Vitara 2018', 'foto_url' => 'https://upload.wikimedia.org/wikipedia/commons/5/56/2002_Suzuki_Grand_Vitara_TD_2.0.jpg', 'litros_asignados' => 100],
            ['placa' => '2468DEF', 'tipo_uso' => 'transporte_publico', 'tipo_combustible' => 'diesel', 'marca' => 'Toyota', 'modelo' => 'Hiace 2012', 'foto_url' => 'https://upload.wikimedia.org/wikipedia/commons/a/ac/2012_Toyota_Hiace_2.5_GL_Grandia_in_Silver_Metallic%2C_front_left.jpg', 'litros_asignados' => 300],
            ['placa' => '1357GHI', 'tipo_uso' => 'agricola', 'tipo_combustible' => 'diesel', 'marca' => 'Massey Ferguson', 'modelo' => '265', 'foto_url' => 'https://upload.wikimedia.org/wikipedia/commons/4/4f/Massey_Ferguson_265_-_geograph.org.uk_-_5854401.jpg', 'litros_asignados' => 250],
            ['placa' => '9753JKL', 'tipo_uso' => 'carga', 'tipo_combustible' => 'diesel', 'marca' => 'Toyota', 'modelo' => 'Hilux 2010', 'foto_url' => 'https://upload.wikimedia.org/wikipedia/commons/b/bc/Toyota_Hilux_2.4_J_FX_2010_%2820220430%29.jpg', 'litros_asignados' => 200],
        ];

        foreach ($vehiculos as $datos) {
            $litros = $datos['litros_asignados'];
            unset($datos['litros_asignados']);

            $vehiculo = Vehiculo::firstOrCreate(['placa' => $datos['placa']], $datos);

            Cupo::firstOrCreate(
                ['vehiculo_id' => $vehiculo->id, 'periodo' => now()->format('Y-m')],
                ['litros_asignados' => $litros, 'litros_consumidos' => 0]
            );
        }
    }
}