<x-guest-layout>
    <div class="flex justify-center items-center w-full h-full bg-slate-950">
        <div class="w-full max-w-md p-8 bg-slate-950 rounded-lg shadow-lg">
            <div class="flex justify-center mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="icon icon-tabler icons-tabler-outline icon-tabler-bubble-tea text-white">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path
                        d="M17.95 9l-1.478 8.69c-.25 1.463 -.374 2.195 -.936 2.631c-1.2 .931 -6.039 .88 -7.172 0c-.562 -.436 -.687 -1.168 -.936 -2.632l-1.478 -8.689" />
                    <path d="M6 9l.514 -1.286a5.908 5.908 0 0 1 10.972 0l.514 1.286" />
                    <path d="M5 9h14" />
                    <path d="M12 9l4 -7" />
                    <path d="M10.01 14h.01" />
                    <path d="M11.02 18h.01" />
                    <path d="M13.02 16h.01" />
                </svg>
            </div>
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}" class="p-6 bg-slate-950 rounded-lg shadow-lg">
                @csrf

                <!-- Email Address -->
                <div class="mb-4">
                    <x-input-label for="email" :value="__('Email')" class="block text-sm font-medium text-white" />
                    <x-text-input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm text-white bg-slate-800" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2 text-red-600 text-sm" />
                </div>

                <!-- Password -->
                <div class="mb-4">
                    <x-input-label for="password" :value="__('Password')"
                        class="block text-sm font-medium text-white" />
                    <x-text-input type="password" name="password" id="password" required autocomplete="current-password"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm text-white bg-slate-800" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2 text-red-600 text-sm" />
                </div>


                <div class="mt-6">
                    <x-primary-button
                        class="w-full py-2 px-4 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-opacity-50">
                        {{ __('Log in') }}
                    </x-primary-button>
                </div>
            </form>

           
        </div>
    </div>
</x-guest-layout>
