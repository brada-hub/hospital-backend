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
            get: fn($value) => $value ? mb_convert_case(mb_strtolower($value, 'UTF-8'), MB_CASE_TITLE, 'UTF-8') : null,
            set: fn($value) => $value ? strtoupper($value) : null,
        );
    }

    protected function tipo(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? mb_convert_case(mb_strtolower($value, 'UTF-8'), MB_CASE_TITLE, 'UTF-8') : null,
            set: fn($value) => $value ? strtoupper($value) : null,
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
