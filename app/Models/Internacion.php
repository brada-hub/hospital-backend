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
        'user_id', // médico que internó
    ];

    // Relaciones
    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }
    public function cuidados()
    {
        // Asegúrate de que el modelo Cuidado existe y está en App\Models\Cuidado
        return $this->hasMany(Cuidado::class);
    }
    public function tratamientos()
    {
        // Trae todos los tratamientos asociados a esta internación
        return $this->hasMany(Tratamiento::class);
    }
    public function ocupacionActiva()
    {
        // Busca la ocupación donde la fecha de desocupación AÚN NO ha sido establecida (es NULL).
        return $this->hasOne(Ocupacion::class)->whereNull('fecha_desocupacion');
    }

    public function medico()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function ocupaciones()
    {
        return $this->hasMany(Ocupacion::class, 'internacion_id');
    }

    public function controls()
    {
        return $this->hasMany(Control::class);
    }

    // Logs
    protected static function booted()
    {
        static::created(fn($i) => Log::info('Internación creada', $i->toArray()));
        static::updated(fn($i) => Log::info('Internación actualizada', $i->toArray()));
        static::deleted(fn($i) => Log::info('Internación eliminada', $i->toArray()));
    }
}
