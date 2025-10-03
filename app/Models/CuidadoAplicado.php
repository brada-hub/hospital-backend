<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CuidadoAplicado extends Model
{
    use HasFactory;

    // Asumiendo que el nombre de la tabla es 'cuidados_aplicados'
    protected $table = 'cuidados_aplicados';

    protected $fillable = [
        'user_id', // Enfermera que aplica el cuidado
        'cuidado_id',
        'fecha_aplicacion', // Cuándo se realizó (por defecto, Carbon::now())
        // ✅ CORRECCIÓN: Se elimina 'estado' porque la migración ya no lo incluye.
        'observaciones',
    ];

    // Relación: Un Cuidado Aplicado pertenece a un Cuidado
    public function cuidado()
    {
        return $this->belongsTo(Cuidado::class);
    }

    // Relación: Un Cuidado Aplicado fue registrado por un User (Enfermera)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
