<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Administra extends Model
{
    use HasFactory;

    protected $table = 'administras';

    protected $fillable = [
        'receta_id',
        'hora_programada',
        'user_id',
        'fecha',
        'estado',        // 0 = pendiente, 1 = cumplida, 2 = cumplida con retraso
        'observaciones',
    ];

    protected $casts = [
        'hora_programada' => 'datetime',
        'fecha' => 'datetime',
    ];

    public function receta()
    {
        return $this->belongsTo(Receta::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted()
    {
        static::created(fn($a) => Log::info('Administración registrada', $a->toArray()));
        static::updated(fn($a) => Log::info('Administración actualizada', $a->toArray()));
    }
}
