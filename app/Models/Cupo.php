<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cupo extends Model
{
    protected $table = 'cupos';
    protected $fillable = ['vehiculo_id', 'litros_asignados', 'litros_consumidos', 'periodo'];

    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class);
    }
}
