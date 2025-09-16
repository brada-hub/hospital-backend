<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Sala extends Model
{
    use HasFactory;

    protected $table = 'salas';

    protected $fillable = [
        'nombre',
        'tipo',
        'estado',
        'especialidad_id',
    ];

    /**
     * AÑADIDO: Le dice a Eloquent que el campo 'estado' es un booleano.
     */
    protected $casts = [
        'estado' => 'boolean',
    ];

    public function especialidad()
    {
        return $this->belongsTo(Especialidad::class);
    }

    public function camas()
    {
        return $this->hasMany(Cama::class);
    }

    protected static function booted()
    {
        static::created(fn($s) => Log::info('Sala creada', $s->toArray()));
        static::updated(fn($s) => Log::info('Sala actualizada', $s->toArray()));
        static::deleted(fn($s) => Log::info('Sala eliminada', $s->toArray()));
    }
}
