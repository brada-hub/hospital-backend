<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Log; // <-- Añadido para los logs

class Especialidad extends Model
{
    use HasFactory;

    protected $table = 'especialidads';

    protected $fillable = [
        'nombre',
        'descripcion',
        'estado',
        'hospital_id',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];

    /**
     * Regla para el campo 'nombre'.
     */
    protected function nombre(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? mb_convert_case(mb_strtolower($value, 'UTF-8'), MB_CASE_TITLE, 'UTF-8') : null,
            set: fn($value) => $value ? strtoupper($value) : null,
        );
    }

    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }

    public function salas(): HasMany
    {
        return $this->hasMany(Sala::class, 'especialidad_id');
    }

    public function camas(): HasManyThrough
    {
        return $this->hasManyThrough(Cama::class, Sala::class);
    }

    // AÑADIDO: Logging automático para consistencia
    protected static function booted()
    {
        static::created(fn($e) => Log::info('Especialidad creada', $e->toArray()));
        static::updated(fn($e) => Log::info('Especialidad actualizada', $e->toArray()));
        static::deleted(fn($e) => Log::info('Especialidad eliminada', $e->toArray()));
    }
}
