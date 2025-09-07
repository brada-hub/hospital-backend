<div>
    <header
        class="flex justify-between items-center py-2 px-2 mt-1 mx-6 bg-black rounded-lg shadow-lg fixed top-0 left-0 right-0 z-50">
        <div class="flex items-center">
            <!-- Botón de toggle al principio -->
            <button id="toggleMenuButton"
                class="group px-2 py-2 font-medium text-white transition-colors duration-200 sm:px-6 dark:hover:bg-gray-800 dark:text-gray-950 hover:bg-gray-100 rounded-full">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="icon icon-tabler icons-tabler-outline icon-tabler-menu-deep">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path d="M4 6h16" />
                    <path d="M7 12h13" />
                    <path d="M10 18h10" />
                </svg>
            </button>
        </div>

        <div class="flex items-center space-x-4 ml-auto">
            <span class="text-white font-semibold">{{ Auth::user()->name }}</span>

            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit" class="text-white block px-3 py-0 text-sm w-full text-left">
                    {{ __('Log out') }}
                </button>
            </form>
        </div>
    </header>
    <!-- Nav que se puede mostrar/ocultar, ahora se sobrepone al contenido -->
    <nav id="menu"
        class="py-2 px-2 mx-6 mt-16 rounded-lg shadow-lg hidden absolute top-15 left-0 right-0 bg-black z-40 overflow-x-auto">
        <ul class="flex flex-col md:flex-row md:space-x-4 space-y-2 md:space-y-0">

            {{-- Gestión Almacén --}}
            <li class="relative w-full">
                <button data-submenu-toggle="submenuAlmacen" data-arrow="arrowAlmacen"
                    class="text-white px-2 py-2 flex justify-between items-center hover:bg-gray-800 rounded-md w-full">
                    Gestión Almacén
                    <svg id="arrowAlmacen" class="w-4 h-4 transform transition-transform duration-200 ml-2" fill="none"
                        stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>

                <ul id="submenuAlmacen"
                    class="hidden mt-1 space-y-1 bg-black rounded-md shadow-md p-2 z-50 w-full md:absolute md:w-max md:min-w-[160px] md:top-full">
                    <li class="{{ $seleccion === 'Roles' ? 'bg-red-950 rounded-md' : '' }}">
                        <a wire:click="$set('seleccion', 'Roles')"
                            class="block px-2 py-1 text-white hover:bg-red-950 rounded-md cursor-pointer">ROLES</a>
                    </li>
                    <li class="{{ $seleccion === 'Almacenes' ? 'bg-red-950 rounded-md' : '' }}">
                        <a wire:click="$set('seleccion', 'Almacenes')"
                            class="block px-2 py-1 text-white hover:bg-red-950 rounded-md cursor-pointer">ALMACENES</a>
                    </li>
                </ul>
            </li>

            {{-- Gestión Usuarios --}}
            <li class="relative w-full">
                <button data-submenu-toggle="submenuUsuarios" data-arrow="arrowUsuarios"
                    class="text-white px-2 py-2 flex justify-between items-center hover:bg-gray-800 rounded-md w-full">
                    Gestión Usuarios
                    <svg id="arrowUsuarios" class="w-4 h-4 transform transition-transform duration-200 ml-2" fill="none"
                        stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>

                <ul id="submenuUsuarios"
                    class="hidden mt-1 space-y-1 bg-black rounded-md shadow-md p-2 z-50 w-full md:absolute md:w-max md:min-w-[160px] md:top-full">
                    <li>
                        <a class="block px-2 py-1 text-white hover:bg-gray-700 rounded-md cursor-pointer">ROLES</a>
                    </li>
                    <li>
                        <a class="block px-2 py-1 text-white hover:bg-gray-700 rounded-md cursor-pointer">USUARIOS</a>
                    </li>
                </ul>
            </li>


            {{-- Gestión Compras --}}
            <li class="relative w-full">
                <button data-submenu-toggle="submenuCompras" data-arrow="arrowCompras"
                    class="text-white px-2 py-2 flex justify-between items-center hover:bg-gray-800 rounded-md w-full">
                    Gestión Compras
                    <svg id="arrowCompras" class="w-4 h-4 transform transition-transform duration-200 ml-2" fill="none"
                        stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>

                <ul id="submenuCompras"
                    class="hidden mt-1 space-y-1 bg-black rounded-md shadow-md p-2 z-50 w-full md:absolute md:w-max md:min-w-[160px] md:top-full">
                    <li>
                        <a class="block px-2 py-1 text-white hover:bg-gray-700 rounded-md cursor-pointer">ÓRDENES</a>
                    </li>
                    <li>
                        <a
                            class="block px-2 py-1 text-white hover:bg-gray-700 rounded-md cursor-pointer">PROVEEDORES</a>
                    </li>
                </ul>
            </li>



        </ul>
    </nav>
    <main class="w-full min-h-screen dark:bg-black p-5">
        <br><br>
        @livewire('medicamentos')
        <div class="w-full">
            @if ($seleccion == 'Roles')
                @livewire('roles')
            @endif
            @if ($seleccion == 'Almacenes')
                @livewire('almacenes')

            @endif
        </div>
    </main>
</div>

<script>
    const toggleMenuButton = document.getElementById('toggleMenuButton');
    const menu = document.getElementById('menu');
    const body = document.body;

    toggleMenuButton.addEventListener('click', () => {
        menu.classList.toggle('hidden');
    });

    body.addEventListener('click', (e) => {
        if (!toggleMenuButton.contains(e.target) && !menu.contains(e.target)) {
            menu.classList.add('hidden');
        }
    });

    // Manejo dinámico de submenús
    document.querySelectorAll('[data-submenu-toggle]').forEach(button => {
        button.addEventListener('click', function (e) {
            e.stopPropagation();

            const submenuId = this.getAttribute('data-submenu-toggle');
            const arrowId = this.getAttribute('data-arrow');

            const submenu = document.getElementById(submenuId);
            const arrow = document.getElementById(arrowId);

            submenu.classList.toggle('hidden');
            arrow.classList.toggle('rotate-180');
        });
    });
</script>
