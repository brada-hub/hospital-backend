<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Medicamento extends Model
{
    use HasFactory;

    protected $table = 'medicamentos';

    protected $fillable = [
        'nombre',
        'descripcion',
        'presentacion',
        'via_administracion',
    ];

    protected static function booted()
    {
        static::created(fn($m) => Log::info('Medicamento creado', $m->toArray()));
        static::updated(fn($m) => Log::info('Medicamento actualizado', $m->toArray()));
        static::deleted(fn($m) => Log::info('Medicamento eliminado', $m->toArray()));
    }
}
