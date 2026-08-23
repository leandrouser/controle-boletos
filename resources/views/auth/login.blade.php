<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
    <div>
    <x-input-label for="login" :value="__('E-mail ou Telefone')" />
    <x-text-input id="login" class="block mt-1 w-full" type="text" name="login" :value="old('login')" required autofocus />
    <x-input-error :messages="$errors->get('login')" class="mt-2" />
</div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <div class="relative">
                <x-text-input id="password" class="block mt-1 w-full pe-10"
                                type="password"
                                name="password"
                                required autocomplete="current-password" />

                <button type="button" id="toggle-password"
                    class="absolute inset-y-0 end-0 flex items-center pe-3 mt-1 text-gray-400 hover:text-gray-600"
                    tabindex="-1" aria-label="Mostrar senha">
                    <i id="toggle-password-icon" class="fas fa-eye"></i>
                </button>
            </div>

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="ms-3">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <script>
        document.getElementById('toggle-password').addEventListener('click', function () {
            const campoSenha = document.getElementById('password');
            const icone      = document.getElementById('toggle-password-icon');
            const mostrando  = campoSenha.type === 'text';

            campoSenha.type = mostrando ? 'password' : 'text';
            icone.classList.toggle('fa-eye');
            icone.classList.toggle('fa-eye-slash');
            this.setAttribute('aria-label', mostrando ? 'Mostrar senha' : 'Ocultar senha');
        });
    </script>
</x-guest-layout>
