<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Cama extends Model
{
    use HasFactory;

    protected $table = 'camas';
    protected $fillable = ['nombre', 'tipo', 'estado', 'disponibilidad', 'sala_id'];
    protected $casts = ['estado' => 'boolean', 'disponibilidad' => 'integer'];

    protected function nombre(): Attribute
    {
        return Attribute::make(
            // CORREGIDO: Usamos funciones multibyte para manejar acentos
            get: fn($value) => mb_convert_case(mb_strtolower($value, 'UTF-8'), MB_CASE_TITLE, 'UTF-8'),

            // El 'set' ya funciona bien con acentos, no necesita cambios
            set: fn($value) => strtoupper($value),
        );
    }

    protected function tipo(): Attribute
    {
        return Attribute::make(
            // CORREGIDO: Usamos funciones multibyte para manejar acentos
            get: fn($value) => mb_convert_case(mb_strtolower($value, 'UTF-8'), MB_CASE_TITLE, 'UTF-8'),

            set: fn($value) => strtoupper($value),
        );
    }

    // --- TUS MÉTODOS Y RELACIONES ORIGINALES (INTACTOS) ---

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
