<?php

namespace App\Livewire;

use Livewire\Component;

class Inicio extends Component
{
    public $seleccion = 'Pantallas';
    public function render()
    {
        return view('livewire.inicio');
    }
}
