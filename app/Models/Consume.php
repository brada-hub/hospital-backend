<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Consume extends Model
{
    use HasFactory;
    protected $table = 'consumes'; // Se mantiene tu nombre original

    protected $fillable = [
        'tratamiento_id',
        'alimentacion_id',
        'observaciones',
        'fecha',
        'porcentaje_consumido', // CAMBIO
    ];

    public function tratamiento()
    {
        return $this->belongsTo(Tratamiento::class);
    }

    public function alimentacion()
    {
        return $this->belongsTo(Alimentacion::class);
    }
}
