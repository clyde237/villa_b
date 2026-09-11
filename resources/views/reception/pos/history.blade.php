@extends('layouts.pos')

@section('title', 'Historique des Ventes POS Réception')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <a href="{{ route('reception.pos.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-primary/60 hover:text-primary transition-colors mb-2">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                Retour au terminal POS
            </a>
            <h1 class="text-2xl font-heading font-bold text-primary flex items-center gap-2">
                <i data-lucide="history" class="w-6 h-6 text-primary"></i>
                Historique des Ventes POS Réception
            </h1>
            <p class="text-xs text-secondary mt-0.5">Consultation et réimpression des opérations de vente effectuées à la réception</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('reception.pos.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary text-white text-xs font-bold rounded-xl hover:bg-surface-dark transition-all shadow-sm">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Nouvelle vente POS
            </a>
        </div>
    </div>

    {{-- Filtres de recherche --}}
    <div class="bg-white rounded-2xl p-4 border border-secondary/15 shadow-xs mb-6">
        <form method="GET" action="{{ route('reception.pos.history') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3 items-end">
            <div class="sm:col-span-2">
                <label class="block text-[10px] font-bold uppercase tracking-wider text-primary/50 mb-1">Recherche (N° vente, client, chambre)</label>
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Ex: POS-REC, 204, Dupont..."
                           class="w-full pl-9 pr-3 py-2 text-xs border border-secondary/30 rounded-xl outline-none focus:border-primary">
                    <i data-lucide="search" class="w-4 h-4 text-primary/40 absolute left-3 top-2.5"></i>
                </div>
            </div>

            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-primary/50 mb-1">Statut</label>
                <select name="payment_status" class="w-full px-3 py-2 text-xs border border-secondary/30 rounded-xl bg-white outline-none focus:border-primary">
                    <option value="">Tous les statuts</option>
                    <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Payé immédiatement</option>
                    <option value="charged_to_room" {{ request('payment_status') === 'charged_to_room' ? 'selected' : '' }}>Débité sur chambre</option>
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="flex-1 py-2 px-4 bg-primary text-white text-xs font-bold rounded-xl hover:bg-surface-dark transition-colors">
                    Filtrer
                </button>
                @if(request()->hasAny(['search', 'payment_status', 'date']))
                    <a href="{{ route('reception.pos.history') }}" class="py-2 px-3 bg-secondary/10 hover:bg-secondary/20 text-primary text-xs font-bold rounded-xl transition-colors" title="Réinitialiser">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Tableau des ventes --}}
    <div class="bg-white rounded-2xl border border-secondary/15 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-accent/20 border-b border-secondary/15 text-[10px] font-bold uppercase tracking-wider text-primary/60">
                        <th class="py-3 px-4">Date / Heure</th>
                        <th class="py-3 px-4">N° Vente</th>
                        <th class="py-3 px-4">Client / Chambre</th>
                        <th class="py-3 px-4">Articles</th>
                        <th class="py-3 px-4 text-right">Total TTC</th>
                        <th class="py-3 px-4">Règlement</th>
                        <th class="py-3 px-4">Statut</th>
                        <th class="py-3 px-4 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-secondary/10">
                    @forelse($sales as $sale)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3 px-4 whitespace-nowrap text-primary/70">
                                {{ $sale->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="py-3 px-4 font-mono font-bold text-primary">
                                {{ $sale->sale_number }}
                            </td>
                            <td class="py-3 px-4">
                                <p class="font-bold text-primary">{{ $sale->customer_name }}</p>
                                @if($sale->room_number)
                                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-blue-800">
                                        <i data-lucide="bed" class="w-3 h-3"></i> Chambre {{ $sale->room_number }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-primary/70 max-w-xs truncate">
                                {{ $sale->items->pluck('name')->join(', ') }}
                            </td>
                            <td class="py-3 px-4 text-right font-extrabold text-primary font-heading whitespace-nowrap">
                                {{ $sale->formattedTotal() }}
                            </td>
                            <td class="py-3 px-4 text-primary/80 whitespace-nowrap">
                                {{ $sale->paymentMethodLabel() }}
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <span class="inline-block text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full {{ $sale->payment_status === 'charged_to_room' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                    {{ $sale->paymentStatusLabel() }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                <a href="{{ route('reception.pos.receipt', $sale) }}"
                                   class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold text-primary bg-secondary/10 hover:bg-secondary/20 transition-colors">
                                    <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                                    <span>Reçu</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-primary/40">
                                <i data-lucide="receipt" class="w-10 h-10 mx-auto mb-2 opacity-30"></i>
                                <p class="font-medium text-xs">Aucune vente enregistrée pour le moment.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($sales->hasPages())
            <div class="p-4 border-t border-secondary/10">
                {{ $sales->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
