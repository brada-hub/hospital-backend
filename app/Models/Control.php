<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Control extends Model
{
    use HasFactory;

    protected $table = 'controls';

    protected $fillable = [
        'internacion_id',
        'fecha_control',
        'observaciones',
    ];

    public function internacion()
    {
        return $this->belongsTo(Internacion::class);
    }

    protected static function booted()
    {
        static::created(fn($c) => Log::info('Control creado', $c->toArray()));
        static::updated(fn($c) => Log::info('Control actualizado', $c->toArray()));
        static::deleted(fn($c) => Log::info('Control eliminado', $c->toArray()));
    }
}
