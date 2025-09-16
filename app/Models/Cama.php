<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cama extends Model
{
    use HasFactory;

    protected $table = 'camas';

    // AÑADIDO: 'disponibilidad' a la lista de campos asignables.
    protected $fillable = [
        'nombre',
        'tipo',
        'estado',
        'disponibilidad',
        'sala_id',
    ];

    /**
     * AÑADIDO: Conversión de tipos para los nuevos campos.
     * 'estado' se manejará como true/false.
     * 'disponibilidad' se asegurará de ser siempre un número entero.
     */
    protected $casts = [
        'estado' => 'boolean',
        'disponibilidad' => 'integer',
    ];

    public function sala()
    {
        return $this->belongsTo(Sala::class);
    }

    public function ocupaciones(): HasMany
    {
        return $this->hasMany(Ocupacion::class, 'cama_id');
    }

    protected static function booted()
    {
        static::created(fn($c) => Log::info('Cama creada', $c->toArray()));
        static::updated(fn($c) => Log::info('Cama actualizada', $c->toArray()));
        static::deleted(fn($c) => Log::info('Cama eliminada', $c->toArray()));
    }
}
