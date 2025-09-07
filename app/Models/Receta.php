<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Receta extends Model
{
    use HasFactory;

    protected $table = 'recetas';

    protected $fillable = [
        'tratamiento_id',
        'medicamento_id',
        'frecuencia_medica',
        'concentracion',
        'fecha_inicio',
        'fecha_fin',
    ];

    public function tratamiento()
    {
        return $this->belongsTo(Tratamiento::class);
    }

    public function medicamento()
    {
        return $this->belongsTo(Medicamento::class);
    }

    protected static function booted()
    {
        static::created(fn($r) => Log::info('Receta creada', $r->toArray()));
        static::updated(fn($r) => Log::info('Receta actualizada', $r->toArray()));
        static::deleted(fn($r) => Log::info('Receta eliminada', $r->toArray()));
    }
}
