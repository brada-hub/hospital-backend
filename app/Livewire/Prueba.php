<?php

namespace App\Livewire;

use App\Models\Rol;
use Livewire\Component;

class Prueba extends Component
{
    public $saludo = 'Hola mundo';
    public $nombres = ['alex','brayan','jade','rodri'];
    public $roles;
    public $nombre;
    public $descripcion;

    public $modal = 0;
    public $accion = '';
    public $rol;
    public function render()
    {
        $this->roles = Rol::all();
        return view('livewire.prueba');
    }

    public function registrarRol()
    {
        $rol = new Rol;
        $rol->nombre = $this->nombre;
        $rol->descripcion = $this->descripcion;
        $rol->save();
        $this->limpiarFormulario();
    }
    public function editarRol()
    {
        $rol = Rol::find($this->rol->id);
        $rol->nombre = $this->nombre;
        $rol->descripcion = $this->descripcion;
        $rol->save();
        $this->limpiarFormulario();
    }
    public function abrirFormRegistroRol()
    {
        $this->modal = 1;
        $this->accion = 'Registrar Rol';
    }
    public function abrirFormEdicionRol($id)
    {
        $this->modal = 1;
        $this->accion = 'Editar Rol';
        $this->rol =Rol::find($id);
        $this->nombre = $this->rol->nombre;
        $this->descripcion = $this->rol->descripcion;
    }
    public function limpiarFormulario()
    {
        $this->modal = 0;
        $this->nombre = null;
        $this->descripcion = null;
    }
} // <---- ESTE } FINAL te está faltando en tu archivo
