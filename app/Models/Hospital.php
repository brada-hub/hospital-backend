<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

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
}
