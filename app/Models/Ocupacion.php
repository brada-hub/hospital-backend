<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Ocupacion extends Model
{
    use HasFactory;

    protected $table = 'ocupacions';

    protected $fillable = [
        'internacion_id',
        'cama_id',
        'fecha_ocupacion',
        'fecha_desocupacion',
        'observaciones',
    ];

    public function internacion()
    {
        return $this->belongsTo(Internacion::class);
    }

    public function cama()
    {
        return $this->belongsTo(Cama::class);
    }

    protected static function booted()
    {
        static::created(fn($o) => Log::info('Ocupación registrada', $o->toArray()));
        static::updated(fn($o) => Log::info('Ocupación actualizada', $o->toArray()));
        static::deleted(fn($o) => Log::info('Ocupación eliminada', $o->toArray()));
    }
}
