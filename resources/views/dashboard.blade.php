<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight text-center">
            {{ __('Área de Pagamentos') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-md mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <div class="text-center">
                    <div class="mb-4 text-green-600">
                        <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Boleto Disponível</h3>
                    <p class="text-gray-500 text-sm mb-6">Clique no botão abaixo para visualizar o código de barras para pagamento.</p>

                    <a href="{{ route('boletos.show', ['id' => 1]) }}" 
                    class="inline-flex items-center justify-center w-full px-6 py-4 bg-blue-600 border border-transparent rounded-lg font-bold text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 transition shadow-lg">
                        <span class="mr-2">📊</span>
                        Visualizar Código de Barras
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>