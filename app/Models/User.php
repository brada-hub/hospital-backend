<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // <-- IMPORTANTE

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable; // <-- Agregamos HasApiTokens

    protected $fillable = [
        'nombre',
        'apellidos',
        'telefono',
        'email',
        'password',
        'rol_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed', // Laravel 11 ya soporta
    ];

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'rol_id'); // Un usuario pertenece a un rol
    }
}
