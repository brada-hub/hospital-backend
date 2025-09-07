<div class="container py-4 px-4 mt-10">
    <div class="overflow-x-auto shadow-md sm:rounded-lg bg-black p-4">
        <!-- Botón Nuevo Rol -->
        <div class="flex justify-between items-center mb-4">
            <button wire:click="abrirModal('create')"
                class="px-4 py-2 bg-black text-white rounded-md hover:bg-red-950">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="icon icon-tabler icons-tabler-outline icon-tabler-ufo">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path
                        d="M16.95 9.01c3.02 .739 5.05 2.123 5.05 3.714c0 2.367 -4.48 4.276 -10 4.276s-10 -1.909 -10 -4.276c0 -1.59 2.04 -2.985 5.07 -3.724" />
                    <path
                        d="M7 9c0 1.105 2.239 2 5 2s5 -.895 5 -2v-.035c0 -2.742 -2.239 -4.965 -5 -4.965s-5 2.223 -5 4.965v.035" />
                    <path d="M15 17l2 3" />
                    <path d="M8.5 17l-1.5 3" />
                    <path d="M12 14h.01" />
                    <path d="M7 13h.01" />
                    <path d="M17 13h.01" />
                </svg>
            </button>

            <!-- Buscador -->
            <div class="flex w-full sm:w-1/2 max-w-md">
                <input type="text" wire:model.lazy="search" placeholder="Buscar rol..."
                    class="text-white w-full px-2 py-2 border border-red-300 rounded-l-md focus:outline-none focus:ring-2 focus:ring-red-950 focus:border-transparent bg-black">
                <button wire:click="$refresh" class="px-2 py-2 bg-red-950 text-white rounded-r-md hover:bg-black">
                    Buscar
                </button>
            </div>
        </div>

        <!-- Tabla -->
        <table class="min-w-full text-sm text-left text-gray-500">
            <thead class="text-xs text-white uppercase bg-red-950">
                <tr>
                    <th class="px-6 py-3">Información</th>
                    <th class="px-6 py-3 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($roles as $role)
                    <tr class="border-b bg-black">
                        <td class="px-6 py-4 text-white">
                            <strong class="block mb-1">Rol:</strong>
                            <p class="mb-2">{{ $role->nombrerol }}</p>
                            <strong class="block mb-1">Descripción:</strong>
                            <p class="mb-2">{{ $role->descripcionrol }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end space-x-2">
                                <!-- Botón de editar -->
                                <button wire:click="editarRol({{ $role->id }})" class="text-blue-600 hover:text-blue-800">
                                    ✏️
                                </button>

                                <!-- Botón de información -->
                                <button wire:click="verDetalle({{ $role->id }})"
                                    class="text-green-600 hover:text-green-800">
                                    ℹ️
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Paginación -->
        <div class="px-6 py-4 text-black">
            {{ $roles->links() }}
        </div>
    </div>

    <!-- Modal crear/editar -->
    @if ($modal)
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50 px-4">
            <div class="bg-white p-6 rounded-md shadow-lg w-full sm:w-1/3 md:w-1/4 lg:w-1/3 xl:w-1/4">
                <h2 class="text-xl font-semibold mb-4">
                    {{ $accion === 'edit' ? 'Editar Rol' : 'Nuevo Rol' }}
                </h2>

                <form wire:submit.prevent="guardarRol">
                    <div class="mb-4">
                        <label for="nombreRol" class="block text-sm font-medium text-gray-700">Nombre del Rol</label>
                        <input type="text" wire:model="nombreRol" id="nombreRol"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            required>
                    </div>

                    <div class="mb-4">
                        <label for="descripcionRol" class="block text-sm font-medium text-gray-700">Descripción del
                            Rol</label>
                        <textarea wire:model="descripcionRol" id="descripcionRol"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                            rows="4"></textarea>
                    </div>

                    <div class="flex justify-end space-x-4">
                        <button type="button" wire:click="cerrarModal"
                            class="px-4 py-2 bg-gray-500 text-white rounded-md">Cancelar</button>
                        <button type="submit"
                            class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Modal de detalles -->
    @if ($detalleModal)
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50 px-4">
            <div class="bg-white p-6 rounded-md shadow-lg w-full sm:w-2/3 md:w-1/2 lg:w-1/3 xl:w-1/3">
                <h2 class="text-xl font-semibold mb-4">Detalles del Rol</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <strong class="block">Nombre del Rol:</strong>
                        <p>{{ $nombreRol }}</p>
                    </div>

                    <div>
                        <strong class="block">Descripción del Rol:</strong>
                        <p>{{ $descripcionRol }}</p>
                    </div>

                    <div class="col-span-1 sm:col-span-2">
                        <strong class="block">Usuarios Asociados:</strong>
                        @if ($usuariosRol && $usuariosRol->isNotEmpty())
                            <ul class="list-disc ml-5">
                                @foreach ($usuariosRol as $usuario)
                                    <li>{{ $usuario->name }} ({{ $usuario->email }})</li>
                                @endforeach
                            </ul>
                        @else
                            <p>No hay usuarios asociados a este rol.</p>
                        @endif
                    </div>
                </div>

                <div class="flex justify-end space-x-4 mt-4">
                    <button type="button" wire:click="cerrarModal"
                        class="px-4 py-2 bg-gray-500 text-white rounded-md">Cerrar</button>
                </div>
            </div>
        </div>
    @endif
</div>