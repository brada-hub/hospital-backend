<?php
// En: app/Models/Especialidad.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

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

    /**
     * AÑADIDO: Le dice a Eloquent que el campo 'estado' es un booleano.
     */
    protected $casts = [
        'estado' => 'boolean',
    ];

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
}
