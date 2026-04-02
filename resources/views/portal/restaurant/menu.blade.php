<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Menu — {{ $tenant->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-accent/25 font-body text-primary">
    <header class="sticky top-0 z-40 bg-white/90 backdrop-blur border-b border-secondary/15">
        <div class="max-w-3xl mx-auto px-4 py-4">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-[11px] uppercase tracking-widest text-primary/45 font-semibold">Restaurant</p>
                    <h1 class="font-heading text-xl font-semibold truncate">{{ $tenant->name }}</h1>
                    @if($tableNumber)
                        <p class="text-xs text-primary/55 mt-0.5">Table {{ $tableNumber }}</p>
                    @endif
                </div>
                <a href="#cart" onclick="openCart()"
                    class="inline-flex items-center gap-2 rounded-xl border border-secondary/25 bg-white px-3 py-2 text-xs font-semibold text-primary hover:bg-accent/20">
                    <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                    Panier
                    <span id="cart-count-pill" class="hidden inline-flex h-5 min-w-5 px-1 rounded-full items-center justify-center text-[10px] font-semibold bg-primary text-secondary"></span>
                </a>
            </div>

            <div class="mt-4 flex items-center gap-2">
                <div class="relative flex-1">
                    <input id="menu-search" type="text" placeholder="Rechercher un plat, une boisson..."
                        class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-secondary/25 bg-white text-sm outline-none focus:border-secondary">
                    <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-primary/30"></i>
                </div>
                <button type="button" onclick="toggleCategories()"
                    class="inline-flex items-center justify-center h-11 w-11 rounded-xl border border-secondary/25 bg-white hover:bg-accent/20">
                    <i data-lucide="filter" class="w-4 h-4 text-primary/70"></i>
                </button>
            </div>

            <div id="category-bar" class="hidden mt-3 overflow-x-auto">
                <div class="flex items-center gap-2 pb-1">
                    <button type="button" data-cat="all" onclick="filterCategory('all')"
                        class="cat-chip px-3 py-1.5 rounded-full text-xs font-semibold border border-secondary/25 bg-white text-primary">
                        Tout
                    </button>
                    @foreach($categories as $category)
                        <button type="button" data-cat="{{ $category->id }}" onclick="filterCategory('{{ $category->id }}')"
                            class="cat-chip px-3 py-1.5 rounded-full text-xs font-semibold border border-secondary/25 bg-white text-primary">
                            {{ $category->name }}
                        </button>
                    @endforeach
                    <button type="button" data-cat="none" onclick="filterCategory('none')"
                        class="cat-chip px-3 py-1.5 rounded-full text-xs font-semibold border border-secondary/25 bg-white text-primary">
                        Autres
                    </button>
                </div>
            </div>
        </div>
    </header>

    <main class="max-w-3xl mx-auto px-4 py-5 pb-28">
        @if($errors->any())
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        <div id="menu-list" class="space-y-3">
            @foreach($items as $item)
                <article
                    data-item
                    data-id="{{ $item->id }}"
                    data-name="{{ $item->name }}"
                    data-price="{{ $item->price }}"
                    data-category="{{ $item->restaurant_menu_category_id ?? 'none' }}"
                    class="rounded-2xl border border-secondary/15 bg-white p-4 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h2 class="font-heading text-base font-semibold text-primary truncate">{{ $item->name }}</h2>
                            @if($item->description)
                                <p class="text-sm text-primary/55 mt-1">{{ $item->description }}</p>
                            @endif
                            <div class="mt-2 flex items-center gap-2 text-xs text-primary/55">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-accent/25 border border-secondary/15 font-semibold">
                                    {{ strtoupper($item->type) }}
                                </span>
                                @if($item->category)
                                    <span class="truncate">{{ $item->category->name }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="flex flex-col items-end gap-2 flex-shrink-0">
                            <p class="text-sm font-semibold text-primary">
                                {{ number_format($item->price / 100, 0, ',', ' ') }} FCFA
                            </p>
                            <div class="flex items-center gap-1.5">
                                <button type="button" onclick="decItem({{ $item->id }})"
                                    class="h-9 w-9 rounded-xl border border-secondary/25 bg-white hover:bg-accent/20 inline-flex items-center justify-center">
                                    <i data-lucide="minus" class="w-4 h-4"></i>
                                </button>
                                <span id="qty-{{ $item->id }}" class="w-9 text-center text-sm font-semibold text-primary">0</span>
                                <button type="button" onclick="incItem({{ $item->id }})"
                                    class="h-9 w-9 rounded-xl bg-primary text-secondary hover:bg-surface-dark inline-flex items-center justify-center">
                                    <i data-lucide="plus" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </main>

    <div class="fixed bottom-0 inset-x-0 z-40">
        <div class="max-w-3xl mx-auto px-4 pb-4">
            <div class="rounded-2xl border border-secondary/20 bg-primary text-secondary shadow-xl px-4 py-3 flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-xs text-secondary/80">Total</p>
                    <p id="cart-total" class="font-heading text-lg font-semibold truncate">0 FCFA</p>
                </div>
                <button type="button" onclick="openCart()"
                    class="inline-flex items-center gap-2 rounded-xl bg-secondary text-primary px-4 py-2 text-xs font-semibold hover:opacity-95">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                    Commander
                </button>
            </div>
        </div>
    </div>

    <div id="cart-modal" class="hidden fixed inset-0 z-50 flex items-end sm:items-center justify-center">
        <div class="absolute inset-0 bg-black/40" onclick="closeCart()"></div>
        <div id="cart" class="relative w-full sm:max-w-xl bg-white rounded-t-3xl sm:rounded-3xl shadow-2xl border border-secondary/15 max-h-[85vh] overflow-hidden">
            <div class="px-5 py-4 border-b border-secondary/15 flex items-center justify-between">
                <div>
                    <p class="font-heading text-lg font-semibold text-primary">Ta commande</p>
                    <p class="text-xs text-primary/45 mt-0.5">Vérifie puis valide</p>
                </div>
                <button type="button" onclick="closeCart()" class="h-9 w-9 rounded-xl border border-secondary/25 inline-flex items-center justify-center hover:bg-accent/20">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <div class="p-5 space-y-4 overflow-y-auto max-h-[calc(85vh-10rem)]">
                <div id="cart-lines" class="space-y-2"></div>

                <div class="rounded-2xl border border-secondary/15 bg-accent/15 p-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-widest text-primary/45 mb-1.5">Nom (optionnel)</label>
                            <input id="customer-name" type="text" class="w-full px-3 py-2 text-sm rounded-xl border border-secondary/25 outline-none focus:border-secondary" placeholder="Ex: Jean">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-widest text-primary/45 mb-1.5">Téléphone (optionnel)</label>
                            <input id="customer-phone" type="text" class="w-full px-3 py-2 text-sm rounded-xl border border-secondary/25 outline-none focus:border-secondary" placeholder="+237...">
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="block text-xs font-semibold uppercase tracking-widest text-primary/45 mb-1.5">Note (optionnel)</label>
                        <textarea id="order-notes" rows="2" class="w-full px-3 py-2 text-sm rounded-xl border border-secondary/25 outline-none focus:border-secondary" placeholder="Sans oignon, bien cuit..."></textarea>
                    </div>
                </div>
            </div>

            <form id="order-form" method="POST" action="{{ route('portal.restaurant.store', ['tenant' => $tenant->slug]) }}" class="p-5 border-t border-secondary/15 bg-white">
                @csrf
                <input type="hidden" name="customer_name" id="customer-name-hidden">
                <input type="hidden" name="customer_phone" id="customer-phone-hidden">
                <input type="hidden" name="notes" id="order-notes-hidden">
                <input type="hidden" name="items_json" id="items-json">

                <div class="mb-4">
                    <label class="block text-xs font-semibold uppercase tracking-widest text-primary/45 mb-1.5">Table</label>
                    <input id="table-number"
                           name="table_number"
                           type="text"
                           maxlength="10"
                           value="{{ $tableNumber }}"
                           placeholder="Ex: 12"
                           required
                           class="w-full px-3 py-2 text-sm rounded-xl border border-secondary/25 outline-none focus:border-secondary">
                    <p class="text-[11px] text-primary/45 mt-1">Si tu as scanne le QR, la table peut deja etre remplie.</p>
                </div>

                <div class="flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-xs text-primary/45">Total</p>
                        <p id="cart-total-modal" class="font-heading text-lg font-semibold text-primary truncate">0 FCFA</p>
                    </div>
                    <button id="submit-order" type="submit" class="inline-flex items-center gap-2 rounded-xl bg-primary text-secondary px-5 py-3 text-xs font-semibold hover:bg-surface-dark">
                        <i data-lucide="send" class="w-4 h-4"></i>
                        Valider
                    </button>
                </div>
            </form>
        </div>
    </div>

<script>
const cart = new Map(); // id -> qty

const formatFCFA = (centimes) => {
    const fcfa = Math.round(Number(centimes || 0) / 100);
    return new Intl.NumberFormat('fr-FR').format(fcfa) + ' FCFA';
};

function getItemEl(id) {
    return document.querySelector(`[data-item][data-id="${id}"]`);
}

function syncQtyUI(id) {
    const qty = cart.get(String(id)) || 0;
    const el = document.getElementById(`qty-${id}`);
    if (el) el.textContent = String(qty);
}

function computeTotal() {
    let total = 0;
    cart.forEach((qty, id) => {
        const el = getItemEl(id);
        if (!el) return;
        const price = Number(el.dataset.price || 0);
        total += price * Number(qty);
    });
    return total;
}

function syncTotalsUI() {
    const total = computeTotal();
    const totalEl = document.getElementById('cart-total');
    const totalModalEl = document.getElementById('cart-total-modal');
    if (totalEl) totalEl.textContent = formatFCFA(total);
    if (totalModalEl) totalModalEl.textContent = formatFCFA(total);

    let count = 0;
    cart.forEach((qty) => count += Number(qty));
    const pill = document.getElementById('cart-count-pill');
    if (pill) {
        if (count > 0) {
            pill.textContent = String(count);
            pill.classList.remove('hidden');
        } else {
            pill.textContent = '';
            pill.classList.add('hidden');
        }
    }
}

function incItem(id) {
    const key = String(id);
    const next = Math.min(99, (cart.get(key) || 0) + 1);
    cart.set(key, next);
    syncQtyUI(id);
    syncTotalsUI();
}

function decItem(id) {
    const key = String(id);
    const next = Math.max(0, (cart.get(key) || 0) - 1);
    if (next <= 0) cart.delete(key);
    else cart.set(key, next);
    syncQtyUI(id);
    syncTotalsUI();
}

function openCart() {
    const modal = document.getElementById('cart-modal');
    if (!modal) return;
    renderCartLines();
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeCart() {
    const modal = document.getElementById('cart-modal');
    if (!modal) return;
    modal.classList.add('hidden');
    document.body.style.overflow = '';
}

function renderCartLines() {
    const container = document.getElementById('cart-lines');
    if (!container) return;

    if (cart.size === 0) {
        container.innerHTML = `
            <div class="rounded-2xl border border-secondary/15 bg-white p-4 text-center">
                <p class="text-sm font-semibold text-primary">Panier vide</p>
                <p class="text-xs text-primary/45 mt-1">Ajoute des articles depuis le menu.</p>
            </div>
        `;
        syncTotalsUI();
        return;
    }

    const lines = [];
    cart.forEach((qty, id) => {
        const el = getItemEl(id);
        if (!el) return;
        const name = el.dataset.name || 'Article';
        const price = Number(el.dataset.price || 0);
        const lineTotal = price * Number(qty);
        lines.push(`
            <div class="rounded-2xl border border-secondary/15 bg-white px-4 py-3 flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-primary truncate">${escapeHtml(name)}</p>
                    <p class="text-xs text-primary/45 mt-0.5">${formatFCFA(price)} x ${qty}</p>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    <button type="button" onclick="decItem(${Number(id)}); renderCartLines();"
                        class="h-9 w-9 rounded-xl border border-secondary/25 bg-white hover:bg-accent/20 inline-flex items-center justify-center">
                        <i data-lucide="minus" class="w-4 h-4"></i>
                    </button>
                    <span class="w-8 text-center text-sm font-semibold text-primary">${qty}</span>
                    <button type="button" onclick="incItem(${Number(id)}); renderCartLines();"
                        class="h-9 w-9 rounded-xl bg-primary text-secondary hover:bg-surface-dark inline-flex items-center justify-center">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                    </button>
                    <span class="w-24 text-right text-sm font-semibold text-primary">${formatFCFA(lineTotal)}</span>
                </div>
            </div>
        `);
    });

    container.innerHTML = lines.join('');
    if (typeof window.refreshLucideIcons === 'function') window.refreshLucideIcons();
    syncTotalsUI();
}

function escapeHtml(value) {
    return (value || '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function toggleCategories() {
    document.getElementById('category-bar')?.classList.toggle('hidden');
}

let selectedCategory = 'all';
function filterCategory(cat) {
    selectedCategory = String(cat);
    document.querySelectorAll('.cat-chip').forEach((chip) => {
        const active = chip.getAttribute('data-cat') === selectedCategory;
        chip.classList.toggle('bg-primary', active);
        chip.classList.toggle('text-secondary', active);
        chip.classList.toggle('border-primary', active);
        chip.classList.toggle('bg-white', !active);
        chip.classList.toggle('text-primary', !active);
    });
    applyFilters();
}

function applyFilters() {
    const q = String(document.getElementById('menu-search')?.value || '').trim().toLowerCase();
    document.querySelectorAll('[data-item]').forEach((el) => {
        const name = String(el.dataset.name || '').toLowerCase();
        const desc = String(el.textContent || '').toLowerCase();
        const cat = String(el.dataset.category || 'none');
        const okCat = selectedCategory === 'all' || selectedCategory === cat;
        const okQ = !q || name.includes(q) || desc.includes(q);
        el.classList.toggle('hidden', !(okCat && okQ));
    });
}

document.getElementById('menu-search')?.addEventListener('input', applyFilters);

document.getElementById('order-form')?.addEventListener('submit', function (e) {
    if (cart.size === 0) {
        e.preventDefault();
        alert('Ajoute au moins un article avant de valider.');
        return;
    }

    const tableValue = String(document.getElementById('table-number')?.value || '').trim();
    if (!tableValue) {
        e.preventDefault();
        alert('Indique le numero de table avant de valider.');
        return;
    }

    const items = [];
    cart.forEach((qty, id) => items.push({ id: Number(id), qty: Number(qty) }));
    document.getElementById('items-json').value = JSON.stringify(items);

    document.getElementById('customer-name-hidden').value = document.getElementById('customer-name')?.value || '';
    document.getElementById('customer-phone-hidden').value = document.getElementById('customer-phone')?.value || '';
    document.getElementById('order-notes-hidden').value = document.getElementById('order-notes')?.value || '';

    document.getElementById('submit-order')?.setAttribute('disabled', 'disabled');
});

syncTotalsUI();
if (typeof window.refreshLucideIcons === 'function') window.refreshLucideIcons();
</script>
</body>
</html>
