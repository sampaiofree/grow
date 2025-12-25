<x-guest-layout>
    <p class="pp">Por favor, utilize as mesmas informações fornecidas no momento da compra da assinatura para garantir o acesso correto ao sistema. </p>
    <p class="pp">Caso ainda não tenha realizado o pagamento, clique <a href="{{ route('comprar_assinatura') }}" style="font-weight: bolder; color: rgb(0, 0, 182);">AQUI</a> para adquirir sua assinatura.</p>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Nome')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div id="div_email" class="mt-4">
            <x-input-label for="email" :value="__('Email - o mesmo usado na compra da assinatura')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Senha')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirme sua senha')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800" href="{{ route('login') }}">
                Já possui um cadastro?
            </a>

            <x-primary-button class="ms-4">
                Cadastrar
            </x-primary-button>
        </div>
    </form>
    <style>
        #div_email label{color: red;}

        .pp{
            font-size: small;
            color: #4f4f4f;
            line-height: 1.3;
            margin-bottom: 10px;
        }
    </style>
</x-guest-layout>
