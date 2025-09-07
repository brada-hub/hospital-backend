<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Cuidado extends Model
{
    use HasFactory;

    protected $table = 'cuidados';

    protected $fillable = [
        'internacion_id',
        'tipo',
        'descripcion',
        'fecha_inicio',
        'fecha_fin',
        'frecuencia',
        'estado',
    ];

    public function internacion()
    {
        return $this->belongsTo(Internacion::class);
    }

    protected static function booted()
    {
        static::created(fn($c) => Log::info('Cuidado creado', $c->toArray()));
        static::updated(fn($c) => Log::info('Cuidado actualizado', $c->toArray()));
        static::deleted(fn($c) => Log::info('Cuidado eliminado', $c->toArray()));
    }
}
