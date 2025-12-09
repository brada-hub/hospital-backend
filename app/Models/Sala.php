<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    protected $casts = [
        'estado' => 'boolean',
    ];

    /**
     * Formato automático para el campo 'nombre'.
     */
    protected function nombre(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? mb_convert_case(mb_strtolower($value, 'UTF-8'), MB_CASE_TITLE, 'UTF-8') : null,
            set: fn($value) => $value ? strtoupper($value) : null,
        );
    }

    /**
     * Formato automático para el campo 'tipo'.
     */
    protected function tipo(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? mb_convert_case(mb_strtolower($value, 'UTF-8'), MB_CASE_TITLE, 'UTF-8') : null,
            set: fn($value) => $value ? strtoupper($value) : null,
        );
    }

    public function especialidad(): BelongsTo
    {
        return $this->belongsTo(Especialidad::class);
    }

    public function camas(): HasMany
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
