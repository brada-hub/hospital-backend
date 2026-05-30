<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Antropometria extends Model
{
    use HasFactory;

    protected $table = 'antropometrias';

    protected $fillable = [
        'internacion_id',
        'peso',
        'altura',
        'imc',
        'observaciones',
    ];

    protected $casts = [
        'peso' => 'float',
        'altura' => 'float',
        'imc' => 'float',
    ];

    /**
     * Relación con la Internación
     */
    public function internacion()
    {
        return $this->belongsTo(Internacion::class);
    }

    /**
     * Hook del modelo para calcular el IMC automáticamente antes de guardar
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($antropometria) {
            if ($antropometria->peso && $antropometria->altura && $antropometria->altura > 0) {
                $alturaM = $antropometria->altura / 100;
                $antropometria->imc = round($antropometria->peso / ($alturaM ** 2), 1);
            } else {
                $antropometria->imc = 0.0;
            }
        });
    }
}
