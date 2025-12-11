<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'nombre',
        'apellidos',
        'telefono',
        'email',
        'password',
        'rol_id',
        'hospital_id',  // Este campo debe ser asignable masivamente
        'must_change_password',
    ];
    public function internaciones()
    {
        // El 'user_id' en la tabla 'internacions' se relaciona con el 'id' de la tabla 'users'.
        return $this->hasMany(Internacion::class, 'user_id');
    }
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'must_change_password' => 'boolean',
    ];

    // Relación con el hospital
    public function hospital()
    {
        return $this->belongsTo(Hospital::class, 'hospital_id');
    }

    // Relación con el rol
    public function rol()
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'user_permissions')->withPivot('estado');
    }
    public function paciente()
    {
        return $this->hasOne(Paciente::class);
    }
    public function hasPermission(string $permission)
    {
        // 1. Verificar si el permiso está explícitamente negado para este usuario
        $userPermission = $this->permissions->firstWhere('nombre', $permission);
        if ($userPermission && $userPermission->pivot->estado === 'denegado') {
            return false;
        }

        // 2. Si el permiso está explícitamente permitido, otorgar acceso
        if ($userPermission && $userPermission->pivot->estado === 'permitido') {
            return true;
        }

        // 3. Si no hay una configuración individual, verificar el permiso del rol
        return $this->rol->permissions->contains('nombre', $permission);
    }
}
