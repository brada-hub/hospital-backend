<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlimentacionTiempo extends Model
{
    protected $fillable = [
        'alimentacion_id',
        'tiempo_comida',
        'descripcion',
        'orden'
    ];

    public function alimentacion()
    {
        return $this->belongsTo(Alimentacion::class);
    }
}
