<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Especialidad extends Model
{
    use HasFactory;

    protected $table = 'especialidads';

    protected $fillable = [
        'nombre',
        'descripcion',
        'estado',
        'hospital_id',
    ];

    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }

    protected static function booted()
    {
        static::created(fn($e) => Log::info('Especialidad creada', $e->toArray()));
        static::updated(fn($e) => Log::info('Especialidad actualizada', $e->toArray()));
        static::deleted(fn($e) => Log::info('Especialidad eliminada', $e->toArray()));
    }
}
