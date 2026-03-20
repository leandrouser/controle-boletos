<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Visualizar Código de Barras') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-8 text-center">

                {{-- Badge do tipo do boleto --}}
                <div class="mb-6">
                    @if($tipo === 'bancario')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                            🏦 Boleto Bancário
                        </span>
                    @elseif($tipo === 'convenio')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                            ⚡ Convênio / Concessionária
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                            ⚠️ Tipo não identificado
                        </span>
                    @endif
                </div>

                {{-- Aviso de erro --}}
                @if($aviso)
                    <div class="bg-red-50 border border-red-300 text-red-800 px-4 py-3 rounded mb-6 text-left text-sm">
                        ⚠️ {{ $aviso }}
                    </div>
                @endif

                {{-- Dados do boleto --}}
                <div class="grid grid-cols-2 gap-4 text-left mb-8 bg-gray-50 p-4 rounded border">
                    <div>
                        <span class="text-xs text-gray-500 uppercase tracking-wide">Beneficiário</span>
                        <p class="font-semibold text-gray-800 mt-1">{{ $boleto->beneficiario }}</p>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 uppercase tracking-wide">Valor</span>
                        <p class="font-semibold text-gray-800 mt-1">R$ {{ number_format($boleto->valor, 2, ',', '.') }}</p>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 uppercase tracking-wide">Vencimento</span>
                        <p class="font-semibold text-gray-800 mt-1">
                            {{ \Carbon\Carbon::parse($boleto->data_vencimento)->format('d/m/Y') }}
                        </p>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 uppercase tracking-wide">Status</span>
                        <p class="font-semibold mt-1 {{ $boleto->status === 'pago' ? 'text-green-600' : 'text-red-500' }}">
                            {{ ucfirst($boleto->status) }}
                        </p>
                    </div>
                </div>

                {{-- Linha digitável original --}}
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-2 text-left">Linha Digitável</h3>
                <div class="bg-gray-100 p-4 rounded mb-6 font-mono text-base border break-all text-left">
                    {{ $numero }}
                    <span class="text-xs text-gray-400 ml-2">({{ $tamanho }} dígitos)</span>
                </div>

                {{-- Código de barras gerado (44 dígitos) --}}
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-2 text-left">Código de Barras (44 dígitos)</h3>
                <div class="bg-gray-100 p-4 rounded mb-6 font-mono text-sm border break-all text-left text-gray-600">
                    {{ $codigo44 }}
                </div>

                {{-- SVG do código de barras --}}
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-4 text-left">Código de Barras</h3>
                <div class="overflow-x-auto mb-8 border rounded p-4 bg-white">
                    <div style="width: 100%; min-width: 600px;">
                        <div class="barcode-svg-wrapper">
                            {!! $barcode !!}
                        </div>
                    </div>
                </div>

                <div class="flex justify-between items-center border-t pt-6">
                    <a href="{{ route('dashboard') }}" class="text-blue-600 hover:underline text-sm">
                        &larr; Voltar para a lista
                    </a>
                    <button onclick="window.print()" class="bg-black text-white px-6 py-2 rounded font-bold shadow hover:bg-gray-800 transition text-sm">
                        🖨️ Imprimir / Salvar PDF
                    </button>
                </div>

            </div>
        </div>
    </div>

    <style>
        .barcode-svg-wrapper svg {
            width: 100% !important;
            height: 100px !important;
            display: block;
        }
        @media print {
            body { background: white; }
            .barcode-svg-wrapper svg {
                width: 100% !important;
                height: 100px !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            button, a { display: none; }
        }
    </style>
</x-app-layout>