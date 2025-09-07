<div>
    <h1 class="text-white">{{$saludo}}</h1>\
    @for ($i = 0; $i < 4; $i++)
    <span class="text-white">{{$nombres[$i]}}</span> <br>
    @endfor
    @foreach ($nombres as $nombre)
    <span class="text-white">{{$nombre}}</span> <br>
    @endforeach
    @foreach ($roles as $rol)
    <span class="text-white">{{$rol->nombre}}, {{$rol->descripcion}}</span> <br>
    @endforeach
    <div class="container mx-auto p-6">
        <!-- Encabezado con buscador y botón -->
        <div class="flex justify-between items-center mb-6">
            <div class="flex items-center space-x-4">
                <input type="text" id="searchInput" placeholder="Buscar usuario..." class="border border-gray-300 rounded-lg p-2 w-64 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <button wire:click='abrirFormRegistroRol' id="addUserBtn" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition">Agregar Nuevo Rol</button>
        </div>

        <!-- Tabla de usuarios -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descripcion</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Opciones</th>
                    </tr>
                </thead>
                <tbody id="userTable" class="bg-white divide-y divide-gray-200">
                    <!-- Datos de ejemplo -->
                    @foreach ($roles as $rol)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{$rol->id}}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{$rol->nombre}}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{$rol->descripcion}}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                          <button wire:click='abrirFormEdicionRol({{ $rol->id }})'>Editar</button>
                        </td>
                    </tr>
                    @endforeach

                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        <div class="mt-4 flex justify-center space-x-2">
            <button id="prevPage" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 disabled:opacity-50" disabled>Anterior</button>
            <span id="pageInfo" class="px-4 py-2 text-gray-700">Página 1 de 3</span>
            <button id="nextPage" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">Siguiente</button>
        </div>


        <!-- Modal -->
        @if ($modal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center">
            <div class="relative p-5 border w-full max-w-md shadow-lg rounded-lg bg-white">
              <!-- Header -->
              <div class="mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Registrar Rol</h3>
              </div>
              <!-- Body -->
              <div class="mb-4">
                <div class="mb-4">
                  <label for="nombre" class="block text-sm font-medium text-gray-700">Nombre</label>
                  <input wire:model='nombre' type="text" id="nombre" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Nombre del rol">
                </div>
                <div class="mb-4">
                  <label for="descripcion" class="block text-sm font-medium text-gray-700">Descripción</label>
                  <input wire:model='descripcion' type="text" id="descripcion" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Descripción del rol">
                </div>
              </div>
              <!-- Footer -->
              <div class="flex justify-end space-x-3">
                <button wire:click='$set("modal",0)' type="button" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">Cancelar</button>
                @if ($accion == 'Registrar Rol')
                <button wire:click='registrarRol' type="button" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Registrar Rol</button>
                @endif
                @if ($accion == 'Editar Rol')
                <button wire:click='editarRol' type="button" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Editar Rol</button>
                @endif
            </div>
            </div>
          </div>
        @endif
    </div>

</div>
