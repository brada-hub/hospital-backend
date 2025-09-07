<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Cama extends Model
{
    use HasFactory;

    protected $table = 'camas';

    protected $fillable = [
        'nombre',
        'tipo',
        'estado',
        'sala_id',
    ];

    public function sala()
    {
        return $this->belongsTo(Sala::class);
    }

    protected static function booted()
    {
        static::created(fn($c) => Log::info('Cama creada', $c->toArray()));
        static::updated(fn($c) => Log::info('Cama actualizada', $c->toArray()));
        static::deleted(fn($c) => Log::info('Cama eliminada', $c->toArray()));
    }
}
