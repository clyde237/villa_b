@extends('layouts.hotel')

@section('title', 'Nouvelle commande')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8 flex items-center justify-between">
        <div>
            <a href="{{ route('shop.orders.index') }}" class="text-xs text-primary/50 hover:text-primary transition-colors flex items-center gap-1 mb-2">
                <i data-lucide="arrow-left" class="w-3 h-3"></i>
                Retour aux commandes
            </a>
            <h1 class="text-3xl font-heading font-bold text-primary flex items-center gap-2">
                <i data-lucide="shopping-cart" class="w-7 h-7 text-primary/80"></i>
                Nouvelle commande
            </h1>
            <p class="text-sm text-secondary mt-1 ml-9">Point de vente boutique</p>
        </div>
    </div>

    <form action="{{ route('shop.orders.store') }}" method="POST" class="space-y-6" x-data="orderItemsDef(@js($products))">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Formulaire de commande -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Informations client (Avec recherche et création rapide) -->
                <div class="bg-white rounded-xl shadow-sm border border-secondary/10 p-6 flex flex-col gap-4 relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-1 h-full bg-blue-400"></div>
                    <div class="flex items-center pb-3 border-b border-secondary/10">
                        <i data-lucide="user" class="w-5 h-5 text-primary mr-2"></i>
                        <h2 class="text-lg font-heading font-semibold text-primary">Informations Client</h2>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-widest text-primary/50 mb-1.5">Rechercher un client *</label>
                        <x-customer-search 
                            :customers="$customers" 
                        name="customer_id" 
                        :value="old('customer_id')" 
                        :allow-creation="true"
                        creation-label="Créer vite fait"
                    >
                        <div class="flex justify-between items-center bg-blue-50/50 text-blue-800 p-3 rounded-lg border border-blue-100/50 mb-4">
                            <div class="flex items-center">
                                <i data-lucide="user-plus" class="w-4 h-4 mr-2"></i>
                                <span class="font-medium text-sm">Création d'un nouveau client</span>
                            </div>
                            <button type="button" @click="cancelCreatingNew()" class="text-xs font-medium hover:underline focus:outline-none text-blue-600">Annuler</button>
                        </div>

                        <input type="hidden" name="create_customer" :value="isCreatingNew ? '1' : '0'">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 px-1">
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-widest text-primary/50 mb-1.5">Prénom *</label>
                                <input type="text" name="customer_first_name" x-model="customerFirstName" :required="isCreatingNew"
                                       class="w-full px-3 py-2 text-sm border border-secondary/30 rounded-lg text-primary outline-none focus:border-secondary transition-colors">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-widest text-primary/50 mb-1.5">Nom *</label>
                                <input type="text" name="customer_name" x-model="customerName" :required="isCreatingNew"
                                       class="w-full px-3 py-2 text-sm border border-secondary/30 rounded-lg text-primary outline-none focus:border-secondary transition-colors @error('customer_name') border-red-500 @enderror">
                                @error('customer_name')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-widest text-primary/50 mb-1.5">Téléphone</label>
                                <input type="text" name="customer_phone" x-model="customerPhone"
                                       class="w-full px-3 py-2 text-sm border border-secondary/30 rounded-lg text-primary outline-none focus:border-secondary transition-colors">
                            </div>
                        </div>
                    </x-customer-search>
                    </div>

                    <div class="mt-2 pt-4 border-t border-secondary/10">
                        <label class="block text-xs font-semibold uppercase tracking-widest text-primary/50 mb-1.5">Associé à une chambre (Optionnel)</label>
                        <select name="booking_id"
                                class="w-full px-3 py-2.5 text-sm border border-secondary/30 rounded-lg text-primary outline-none focus:border-secondary transition-colors">
                            <option value="">-- Ignorer --</option>
                            @foreach ($bookings as $booking)
                                <option value="{{ $booking->id }}" {{ old('booking_id') == $booking->id ? 'selected' : '' }}>
                                    Chambre {{ $booking->room?->number ?? '—' }} ({{ $booking->customer?->full_name ?? '—' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Articles -->
                <div class="bg-white rounded-xl shadow-sm border border-secondary/10 p-6 relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-1 h-full bg-green-400"></div>
                    <div class="flex items-center pb-3 border-b border-secondary/10 mb-5">
                        <i data-lucide="shopping-bag" class="w-5 h-5 text-primary mr-2"></i>
                        <h2 class="text-lg font-heading font-semibold text-primary">Contenu de la commande</h2>
                    </div>

                    <div id="items-container" class="space-y-4 mb-6">
                        <template x-for="(item, index) in items" :key="item.id">
                            <div class="item-row grid grid-cols-1 md:grid-cols-12 gap-4 pb-4 border-b border-secondary/10 relative group">
                                <div class="md:col-span-5">
                                    <label class="block text-xs font-semibold uppercase tracking-widest text-primary/50 mb-1.5">Article *</label>
                                    <div class="relative" @click.away="item.showDropdown = false">
                                        <input type="text" x-model="item.search" @focus="item.showDropdown = true" @input="item.product_id = ''; item.showDropdown = true" class="w-full px-3 py-2 text-sm border border-secondary/30 rounded-lg text-primary outline-none focus:border-secondary transition-colors" placeholder="Rechercher un article...">
                                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                            <i data-lucide="chevron-down" class="w-4 h-4 text-secondary/50"></i>
                                        </div>
                                        <div x-show="item.showDropdown" class="absolute z-50 mt-1 w-full bg-white rounded-md shadow-lg border border-gray-200 overflow-hidden" style="display: none;" x-transition>
                                            <ul class="max-h-60 overflow-auto py-1">
                                                <template x-for="p in filteredProducts(item.search)" :key="p.id">
                                                    <li @click="selectProduct(index, p)" class="cursor-pointer px-4 py-2 hover:bg-gray-100 text-sm flex justify-between items-center group/item">
                                                        <span x-text="p.name" class="text-primary font-medium group-hover/item:text-primary"></span>
                                                        <span x-text="formatPrice(p.price)" class="text-xs text-primary/60 group-hover/item:text-primary/70"></span>
                                                    </li>
                                                </template>
                                                <li x-show="filteredProducts(item.search).length === 0" class="px-4 py-2 text-sm text-gray-500">Aucun produit trouvé</li>
                                            </ul>
                                        </div>
                                        <input type="hidden" :name="`items[${index}][product_id]`" :value="item.product_id" required>
                                    </div>
                                </div>

                                <div class="md:col-span-3">
                                    <label class="block text-xs font-semibold uppercase tracking-widest text-primary/50 mb-1.5">Prix unitaire</label>
                                    <input type="text" readonly :value="formatPrice(getProductPrice(item.product_id))" class="unit-price w-full px-3 py-2 text-sm border border-secondary/20 rounded-lg bg-accent/5 text-primary outline-none">
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-xs font-semibold uppercase tracking-widest text-primary/50 mb-1.5">Qté *</label>
                                    <input type="number" :name="`items[${index}][quantity]`" x-model.number="item.quantity" min="1" required class="quantity w-full px-3 py-2 text-sm border border-secondary/30 rounded-lg text-primary outline-none focus:border-secondary transition-colors">
                                </div>

                                <div class="md:col-span-2 relative">
                                    <label class="block text-xs font-semibold uppercase tracking-widest text-primary/50 mb-1.5">Total</label>
                                    <input type="text" readonly :value="formatPrice(getItemTotal(item))" class="item-total w-full px-3 py-2 text-sm border border-secondary/20 rounded-lg bg-accent/5 font-semibold text-primary outline-none">
                                    
                                    <button type="button" @click="removeItem(index)" x-show="items.length > 1" class="absolute -right-2 top-8 w-8 h-8 flex items-center justify-center rounded-lg text-red-400 hover:bg-red-50 hover:text-red-500 opacity-0 group-hover:opacity-100 transition-all" title="Supprimer">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>

                    <button type="button" @click="addItem()" class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-primary bg-secondary/10 hover:bg-secondary/20 rounded-lg transition-colors border border-secondary/20">
                        <i data-lucide="plus" class="w-4 h-4 mr-2"></i> Ajouter un article
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Méthode de paiement -->
                    <div class="bg-white rounded-xl shadow-sm border border-secondary/10 p-6 relative overflow-hidden">
                        <div class="absolute top-0 left-0 w-1 h-full bg-purple-400"></div>
                        <div class="flex items-center pb-3 border-b border-secondary/10 mb-5">
                            <i data-lucide="credit-card" class="w-5 h-5 text-primary mr-2"></i>
                            <h2 class="text-lg font-heading font-semibold text-primary">Paiement</h2>
                        </div>

                        <label class="block text-xs font-semibold uppercase tracking-widest text-primary/50 mb-1.5">Méthode de règlement *</label>
                        <select name="payment_method" required
                                class="w-full px-3 py-2 text-sm border border-secondary/30 rounded-lg text-primary outline-none focus:border-secondary transition-colors @error('payment_method') border-red-500 @enderror">
                            <option value="">-- Sélectionner --</option>
                            <option value="cash" {{ old('payment_method') === 'cash' ? 'selected' : '' }}>Espèces</option>
                            <option value="mobile_money" {{ old('payment_method') === 'mobile_money' ? 'selected' : '' }}>Mobile Money</option>
                            <option value="card" {{ old('payment_method') === 'card' ? 'selected' : '' }}>Carte bancaire</option>
                            <option value="room_charge" {{ old('payment_method') === 'room_charge' ? 'selected' : '' }}>Débiter sur la chambre</option>
                            <option value="other" {{ old('payment_method') === 'other' ? 'selected' : '' }}>Autre</option>
                        </select>
                        @error('payment_method')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Notes -->
                    <div class="bg-white rounded-xl shadow-sm border border-secondary/10 p-6 relative overflow-hidden">
                        <div class="absolute top-0 left-0 w-1 h-full bg-orange-400"></div>
                        <div class="flex items-center pb-3 border-b border-secondary/10 mb-5">
                            <i data-lucide="file-text" class="w-5 h-5 text-primary mr-2"></i>
                            <h2 class="text-lg font-heading font-semibold text-primary">Notes additionnelles</h2>
                        </div>
                        <label class="block text-xs font-semibold uppercase tracking-widest text-primary/50 mb-1.5">Observation (Optionnel)</label>
                        <textarea name="notes" rows="3"
                                  class="w-full px-3 py-2 text-sm border border-secondary/30 rounded-lg text-primary outline-none focus:border-secondary transition-colors resize-none placeholder-primary/30" placeholder="Une précision particulière sur cette commande...">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Récapitulatif -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-sm border border-secondary/10 overflow-hidden sticky top-6">
                    <div class="bg-accent/5 p-6 border-b border-secondary/10">
                        <div class="flex items-center text-primary mb-1">
                            <i data-lucide="receipt" class="w-5 h-5 mr-2"></i>
                            <h2 class="text-lg font-heading font-semibold">Récapitulatif</h2>
                        </div>
                        <p class="text-xs text-primary/50">Montant à régler en caisse</p>
                    </div>

                    <div class="p-6 space-y-4">
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-primary/70">Sous-total</span>
                            <span class="font-medium text-primary" x-text="formatPrice(subtotal)">0 FCFA</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-primary/70">TVA (19,25%)</span>
                            <span class="font-medium text-primary" x-text="formatPrice(tax)">0 FCFA</span>
                        </div>
                        
                        <div class="border-t-2 border-dashed border-secondary/20 pt-4 mt-2">
                            <div class="flex justify-between items-end">
                                <span class="font-semibold text-primary">Total à payer</span>
                                <span class="text-2xl font-heading font-bold text-primary" x-text="formatPrice(total)">0 FCFA</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50/50 p-6 border-t border-secondary/10 space-y-3">
                        <button type="submit" class="w-full flex justify-center items-center gap-2 bg-primary hover:bg-surface-dark text-white px-6 py-3 rounded-xl font-medium transition-colors shadow-sm">
                            <i data-lucide="check-circle" class="w-5 h-5"></i>
                            Valider la commande
                        </button>
                        <a href="{{ route('shop.orders.index') }}" class="block w-full text-center hover:bg-secondary/10 text-primary/70 px-6 py-3 rounded-xl font-medium transition-colors text-sm border border-secondary/20">
                            Annuler
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
