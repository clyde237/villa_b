@extends('layouts.hotel')

@section('title', 'Détail commande')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-primary">{{ $order->order_number }}</h1>
            <p class="text-secondary mt-1">{{ $order->created_at->locale('fr')->format('d F Y H:i') }}</p>
        </div>
        <a href="{{ route('shop.orders.index') }}" class="text-primary hover:text-primary/70 transition-colors">
            <i data-lucide="arrow-left" class="w-5 h-5 inline mr-2"></i> Retour
        </a>
    </div>

    @if ($message = session('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800">
            <i data-lucide="check-circle" class="w-5 h-5 inline mr-2"></i> {{ $message }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Détails -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Info client -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-primary mb-4">Client</h2>
                <div class="space-y-3">
                    <div>
                        <p class="text-secondary text-sm">Nom</p>
                        <p class="font-medium text-primary">{{ $order->customer_name }}</p>
                    </div>
                    @if ($order->customer_phone)
                        <div>
                            <p class="text-secondary text-sm">Téléphone</p>
                            <p class="font-medium text-primary">{{ $order->customer_phone }}</p>
                        </div>
                    @endif
                    @if ($order->booking)
                        <div>
                            <p class="text-secondary text-sm">Réservation</p>
                            <p class="font-medium text-primary">
                                <a href="{{ route('bookings.show', $order->booking) }}" class="hover:text-primary/70">
                                    {{ $order->booking->customer->name }} - Chambre {{ $order->booking->rooms->first()?->number }}
                                </a>
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Articles -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-primary mb-4">Articles</h2>
                <div class="space-y-3">
                    @foreach ($order->items as $item)
                        <div class="flex justify-between py-3 border-b border-gray-200 last:border-b-0">
                            <div>
                                <p class="font-medium text-primary">{{ $item->product->name }}</p>
                                <p class="text-secondary text-sm">{{ $item->quantity }} × {{ number_format($item->unit_price / 100, 0, ',', ' ') }} FCFA</p>
                            </div>
                            <p class="font-semibold text-primary">
                                {{ number_format($item->item_total / 100, 0, ',', ' ') }} FCFA
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Notes -->
            @if ($order->notes)
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-primary mb-4">Notes</h2>
                    <p class="text-secondary">{{ $order->notes }}</p>
                </div>
            @endif
        </div>

        <!-- Résumé & Actions -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-sm p-6 sticky top-6 space-y-6">
                <!-- Résumé -->
                <div>
                    <h2 class="text-lg font-semibold text-primary mb-4">Résumé</h2>
                    <div class="space-y-3">
                        <div class="flex justify-between text-secondary">
                            <span>Articles :</span>
                            <span>{{ $order->total_items }}</span>
                        </div>
                        <div class="flex justify-between text-secondary">
                            <span>Sous-total :</span>
                            <span>{{ number_format($order->subtotal / 100, 0, ',', ' ') }} FCFA</span>
                        </div>
                        <div class="flex justify-between text-secondary">
                            <span>TVA :</span>
                            <span>{{ number_format($order->tax_amount / 100, 0, ',', ' ') }} FCFA</span>
                        </div>
                        <div class="border-t border-gray-200 pt-3 flex justify-between font-semibold">
                            <span class="text-primary">Total :</span>
                            <span class="text-lg text-primary">{{ number_format($order->total_amount / 100, 0, ',', ' ') }} FCFA</span>
                        </div>
                    </div>
                </div>

                <!-- Statut paiement -->
                <div class="border-t border-gray-200 pt-6">
                    <p class="text-secondary text-sm mb-2">Statut paiement</p>
                    @if ($order->payment_status === 'paid')
                        <span class="bg-green-50 text-green-700 px-4 py-2 rounded-full text-sm font-medium inline-block">Payée</span>
                        @if ($order->paid_at)
                            <p class="text-secondary text-sm mt-2">{{ $order->paid_at->locale('fr')->format('d F Y H:i') }}</p>
                        @endif
                    @elseif ($order->payment_status === 'unpaid')
                        <span class="bg-yellow-50 text-yellow-700 px-4 py-2 rounded-full text-sm font-medium inline-block">En attente</span>
                    @else
                        <span class="bg-red-50 text-red-700 px-4 py-2 rounded-full text-sm font-medium inline-block">Remboursée</span>
                    @endif
                </div>

                <!-- Méthode paiement -->
                @if ($order->payment_method)
                    <div>
                        <p class="text-secondary text-sm mb-2">Méthode</p>
                        <p class="font-medium text-primary">
                            @switch($order->payment_method)
                                @case('cash') Espèces @break
                                @case('mobile_money') Mobile Money @break
                                @case('card') Carte bancaire @break
                                @case('room_charge') Sur chambre @break
                                @default Autre
                            @endswitch
                        </p>
                    </div>
                @endif

                <!-- Actions -->
                <div class="border-t border-gray-200 pt-6 space-y-3">
                    @if ($order->payment_status === 'unpaid')
                        <form action="{{ route('shop.orders.paid', $order) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                                <i data-lucide="check" class="w-4 h-4 inline mr-2"></i> Marquer payée
                            </button>
                        </form>
                    @endif

                    @if ($order->payment_status === 'paid')
                        <form action="{{ route('shop.orders.refund', $order) }}" method="POST" onsubmit="return confirm('Confirmer le remboursement ?');">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                                <i data-lucide="undo" class="w-4 h-4 inline mr-2"></i> Rembourser
                            </button>
                        </form>
                    @endif

                    <button onclick="window.print()" class="w-full bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                        <i data-lucide="printer" class="w-4 h-4 inline mr-2"></i> Imprimer
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
