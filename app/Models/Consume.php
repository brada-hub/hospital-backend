<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Consume extends Model
{
    use HasFactory;

    protected $table = 'consumes';

    protected $fillable = [
        'tratamiento_id',
        'alimentacion_id',
        'observaciones',
        'fecha',
        'estado',
    ];

    public function tratamiento()
    {
        return $this->belongsTo(Tratamiento::class);
    }

    public function alimentacion()
    {
        return $this->belongsTo(Alimentacion::class);
    }

    protected static function booted()
    {
        static::created(fn($c) => Log::info('Consume registrado', $c->toArray()));
        static::updated(fn($c) => Log::info('Consume actualizado', $c->toArray()));
        static::deleted(fn($c) => Log::info('Consume eliminado', $c->toArray()));
    }
}
