<?php

namespace App\Livewire;

use App\Models\Role;
use Livewire\Component;
use Livewire\WithPagination;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

class Roles extends Component
{
    use WithPagination;

    public $search = '';
    public $modal = false;
    public $detalleModal = false;
    public $accion = 'create';

    public $rolId = null;
    public $nombreRol = '';
    public $descripcionRol = '';
    public $usuariosRol = null;

    protected $paginationTheme = 'tailwind';

    public function render()
    {
        $roles = Role::with('users')
            ->when($this->search, function ($query) {
                $query->where('nombrerol', 'like', '%' . $this->search . '%')
                    ->orWhere('descripcionrol', 'like', '%' . $this->search . '%');
            })
            ->paginate(5);

        return view('livewire.roles', compact('roles'));
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function abrirModal($accion)
    {
        $this->reset(['rolId', 'nombreRol', 'descripcionRol', 'usuariosRol']);
        $this->accion = $accion;
        $this->modal = true;
        $this->detalleModal = false;
    }

    public function editarRol($id)
    {
        $rol = Role::findOrFail($id);
        $this->rolId = $rol->id;
        $this->nombreRol = $rol->nombrerol;
        $this->descripcionRol = $rol->descripcionrol;
        $this->accion = 'edit';
        $this->modal = true;
        $this->detalleModal = false;
    }

    public function verDetalle($id)
    {
        $rol = Role::with('users')->findOrFail($id);
        $this->rolId = $rol->id;
        $this->nombreRol = $rol->nombrerol;
        $this->descripcionRol = $rol->descripcionrol;
        $this->usuariosRol = $rol->users;
        $this->modal = false;
        $this->detalleModal = true;
    }

    public function guardarRol()
    {
        $this->validate([
            'nombreRol' => 'required|string|max:255',
            'descripcionRol' => 'nullable|string|max:255',
        ]);

        try {
            if ($this->accion === 'edit' && $this->rolId) {
                $rol = Role::findOrFail($this->rolId);
                $rol->update([
                    'nombrerol' => $this->nombreRol,
                    'descripcionrol' => $this->descripcionRol,
                ]);

                LivewireAlert::title('Rol actualizado con éxito.')
                    ->success()
                    ->show();
            } else {
                Role::create([
                    'nombrerol' => $this->nombreRol,
                    'descripcionrol' => $this->descripcionRol,
                ]);

                LivewireAlert::title('Rol creado con éxito.')
                    ->success()
                    ->show();
            }

            $this->cerrarModal();
        } catch (\Exception $e) {
            LivewireAlert::title('Error: ' . $e->getMessage())
                ->error()
                ->show();
        }
    }

    public function cerrarModal()
    {
        $this->modal = false;
        $this->detalleModal = false;
        $this->reset(['rolId', 'nombreRol', 'descripcionRol', 'usuariosRol']);
        $this->resetErrorBag();
    }
}
