# Villa Boutanga PMS — Contexte du projet

## Stack technique
- Laravel 12, Blade, TailwindCSS v4, PostgreSQL
- Alpine.js, Lucide Icons
- Laravel Herd (Windows), pgAdmin

## Ce qui est fait
- 16 modèles + trait BelongsToTenant (multitenant)
- 16 migrations en 6 vagues
- Seeders : Tenant (Villa Boutanga), 4 Users, 4 RoomTypes, 10 Rooms, 50 Customers
- Enums : RoomStatus, BookingStatus
- Services : CheckOutService, LoyaltyService
- Table folio_items (suivi prestations séjour)

## Modules UI terminés
- Layout hotel + Sidebar (layouts/hotel.blade.php)
- Login page
- Dashboard temps réel
- Chambres (liste, carte, types CRUD, statuts)
- Clients (liste, fiche, fidélité)
- Réservations (wizard création, check-in/out, folio, paiements, facture)
- Housekeeping (basique)

## Conventions importantes
- Prix stockés en centimes (45000 FCFA = 4500000 en base)
- Couleurs via variables CSS : text-primary, text-secondary, text-accent, bg-surface-dark
- Icônes via Lucide : <i data-lucide="nom" class="w-4 h-4"></i>
- Recherche live : debounce 400ms sur oninput
- Modals : JS vanilla avec classList.remove('hidden')
- data-* attributes pour passer données aux modals (évite bugs apostrophes)

## Règles métier clés
- Folio : ajout prestations uniquement en checked_in
- Check-out : solde dû doit être 0
- Points fidélité : 1pt/1000 FCFA, multiplicateurs par niveau
- Niveaux : bronze(0) silver(5M) gold(20M) platinum(50M) FCFA cumulés
- Chambre après check-out → statut 'cleaning' (pas 'available')

## Ce qu'il reste à faire
- Edit réservation
- Réservation de groupe
- Middleware RBAC
- Module Utilisateurs
- Housekeeping complet
- Module Restaurant
- Rapports
- PDF facture (DomPDF)
- Notifications email
- BookingFactory

## Structure des fichiers clés
- app/Models/ → tous les modèles
- app/Services/ → CheckOutService, LoyaltyService
- app/Enums/ → RoomStatus, BookingStatus
- resources/views/layouts/hotel.blade.php → layout principal
- resources/views/components/sidebar-link.blade.php
- resources/views/components/stat-card.blade.php