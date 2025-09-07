<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use App\Models\Usuario;
use App\Models\Cuidado;

class CuidadoAplicado extends Model
{
    use HasFactory;

    protected $table = 'cuidados_aplicados';

    protected $fillable = [
        'usuario_id',
        'cuidado_id',
        'fecha_aplicacion',
        'estado',
        'observaciones',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }

    public function cuidado()
    {
        return $this->belongsTo(Cuidado::class);
    }

    protected static function booted()
    {
        static::created(fn($c) => Log::info('Cuidado aplicado creado', $c->toArray()));
        static::updated(fn($c) => Log::info('Cuidado aplicado actualizado', $c->toArray()));
        static::deleted(fn($c) => Log::info('Cuidado aplicado eliminado', $c->toArray()));
    }
}
