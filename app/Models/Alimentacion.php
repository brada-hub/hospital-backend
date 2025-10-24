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
        'via_administracion',
        'frecuencia_tiempos',
        'restricciones',
        'descripcion',
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'motivo_suspension',
    ];

    protected $casts = [
        'frecuencia_tiempos' => 'integer',
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
    ];

    public function internacion()
    {
        return $this->belongsTo(Internacion::class);
    }

    public function tipoDieta()
    {
        return $this->belongsTo(TipoDieta::class, 'tipo_dieta_id');
    }

    public function consumes()
    {
        return $this->hasMany(Consume::class);
    }

    // <CHANGE> Agregar relación con tiempos
    public function tiempos()
    {
        return $this->hasMany(AlimentacionTiempo::class)->orderBy('orden');
    }

    public function scopeActivas($query)
    {
        return $query->where('estado', 0);
    }

    public function suspender($motivo)
    {
        $this->update([
            'estado' => 1,
            'motivo_suspension' => $motivo,
        ]);
    }
}
