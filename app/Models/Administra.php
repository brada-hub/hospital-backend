<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Administra extends Model
{
    use HasFactory;

    protected $table = 'administras';

    protected $fillable = [
        'receta_id',
        'user_id',       // ✅ Corregido a user_id para coincidir con la migración
        'fecha',
        // ❌ Se elimina 'dosis'
        'estado',        // ✅ Ahora se espera un integer
        'observaciones',
    ];

    public function receta()
    {
        return $this->belongsTo(Receta::class);
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }
    protected static function booted()
    {
        static::created(fn($a) => Log::info('Administración registrada', $a->toArray()));
        static::updated(fn($a) => Log::info('Administración actualizada', $a->toArray()));
        static::deleted(fn($a) => Log::info('Administración eliminada', $a->toArray()));
    }
}
