<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

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
    protected static function booted()
    {
        static::created(fn($p) => Log::info('Paciente creado', $p->toArray()));
        static::updated(fn($p) => Log::info('Paciente actualizado', $p->toArray()));
        static::deleted(fn($p) => Log::info('Paciente eliminado', $p->toArray()));
    }
}
