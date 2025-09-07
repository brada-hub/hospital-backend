<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Internacion extends Model
{
    use HasFactory;

    protected $table = 'internacions';

    protected $fillable = [
        'fecha_ingreso',
        'fecha_alta',
        'motivo',
        'diagnostico',
        'observaciones',
        'paciente_id',
    ];

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    protected static function booted()
    {
        static::created(fn($i) => Log::info('Internación creada', $i->toArray()));
        static::updated(fn($i) => Log::info('Internación actualizada', $i->toArray()));
        static::deleted(fn($i) => Log::info('Internación eliminada', $i->toArray()));
    }
}
