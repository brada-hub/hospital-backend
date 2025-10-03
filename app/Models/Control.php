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
        'tipo',
        'internacion_id',
        'user_id',
        'fecha_control',
        'observaciones',
    ];

    // Relación: Un control pertenece a una internación.
    public function internacion()
    {
        return $this->belongsTo(Internacion::class);
    }

    // Relación: Un control fue creado por un usuario.
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relación: Un control tiene muchos valores (signos vitales).
    public function valores()
    {
        return $this->hasMany(Valor::class);
    }
    protected static function booted()
    {
        static::created(fn($c) => Log::info('Control/Evolución creado', $c->toArray()));
        static::updated(fn($c) => Log::info('Control/Evolución actualizado', $c->toArray()));
        static::deleted(fn($c) => Log::info('Control/Evolución eliminado', $c->toArray()));
    }
}
