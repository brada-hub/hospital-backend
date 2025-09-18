<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MedicamentoCategoria extends Model
{
    use HasFactory;
    protected $fillable = ['nombre'];

    public function medicamentos()
    {
        return $this->hasMany(Medicamento::class, 'categoria_id');
    }
}
