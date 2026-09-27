<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehiculo extends Model
{
    protected $table = 'vehiculos';
    protected $fillable = ['placa', 'tipo_uso', 'tipo_combustible', 'marca', 'modelo', 'foto_url'];

    public function cupos()
    {
        return $this->hasMany(Cupo::class);
    }

    public function despachos()
    {
        return $this->hasMany(Despacho::class);
    }

    public static function normalizarPlaca(string $placa): string
    {
        return strtoupper(str_replace([' ', '-'], '', $placa));
    }
}