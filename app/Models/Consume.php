<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Consume extends Model
{
    use HasFactory;

    protected $table = 'consumes';

    protected $fillable = [
        'tratamiento_id',
        'alimentacion_id',
        'tiempo_comida',
        'fecha',
        'porcentaje_consumido',
        'observaciones',
        'registrado_por',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function tratamiento()
    {
        return $this->belongsTo(Tratamiento::class);
    }

    public function alimentacion()
    {
        return $this->belongsTo(Alimentacion::class);
    }

    public function registradoPor()
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }
}
