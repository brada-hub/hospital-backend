<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class Receta extends Model
{
    use HasFactory;

    protected $table = 'recetas';

    protected $fillable = [
        'tratamiento_id',
        'medicamento_id',
        'dosis',
        'via_administracion',
        'frecuencia_horas',
        'duracion_dias',
        'indicaciones',
        'estado',
    ];

    // Nuevo accessor para calcular la fecha de fin
    public function getFechaFinAttribute()
    {
        // La fecha de inicio de la receta es la misma que la del tratamiento padre
        // Por eso accedemos a la relación 'tratamiento'
        return Carbon::parse($this->tratamiento->fecha_inicio)->addDays($this->duracion_dias);
    }

    public function tratamiento()
    {
        return $this->belongsTo(Tratamiento::class);
    }

    public function medicamento()
    {
        return $this->belongsTo(Medicamento::class);
    }

    public function administras()
    {
        return $this->hasMany(Administra::class);
    }

    protected static function booted()
    {
        static::created(fn($r) => Log::info('Receta creada', $r->toArray()));
        static::updated(fn($r) => Log::info('Receta actualizada', $r->toArray()));
        static::deleted(fn($r) => Log::info('Receta eliminada', $r->toArray()));
    }
}
