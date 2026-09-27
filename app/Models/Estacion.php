<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Estacion extends Authenticatable
{
    protected $table = 'estaciones';
    protected $fillable = ['nombre', 'ubicacion', 'codigo', 'password'];
    protected $hidden = ['password'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function despachos()
    {
        return $this->hasMany(Despacho::class);
    }
}