<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Rol extends Model
{
    use HasFactory;

    protected $table = 'rols';

    // ✅ 1. Añadido 'estado' a los campos rellenables.
    protected $fillable = ['nombre', 'descripcion', 'estado'];

    /**
     * ✅ 2. Se asegura que el 'estado' sea tratado como booleano.
     * Esto convierte automáticamente 1/0 a true/false.
     */
    protected $casts = [
        'estado' => 'boolean',
    ];

    public function usuarios()
    {
        // 🚨 OJO: Asegúrate que el modelo User existe en App\Models\User
        return $this->hasMany(User::class, 'rol_id');
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permissions');
    }

    protected static function booted()
    {
        static::created(function ($rol) {
            Log::info('Rol creado: ', $rol->toArray());
        });

        static::updated(function ($rol) {
            Log::info('Rol actualizado: ', $rol->toArray());
        });

        static::deleted(function ($rol) {
            Log::info('Rol eliminado: ', $rol->toArray());
        });
    }
}
