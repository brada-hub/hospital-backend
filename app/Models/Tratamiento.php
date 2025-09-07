<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Tratamiento extends Model
{
    use HasFactory;

    protected $table = 'tratamientos';

    protected $fillable = [
        'tipo',
        'descripcion',
        'fecha_inicio',
        'fecha_fin',
        'observaciones',
        'internacion_id',
    ];

    public function internacion()
    {
        return $this->belongsTo(Internacion::class);
    }

    protected static function booted()
    {
        static::created(fn($t) => Log::info('Tratamiento creado', $t->toArray()));
        static::updated(fn($t) => Log::info('Tratamiento actualizado', $t->toArray()));
        static::deleted(fn($t) => Log::info('Tratamiento eliminado', $t->toArray()));
    }
}
