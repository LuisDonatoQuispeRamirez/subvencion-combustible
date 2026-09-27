<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Despacho extends Model
{
    protected $table = 'despachos';
    protected $fillable = ['vehiculo_id', 'estacion_id', 'litros_despachados', 'fecha_hora'];

    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class);
    }

    public function estacion()
    {
        return $this->belongsTo(Estacion::class);
    }
}
