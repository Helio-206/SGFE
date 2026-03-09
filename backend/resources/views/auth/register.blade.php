<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Nome Completo -->
        <div>
            <x-input-label for="nome" :value="__('Nome Completo')" />
            <x-text-input id="nome" class="block mt-1 w-full" type="text" name="nome" :value="old('nome')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('nome')" class="mt-2" />
        </div>

        <!-- Username -->
        <div class="mt-4">
            <x-input-label for="username" :value="__('Nome de Utilizador')" />
            <x-text-input id="username" class="block mt-1 w-full" type="text" name="username" :value="old('username')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('username')" class="mt-2" />
        </div>

        <!-- Email -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="email" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Instituição (Vínculo Obrigatório) -->
        <div class="mt-4">
            <x-input-label for="id_inst" :value="__('Instituição')" />
            <select id="id_inst" name="id_inst" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                <option value="">-- Selecione a Instituição --</option>
                @foreach ($instituicoes as $inst)
                    <option value="{{ $inst->id_inst }}" {{ old('id_inst') == $inst->id_inst ? 'selected' : '' }}>
                        {{ $inst->codigo }} — {{ $inst->nome }}
                    </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('id_inst')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Palavra-passe')" />
            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirmar Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirmar Palavra-passe')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                {{ __('Já tem conta?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Registar') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
