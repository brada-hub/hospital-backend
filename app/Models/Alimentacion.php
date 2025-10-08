<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alimentacion extends Model
{
    use HasFactory;
    protected $table = 'alimentacions';

    protected $fillable = [
        'internacion_id',
        'tipo_dieta_id',
        'frecuencia',
        'fecha_inicio',
        'fecha_fin',
        'descripcion',
        'estado', // <-- AÑADIR
        'motivo_suspension', // <-- AÑADIR
    ];


    // ... tus relaciones aquí ...
    public function tipoDieta()
    {
        return $this->belongsTo(TipoDieta::class, 'tipo_dieta_id');
    }

    // Es buena idea añadir también la relación inversa
    public function internacion()
    {
        return $this->belongsTo(Internacion::class);
    }
}
