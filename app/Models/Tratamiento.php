<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Tratamiento extends Model
{
    use HasFactory;

    protected $table = 'tratamientos';

    protected $fillable = [
        'tipo',
        'descripcion',
        'fecha_inicio',
        'fecha_fin',
        'observaciones',
        'estado',
        'internacion_id',
        'user_id',
    ];
    protected $casts = [
        'estado' => 'integer',
    ];
    public function internacion()
    {
        return $this->belongsTo(Internacion::class);
    }

    public function recetas()
    {
        return $this->hasMany(Receta::class);
    }

    public function medico()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    protected static function booted()
    {
        static::created(fn($t) => Log::info('Tratamiento creado', $t->toArray()));
        static::updated(fn($t) => Log::info('Tratamiento actualizado', $t->toArray()));
        static::deleted(fn($t) => Log::info('Tratamiento eliminado', $t->toArray()));
    }
}
