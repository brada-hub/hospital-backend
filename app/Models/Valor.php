<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Valor extends Model
{
    use HasFactory;

    protected $table = 'valors';

    protected $fillable = [
        'control_id',
        'signo_id',
        'medida',
    ];

    public function control()
    {
        return $this->belongsTo(Control::class);
    }

    public function signo()
    {
        return $this->belongsTo(Signo::class);
    }

    protected static function booted()
    {
        static::created(fn($v) => Log::info('Valor registrado', $v->toArray()));
        static::updated(fn($v) => Log::info('Valor actualizado', $v->toArray()));
        static::deleted(fn($v) => Log::info('Valor eliminado', $v->toArray()));
    }
}
