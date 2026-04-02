<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Recu restaurant #{{ $order->id }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
        }
    </style>
</head>

<body class="min-h-screen bg-accent/25 font-body text-primary">
    <div class="max-w-xl mx-auto px-4 py-6">
        <div class="no-print flex items-center justify-between gap-2 mb-4">
            <a href="{{ route('restaurant.billing.show', $order) }}" class="inline-flex items-center gap-2 px-4 py-2 border border-secondary/25 bg-white text-primary text-xs font-semibold rounded-lg hover:bg-accent/20">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                Retour
            </a>
            <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white text-xs font-semibold rounded-lg hover:bg-surface-dark">
                <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                Imprimer
            </button>
        </div>

        <div class="rounded-2xl border border-secondary/15 bg-white shadow-sm overflow-hidden">
            <div class="px-5 py-5 border-b border-secondary/15">
                <p class="text-[11px] uppercase tracking-widest text-primary/45 font-semibold">Recu restaurant</p>
                <h1 class="font-heading text-xl font-semibold">Commande #{{ $order->id }}</h1>
                <p class="text-xs text-primary/55 mt-1">
                    Table {{ $order->table_number }}
                    · {{ $order->placed_at?->format('d/m/Y H:i') }}
                </p>
            </div>

            <div class="px-5 py-4">
                <div class="flex items-center justify-between text-sm">
                    <p class="text-primary/55">Paiement</p>
                    <p class="font-semibold text-primary">
                        {{ strtoupper($order->payment_status ?? 'unpaid') }}
                        @if($order->payment_method) · {{ strtoupper(str_replace('_', ' ', $order->payment_method)) }} @endif
                    </p>
                </div>

                <div class="mt-4 divide-y divide-secondary/10">
                    @foreach($order->items as $line)
                        <div class="py-3 flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-primary truncate">{{ $line->item_name }}</p>
                                <p class="text-xs text-primary/45 mt-0.5">
                                    {{ number_format($line->unit_price / 100, 0, ',', ' ') }} FCFA x {{ (int) $line->quantity }}
                                </p>
                            </div>
                            <p class="text-sm font-semibold text-primary flex-shrink-0">
                                {{ number_format($line->total_price / 100, 0, ',', ' ') }} FCFA
                            </p>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 pt-4 border-t border-secondary/15 flex items-center justify-between">
                    <p class="text-sm font-semibold text-primary">Total</p>
                    <p class="font-heading text-lg font-semibold text-primary">
                        {{ number_format($order->total_amount / 100, 0, ',', ' ') }} FCFA
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
    if (typeof window.refreshLucideIcons === 'function') window.refreshLucideIcons();
    </script>
</body>
</html>

