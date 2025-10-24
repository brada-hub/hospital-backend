<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notificacion extends Model
{
    use HasFactory;

    protected $table = 'notificaciones';

    protected $fillable = [
        'user_id',
        'internacion_id',
        'control_id',
        'tipo',
        'titulo',
        'mensaje',
        'leida',
    ];

    protected $casts = [
        'leida' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function internacion()
    {
        return $this->belongsTo(Internacion::class);
    }

    public function control()
    {
        return $this->belongsTo(Control::class);
    }

    public function scopeNoLeidas($query)
    {
        return $query->where('leida', false);
    }

    public function scopeCriticas($query)
    {
        return $query->where('tipo', 'critica');
    }

    public function marcarComoLeida()
    {
        $this->update(['leida' => true]);
    }
}
