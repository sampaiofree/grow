<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
        Obrigado por se registrar! Antes de começar, poderia verificar seu endereço de e-mail clicando no link que acabamos de enviar? Se não recebeu o e-mail, podemos enviar outro com prazer.
    </div>

    @if (session('message'))
    <div class="mb-4 font-medium text-sm text-green-600 dark:text-green-400">
        {{ session('message') }}
    </div>
    @endif


    <div class="mt-4 flex items-center justify-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <div>
                <x-primary-button>
                    Reenviar E-mail de Verificação
                </x-primary-button>
            </div>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                {{ __('Log Out') }}
            </button>
        </form>
    </div>
</x-guest-layout>
