<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RangoNormal extends Model
{
    use HasFactory;

    protected $table = 'rangos_normales';

    protected $fillable = [
        'signo_id',
        'valor_minimo',
        'valor_maximo',
    ];

    protected $casts = [
        'valor_minimo' => 'double',
        'valor_maximo' => 'double',
    ];

    public function signo()
    {
        return $this->belongsTo(Signo::class);
    }

    public function estaFueraDeRango($valor)
    {
        return $valor < $this->valor_minimo || $valor > $this->valor_maximo;
    }
}
