<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Signo extends Model
{
    use HasFactory;

    protected $table = 'signos';

    protected $fillable = [
        'nombre',
        'unidad'
    ];

    protected static function booted()
    {
        static::created(fn($s) => Log::info('Signo creado', $s->toArray()));
        static::updated(fn($s) => Log::info('Signo actualizado', $s->toArray()));
        static::deleted(fn($s) => Log::info('Signo eliminado', $s->toArray()));
    }
}
