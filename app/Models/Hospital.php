<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
// AÑADE ESTA LÍNEA PARA USAR LA NUEVA SINTAXIS
use Illuminate\Database\Eloquent\Casts\Attribute;

class Hospital extends Model
{
    use HasFactory;

    protected $table = 'hospitals';

    protected $fillable = [
        'nombre',
        'departamento',
        'direccion',
        'nivel',
        'tipo',
        'telefono',
    ];

    protected static function booted()
    {
        static::created(fn($h) => Log::info('Hospital creado', $h->toArray()));
        static::updated(fn($h) => Log::info('Hospital actualizado', $h->toArray()));
        static::deleted(fn($h) => Log::info('Hospital eliminado', $h->toArray()));
    }

    // --- AQUÍ EMPIEZA LA MAGIA ---

    /**
     * Regla para el campo 'nombre'.
     * Guarda en MAYÚSCULAS, pero muestra en formato Título.
     */
    protected function nombre(): Attribute
    {
        return Attribute::make(
            // Accessor (get): así se verá cuando lo muestres en la app.
            get: fn($value) => ucwords(strtolower($value)),

            // Mutator (set): así se guardará en la base de datos.
            set: fn($value) => strtoupper($value),
        );
    }

    /**
     * Regla para el campo 'departamento'.
     * Siempre se guarda en MAYÚSCULAS.
     */
    protected function departamento(): Attribute
    {
        return Attribute::make(
            set: fn($value) => strtoupper($value),
        );
    }

    /**
     * Regla para el campo 'nivel'.
     * Siempre se guarda en MAYÚSCULAS.
     */
    protected function nivel(): Attribute
    {
        return Attribute::make(
            set: fn($value) => strtoupper($value),
        );
    }

    /**
     * Regla para el campo 'tipo'.
     * Siempre se guarda en MAYÚSCULAS.
     */
    protected function tipo(): Attribute
    {
        return Attribute::make(
            set: fn($value) => strtoupper($value),
        );
    }
}
