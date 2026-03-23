# Villa Boutanga PMS — Contexte du projet

## Stack technique
- Laravel 12, Blade, TailwindCSS v4, PostgreSQL
- Alpine.js, Lucide Icons
- Laravel Herd (Windows), pgAdmin

## Ce qui est fait

### Infrastructure
- 16 modèles + trait BelongsToTenant (multitenant)
- 16 migrations en 6 vagues + folio_items
- Seeders : Tenant (Villa Boutanga), 4 Users, 4 RoomTypes, 10 Rooms, 50 Customers
- Enums : RoomStatus, BookingStatus
- Services : CheckOutService, LoyaltyService
- Table folio_items (suivi prestations séjour)

### Modules UI terminés
- Layout hotel + Sidebar avec sous-menu Réservations (layouts/hotel.blade.php)
- Composants : sidebar-link, stat-card
- Login page animée aux couleurs Villa Boutanga
- Dashboard temps réel (arrivées, départs, occupation, housekeeping)
- Chambres : liste, vue carte, types CRUD, statuts, historique
- Clients : liste avec filtres fidélité, fiche détaillée, historique séjours, progression niveau
- Réservations individuelles :
  - Wizard création 3 étapes (client → dates → chambre)
  - Détail avec folio complet
  - Check-in / Check-out automatisé
  - Ajout/suppression prestations folio
  - Enregistrement paiements
  - Génération facture + vue impression
  - Edit réservation
- Réservations groupe :
  - Création dossier avec contact principal
  - Ajout/retrait chambres individuelles
  - Prestations de groupe (3 modes : par chambre, par personne, global)
  - Paiement global (2 modes : proportionnel, égal)
  - Check-in / Check-out groupé
  - Facture groupe détaillée par chambre
  - Edit + Annulation dossier
- Housekeeping basique (changement statuts)
- Factures : vue individuelle + groupe, impression isolée

## Conventions importantes
- Prix stockés en centimes (45000 FCFA = 4500000 en base)
- Affichage : ceil() pour éviter les virgules sur les arrondis TVA
- Tolérance 100 centimes sur validation paiement (arrondis TVA 19,25%)
- Couleurs via variables CSS : text-primary, text-secondary, text-accent, bg-surface-dark
- Icônes via Lucide : <i data-lucide="nom" class="w-4 h-4"></i>
- Recherche live : debounce 400ms sur oninput
- Modals : JS vanilla avec classList.remove('hidden')
- data-* attributes pour passer données aux modals (évite bugs apostrophes)
- Redirects : toujours redirect()->route() jamais back() sur les POST

## Règles métier clés
- Folio : ajout/suppression prestations uniquement en checked_in
- Check-out : solde dû doit être 0 (tolérance 100 centimes)
- Points fidélité : 1pt/1000 FCFA, multiplicateurs par niveau
- Niveaux : bronze(0) silver(5M) gold(20M) platinum(50M) FCFA cumulés
- Chambre après check-out → statut 'cleaning' (pas 'available')
- Groupe : dates bloquées si chambres en checked_in
- Groupe : annulation impossible si status in_house ou completed
- Prestation groupe : label "(groupe GRP-XXXX)" ajouté à la description

## Ce qu'il reste à faire

### Priorité haute
- ❌ Middleware RBAC (limiter accès selon le rôle)
- ❌ Module Utilisateurs (gestion staff par le manager)
- ❌ Housekeeping complet (liste priorités, assignation staff)

### Priorité moyenne
- ❌ Module Restaurant (commandes, menu, lien folio)
- ❌ Rapports (taux occupation, revenus, fidélité)
- ❌ PDF facture via DomPDF

### Technique
- ❌ Notifications email (confirmation réservation, rappel départ)
- ❌ BookingFactory + BookingSeeder
- ❌ Refactoring recalculateTotals en méthode unique
- ❌ Notes internes (internal_notes) dans les vues

## Structure des fichiers clés
- app/Models/           → tous les modèles
- app/Services/         → CheckOutService, LoyaltyService
- app/Enums/            → RoomStatus, BookingStatus
- app/Http/Controllers/ → BookingController, GroupBookingController,
                          RoomController, CustomerController,
                          HousekeepingController, InvoiceController
- resources/views/layouts/hotel.blade.php  → layout principal
- resources/views/components/sidebar-link.blade.php
- resources/views/components/stat-card.blade.php
- resources/views/bookings/  → index, create, select-room, show, edit
- resources/views/groups/    → index, create, show, edit, invoice
- resources/views/customers/ → index, show
- resources/views/rooms/     → index, show
- resources/views/invoices/  → show