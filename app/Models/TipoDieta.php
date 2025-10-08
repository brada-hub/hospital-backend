<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoDieta extends Model
{
    use HasFactory;
    protected $table = 'tipos_dieta';
    protected $fillable = ['nombre', 'descripcion'];

    public function alimentaciones()
    {
        return $this->hasMany(Alimentacion::class, 'tipo_dieta_id');
    }
}
