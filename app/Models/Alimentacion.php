<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Alimentacion extends Model
{
    use HasFactory;

    protected $table = 'alimentacions';

    protected $fillable = [
        'tipo_dieta',
        'frecuencia',
        'fecha_inicio',
        'fecha_fin',
        'descripcion',
    ];

    protected static function booted()
    {
        static::created(fn($a) => Log::info('Alimentación creada', $a->toArray()));
        static::updated(fn($a) => Log::info('Alimentación actualizada', $a->toArray()));
        static::deleted(fn($a) => Log::info('Alimentación eliminada', $a->toArray()));
    }
}
