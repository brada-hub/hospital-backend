<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cuidado extends Model
{
    use HasFactory;

    protected $fillable = [
        'internacion_id',
        'tipo',
        'descripcion',
        'fecha_inicio',
        'fecha_fin', // Nullable
        'frecuencia', // Por ejemplo: 'Diario', 'Cada 8h', 'A demanda'
        'estado', // 0: Activo, 1: Finalizado
    ];

    // Relación: Un Cuidado pertenece a una Internación
    public function internacion()
    {
        return $this->belongsTo(Internacion::class);
    }

    // Relación: Un Cuidado tiene muchos CuidadosAplicados (registro de su cumplimiento)
    public function cuidadosAplicados()
    {
        return $this->hasMany(CuidadoAplicado::class);
    }
}
