<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

/**
 * @property-read \App\Models\User|null $user
 * @property-read \App\Models\Internacion|null $internacionActiva
 */
class Paciente extends Model
{
    use HasFactory;

    protected $table = 'pacientes';

    protected $fillable = [
        'ci',
        'nombre',
        'apellidos',
        'fecha_nacimiento',
        'genero',
        'telefono',
        'direccion',
        'nombre_referencia',
        'apellidos_referencia',
        'celular_referencia',
        'user_id',
        'estado'
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];

    public function internacionActiva()
    {
        return $this->hasOne(Internacion::class)
            ->whereNull('fecha_alta')
            ->latest('fecha_ingreso');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted()
    {
        static::created(fn($p) => Log::info('Paciente creado', $p->toArray()));
        static::updated(fn($p) => Log::info('Paciente actualizado', $p->toArray()));
        static::deleted(fn($p) => Log::info('Paciente eliminado', $p->toArray()));
    }
}
