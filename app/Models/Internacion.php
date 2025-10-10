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
        'user_id', // médico que internó al paciente
    ];

    protected $casts = [
        'fecha_ingreso' => 'datetime',
        'fecha_alta' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELACIONES
    |--------------------------------------------------------------------------
    */

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    public function medico()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function cuidados()
    {
        return $this->hasMany(Cuidado::class);
    }

    public function tratamientos()
    {
        return $this->hasMany(Tratamiento::class);
    }

    public function controles()
    {
        return $this->hasMany(Control::class);
    }

    public function alimentaciones()
    {
        return $this->hasMany(Alimentacion::class);
    }

    public function ocupaciones()
    {
        return $this->hasMany(Ocupacion::class, 'internacion_id');
    }

    public function ocupacionActiva()
    {
        return $this->hasOne(Ocupacion::class)
            ->whereNull('fecha_desocupacion');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES Y MÉTODOS AUXILIARES
    |--------------------------------------------------------------------------
    */

    // Scope para internaciones activas (sin alta)
    public function scopeActivas($query)
    {
        return $query->whereNull('fecha_alta');
    }

    // Scope para filtrar por médico autenticado
    public function scopeDelMedico($query, $medicoId)
    {
        return $query->where('user_id', $medicoId);
    }

    /*
    |--------------------------------------------------------------------------
    | EVENTOS DE MODELO (Logs)
    |--------------------------------------------------------------------------
    */

    protected static function booted()
    {
        static::created(fn($i) => Log::info('🟢 Internación creada', ['id' => $i->id]));
        static::updated(fn($i) => Log::info('🟡 Internación actualizada', ['id' => $i->id]));
        static::deleted(fn($i) => Log::warning('🔴 Internación eliminada', ['id' => $i->id]));
    }
}
