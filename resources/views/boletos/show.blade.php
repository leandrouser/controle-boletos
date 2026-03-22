<x-app-layout>
    <div class="py-12 bg-[#fafafa] min-h-screen font-sans antialiased text-slate-900">
        <div class="max-w-2xl mx-auto px-4">

            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight">Cobrança</h1>
                    <p class="text-sm text-slate-500">Detalhes e código para pagamento</p>
                </div>
                <div class="flex gap-2">
                    <button onclick="window.print()" class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors border border-slate-200 bg-white hover:bg-slate-100 h-9 px-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                        Imprimir
                    </button>
                </div>
            </div>

            <div class="rounded-lg border border-slate-200 bg-white shadow-sm overflow-hidden">

                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <div class="flex items-center gap-2">
                        <div class="h-2 w-2 rounded-full {{ $boleto->status === 'pago' ? 'bg-emerald-500' : 'bg-amber-500' }}"></div>
                        <span class="text-xs font-medium uppercase tracking-wider text-slate-600">{{ $boleto->status }}</span>
                    </div>
                    <span class="text-[11px] font-medium px-2 py-0.5 rounded bg-slate-200 text-slate-700 uppercase">{{ $tipo }}</span>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-2 gap-y-6 gap-x-4 mb-8">
                        <div class="space-y-1">
                            <p class="text-[11px] font-medium text-slate-400 uppercase tracking-tight">Beneficiário</p>
                            <p class="text-sm font-semibold leading-none">{{ $boleto->beneficiario }}</p>
                        </div>
                        <div class="space-y-1 text-right">
                            <p class="text-[11px] font-medium text-slate-400 uppercase tracking-tight">Vencimento</p>
                            <p class="text-sm font-semibold leading-none {{ $boleto->data_vencimento < now() ? 'text-red-600' : '' }}">
                                {{ \Carbon\Carbon::parse($boleto->data_vencimento)->format('d/m/Y') }}
                            </p>
                        </div>
                        <div class="col-span-2 pt-4 border-t border-slate-50">
                            <p class="text-[11px] font-medium text-slate-400 uppercase tracking-tight mb-2">Valor Total</p>
                            <p class="text-3xl font-bold tracking-tighter text-slate-950 italic">R$ {{ number_format($boleto->valor, 2, ',', '.') }}</p>
                        </div>
                    </div>

                    <div class="space-y-2 mb-8">
                        <label class="text-[11px] font-medium text-slate-400 uppercase tracking-tight">Linha Digitável</label>
                        <div class="flex gap-2">
                            <div id="copy-area" class="flex-1 bg-slate-50 border border-slate-200 rounded-md px-3 py-2 text-sm font-mono text-slate-600 break-all leading-relaxed">
                                {{ $numero }}
                            </div>
                            <button onclick="copyCode()" class="inline-flex items-center justify-center rounded-md bg-slate-900 text-white hover:bg-slate-800 px-3 py-2 text-xs font-medium transition-all active:scale-95">
                                <span id="btn-text">Copiar</span>
                            </button>
                        </div>
                    </div>

                    <div class="flex flex-col items-center justify-center py-6 border-2 border-dashed border-slate-100 rounded-xl bg-white">
                         <div class="barcode-container opacity-80 hover:opacity-100 transition-opacity">
                            {!! $barcode !!}
                         </div>
                         <p class="mt-4 font-mono text-[10px] text-slate-400 tracking-widest">{{ $codigo44 }}</p>
                    </div>

                </div>

                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-between items-center">
                    <a href="{{ route('dashboard') }}" class="text-xs font-medium text-slate-500 hover:text-slate-900 transition-colors">
                        &larr; Voltar ao início
                    </a>
                    <p class="text-[10px] text-slate-400 font-mono">ID: #{{ str_pad($boleto->id, 6, '0', STR_PAD_LEFT) }}</p>
                </div>
            </div>
        </div>
    </div>

    <style>
        .barcode-container svg {
            height: 65px !important;
            width: auto !important;
            max-width: 100%;
        }
        @media print {
            .py-12 { background: white !important; padding: 0 !important; }
            .shadow-sm, .border { border: none !important; box-shadow: none !important; }
            button, a { display: none !important; }
            .bg-slate-50 { background: white !important; }
        }
    </style>

    <script>
        function copyCode() {
            const text = document.getElementById('copy-area').innerText;
            const btnText = document.getElementById('btn-text');

            navigator.clipboard.writeText(text).then(() => {
                btnText.innerText = 'Copiado!';
                btnText.parentElement.classList.replace('bg-slate-900', 'bg-emerald-600');

                setTimeout(() => {
                    btnText.innerText = 'Copiar';
                    btnText.parentElement.classList.replace('bg-emerald-600', 'bg-slate-900');
                }, 2000);
            });
        }
    </script>
</x-app-layout>
