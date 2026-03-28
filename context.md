# Villa Boutanga PMS â€” Contexte du projet

## Stack technique
- Laravel 12, Blade, TailwindCSS v4, PostgreSQL
- Alpine.js, Lucide Icons
- Laravel Herd (Windows), pgAdmin

## Ce qui est fait

### Infrastructure
- 16 modÃ¨les + trait BelongsToTenant (multitenant)
- 16 migrations en 6 vagues + folio_items
- Seeders : Tenant (Villa Boutanga), 4 Users, 4 RoomTypes, 10 Rooms, 50 Customers
- Enums : RoomStatus, BookingStatus
- Services : CheckOutService, LoyaltyService
- Table folio_items (suivi prestations sÃ©jour)

### Modules UI terminÃ©s
- Layout hotel + Sidebar avec sous-menu RÃ©servations (layouts/hotel.blade.php)
- Composants : sidebar-link, stat-card
- Login page animÃ©e aux couleurs Villa Boutanga
- Dashboard temps rÃ©el (arrivÃ©es, dÃ©parts, occupation, housekeeping)
- Chambres : liste, vue carte, types CRUD, statuts, historique
- Clients : liste avec filtres fidÃ©litÃ©, fiche dÃ©taillÃ©e, historique sÃ©jours, progression niveau
- RÃ©servations individuelles :
  - Wizard crÃ©ation 3 Ã©tapes (client â†’ dates â†’ chambre)
  - DÃ©tail avec folio complet
  - Check-in / Check-out automatisÃ©
  - Ajout/suppression prestations folio
  - Enregistrement paiements
  - GÃ©nÃ©ration facture + vue impression
  - Edit rÃ©servation
- RÃ©servations groupe :
  - CrÃ©ation dossier avec contact principal
  - Ajout/retrait chambres individuelles
  - Prestations de groupe (3 modes : par chambre, par personne, global)
  - Paiement global (2 modes : proportionnel, Ã©gal)
  - Check-in / Check-out groupÃ©
  - Facture groupe dÃ©taillÃ©e par chambre
  - Edit + Annulation dossier
- Housekeeping basique (changement statuts)
- Factures : vue individuelle + groupe, impression isolÃ©e

### SystÃ¨me RBAC (Role-Based Access Control) â€” COMPLET âœ…

#### Infrastructure RBAC
- **Table roles** : gestion des rÃ´les avec relation many-to-many vers users
- **Table role_user** : pivot table pour l'assignation des rÃ´les
- **Middleware EnsureRoleAccess** : validation des permissions par route
- **Blade directives** : @role, @admin, @hasnotRole pour conditionner l'UI
- **ModÃ¨le Role** : avec mÃ©thodes helper et relation belongsToMany users
- **ModÃ¨le User** : mÃ©thodes hasRole(), hasAnyRole(), isAdmin(), canAccessFinancialData(), etc.

#### RÃ´les dÃ©finis (10 rÃ´les)
- `admin` : Super administrateur (accÃ¨s total)
- `manager` : Directeur d'Ã©tablissement (gestion complÃ¨te)
- `reception` : RÃ©ceptionniste (rÃ©servations, clients, check-in/out)
- `accountant` : Comptable (accÃ¨s donnÃ©es financiÃ¨res)
- `cashier` : Caissier (paiements, factures)
- `housekeeping_leader` : Chef d'Ã©quipe housekeeping
- `housekeeping_staff` : Ã‰quipe housekeeping
- `restaurant_manager` : Manager restaurant
- `restaurant_staff` : Personnel restaurant
- `maintenance` : Ã‰quipe maintenance

#### Protection des routes
- Toutes les routes CRUD protÃ©gÃ©es par middleware `role:*`
- Routes financiÃ¨res : `accountant,manager,admin`
- Routes chambres : `manager,reception,housekeeping_leader,housekeeping_staff`
- Routes rÃ©servations : `reception,manager`
- Routes groupes : `reception,manager`
- Routes housekeeping : `housekeeping_leader,housekeeping_staff,manager`

#### Interface conditionnelle
- **Dashboard** : bouton admin (admin), carte housekeeping (housekeeping)
- **Dashboard** : la 4e carte repose sur `@role(...)` multi-rÃ´les et doit rester visible pour `housekeeping`, `manager` et `reception`
- **Chambres** : boutons CRUD (manager), formulaire statut (housekeeping + manager + reception)
- **RÃ©servations** : boutons crÃ©ation/actions (reception + manager)
- **Groupes** : boutons crÃ©ation/actions (reception + manager)
- **Tests** : 11/11 tests passent âœ…

#### SystÃ¨me de Popup d'accÃ¨s refusÃ© â€” IMPLÃ‰MENTÃ‰ âœ… (Session 2)
- **Composant Blade** : `resources/views/components/access-denied-popup.blade.php`
  - Popup modal avec design cohÃ©rent (rouge/warning, icÃ´ne, animation)
  - Buttons "Fermer" et "Retour au tableau de bord"
  - Focus trap pour accessibilitÃ©
- **IntÃ©gration layout** : Popup inclus dans `layouts/hotel.blade.php`
- **Middleware amÃ©liorÃ©** : `app/Http/Middleware/EnsureRoleAccess.php`
  - DÃ©tecte les requÃªtes AJAX (header `X-Requested-With: XMLHttpRequest`)
  - Retourne JSON `{"access_denied": true, "message": "..."}` au lieu de `abort(403)`
  - Messages personnalisÃ©s selon le rÃ´le requis
- **Interception JavaScript** : Script global dans le popup component
  - Intercepte les rÃ©ponses fetch (AJAX) avec statut 403
  - Affichage automatique du popup avec message d'erreur
- **Formulaires protÃ©gÃ©s** : Classe `expect-popup` sur les formulaires sensibles
  - Transforme les soumissions en requÃªtes AJAX
  - Affiche le popup au lieu de rediriger vers page d'erreur
  - Gestion des erreurs JSON avec logs de debug

#### Formulaires avec classe `expect-popup`
- `resources/views/rooms/show.blade.php` : formulaire changement statut
- `resources/views/rooms/index.blade.php` : crÃ©ation/modification/suppression chambre + types
- `resources/views/bookings/show.blade.php` : check-in, check-out, annulation

#### Routes de test
- `GET /test-popup` : Route protÃ©gÃ©e (admin requis) pour tester le popup
- Boutons de test sur le dashboard (bouton rouge + bouton orange)

#### Utilisateurs de test
- **Super Admin** : `admin@villaboutanga.cm` / `password`
- **Manager** : `manager@villaboutanga.cm` / `password`
- **RÃ©ceptionniste** : `reception@villaboutanga.cm` / `password`
- **Housekeeping-leader** : `housekeeping.leader@villaboutanga.cm` / `password`
- **Housekeeping** : `housekeeping@villaboutanga.cm` / `password`

## Conventions importantes
- Prix stockÃ©s en centimes (45000 FCFA = 4500000 en base)
- Affichage : ceil() pour Ã©viter les virgules sur les arrondis TVA
- TolÃ©rance 100 centimes sur validation paiement (arrondis TVA 19,25%)
- Couleurs via variables CSS : text-primary, text-secondary, text-accent, bg-surface-dark
- IcÃ´nes via Lucide : <i data-lucide="nom" class="w-4 h-4"></i>
- Recherche live : debounce 400ms sur oninput
- Modals : JS vanilla avec classList.remove('hidden')
- data-* attributes pour passer donnÃ©es aux modals (Ã©vite bugs apostrophes)
- Redirects : toujours redirect()->route() jamais back() sur les POST

## RÃ¨gles mÃ©tier clÃ©s
- Folio : ajout/suppression prestations uniquement en checked_in
- Check-out : solde dÃ» doit Ãªtre 0 (tolÃ©rance 100 centimes)
- Points fidÃ©litÃ© : 1pt/1000 FCFA, multiplicateurs par niveau
- Niveaux : bronze(0) silver(5M) gold(20M) platinum(50M) FCFA cumulÃ©s
- Chambre aprÃ¨s check-out â†’ statut 'cleaning' (pas 'available')
- Groupe : dates bloquÃ©es si chambres en checked_in
- Groupe : annulation impossible si status in_house ou completed
- Prestation groupe : label "(groupe GRP-XXXX)" ajoutÃ© Ã  la description

## Ce qu'il reste à  faire

### Priorité haute
- âœ… Middleware RBAC (limiter accÃ¨s selon le rÃ´le) â€” COMPLÃˆT
- âœ… SystÃ¨me de Popup d'accÃ¨s refusÃ© â€” COMPLÃˆT
- âœ… Module Utilisateurs (gestion staff par le manager) â€” COMPLET
- âŒ Housekeeping complet (liste prioritÃ©s, assignation staff)

### PrioritÃ© moyenne
- âŒ Module Restaurant (commandes, menu, lien folio)
- âŒ Rapports (taux occupation, revenus, fidÃ©litÃ©)
- âŒ PDF facture via DomPDF

### Technique
- âŒ Notifications email (confirmation rÃ©servation, rappel dÃ©part)
- âŒ BookingFactory + BookingSeeder
- âŒ Refactoring recalculateTotals en mÃ©thode unique
- âŒ Notes internes (internal_notes) dans les vues

## Modifications dÃ©taillÃ©es (Session 2)

### Fichiers CRÃ‰Ã‰S
1. **`resources/views/components/access-denied-popup.blade.php`**
   - Composant Blade pour le popup modal
   - JavaScript vanilla pour gestion affichage/masquage
   - Interception fetch + gestion formulaires expect-popup
   - Logs de debug pour faciliter le troubleshooting

2. **`popup-access-denied-plan.md`**
   - Plan dÃ©taillÃ© de l'implÃ©mentation du systÃ¨me de popup

### Fichiers MODIFIÃ‰S

1. **`app/Http/Middleware/EnsureRoleAccess.php`**
   - Ajout dÃ©tection requÃªtes AJAX (header `X-Requested-With`)
   - Ajout messages personnalisÃ©s par rÃ´le au lieu de messages gÃ©nÃ©riques
   - Retour JSON au lieu de abort(403) pour les requÃªtes AJAX
   - Structure : dÃ©tecte si requÃªte AJAX â†’ retourne JSON 403, sinon abort()

2. **`resources/views/layouts/hotel.blade.php`**
   - Ajout composant `<x-access-denied-popup />` aprÃ¨s la balise main
   - Positionnement du popup en tant que modal global

3. **`resources/views/rooms/index.blade.php`**
   - Ajout classe `expect-popup` Ã  7 formulaires :
     - CrÃ©ation chambre
     - Modification chambre
     - Suppression chambre (2 fois - carte + liste)
     - Suppression type
     - CrÃ©ation type
     - Modification type

4. **`resources/views/rooms/show.blade.php`**
   - Ajout classe `expect-popup` au formulaire changement statut
   - Ajout `'reception'` Ã  la directive `@role` (now includes reception pour changer statut)

5. **`resources/views/bookings/show.blade.php`**
   - Ajout classe `expect-popup` aux formulaires :
     - Check-in
     - Check-out
     - Annulation rÃ©servation

6. **`resources/views/dashboard.blade.php`**
   - Ajout 2 boutons de test pour le popup :
     - Bouton rouge : test manuel du popup JavaScript
     - Bouton orange : test AJAX avec route protÃ©gÃ©e
   - Ajout script `testPopup()` pour afficher le popup manuellement

7. **`routes/web.php`**
   - Ajout route test : `GET /test-popup` (middleware auth + role:admin)
   - Ajout middleware `role:manager,reception,housekeeping_leader,housekeeping_staff` Ã  la route `updateStatus` (changement statut chambre)
   - Nommage correct de la route test

8. **`context.md`** (ce fichier)
   - Mise Ã  jour sections RBAC et Interface conditionnelle
   - Ajout section "SystÃ¨me de Popup d'accÃ¨s refusÃ©"
   - Ajout section "Modifications dÃ©taillÃ©es"

### Changements de permissions

**Route `/rooms/{room}/status` (changement de statut chambre)**
- **Avant** : Aucun middleware spÃ©cifique (hÃ©ritÃ© du groupe rooms)
- **AprÃ¨s** : Middleware `role:manager,reception,housekeeping_leader,housekeeping_staff`

**Vue changement statut** (`resources/views/rooms/show.blade.php`)
- **Avant** : `@role('housekeeping_leader', 'housekeeping_staff', 'manager')`
- **AprÃ¨s** : `@role('housekeeping_leader', 'housekeeping_staff', 'manager', 'reception')`

### SystÃ¨me de debug intÃ©grÃ©
- Console logs dÃ©taillÃ©s dans le popup component
- Affichage logs lors de :
  - Chargement du script popup
  - Envoi de formulaires
  - RÃ©ception de rÃ©ponses
  - Parsing JSON
  - Affichage popup
- Messages : "Form submitted", "AJAX response received", "Parsed JSON data", etc.

## Structure des fichiers clÃ©s
- app/Models/           â†’ tous les modÃ¨les
- app/Services/         â†’ CheckOutService, LoyaltyService
- app/Enums/            â†’ RoomStatus, BookingStatus
- app/Http/Controllers/ â†’ BookingController, GroupBookingController,
                          RoomController, CustomerController,
                          HousekeepingController, InvoiceController,
                          UserManagementController
- app/Http/Middleware/  â†’ EnsureRoleAccess.php, AdminOnly.php
- resources/views/layouts/hotel.blade.php  â†’ layout principal (inclut popup)
- resources/views/components/              â†’ sidebar-link, stat-card, access-denied-popup
- resources/views/bookings/                â†’ index, create, select-room, show, edit (formulaires expect-popup)
- resources/views/groups/                  â†’ index, create, show, edit, invoice
- resources/views/customers/               â†’ index, show
- resources/views/rooms/                   â†’ index (formulaires expect-popup), show (formulaires expect-popup)
- resources/views/users/                   â†’ index (vue liste + vue cartes, modales create/edit)
- resources/views/invoices/                â†’ show

## Correctif RBAC UI (Session 3)

### ProblÃ¨me rencontrÃ©
- AprÃ¨s lâ€™implÃ©mentation du RBAC, certaines cartes Blade conditionnelles nâ€™Ã©taient plus affichÃ©es.
- SymptÃ´mes observÃ©s :
  - la 4e carte du dashboard disparaissait pour `manager` et `reception`
  - la carte "Changer le statut" sur le dÃ©tail dâ€™une chambre disparaissait aussi
- La route de changement de statut Ã©tait pourtant bien autorisÃ©e pour `manager,reception,housekeeping_leader,housekeeping_staff`.

### Cause racine
- La directive Blade personnalisÃ©e `@role(...)` avait Ã©tÃ© implÃ©mentÃ©e avec `hasRole(...)`.
- Or les vues utilisent souvent `@role('role1', 'role2', 'role3')`.
- Avec cette implÃ©mentation, la directive ne gÃ©rait pas correctement les vÃ©rifications multi-rÃ´les, ce qui masquait des blocs UI autorisÃ©s.

### Correctif appliquÃ©
1. **`app/Providers/AppServiceProvider.php`**
   - Correction de la directive `@role(...)` pour utiliser `hasAnyRole([...])`
   - Correction de `@hasnotRole(...)` pour rester cohÃ©rente avec les vÃ©rifications multi-rÃ´les

2. **`resources/views/dashboard.blade.php`**
   - Ouverture explicite de la 4e carte aux rÃ´les `housekeeping_leader`, `housekeeping_staff`, `manager` et `reception`
   - Objectif : conserver 4 cartes visibles dans la premiÃ¨re section pour les profils mÃ©tier concernÃ©s

### Impact attendu
- Le `manager` et le `receptionniste` revoient la 4e carte du dashboard
- Le `receptionniste` revoit la carte de changement de statut dans `rooms/show`
- Toutes les vues utilisant `@role(...)` avec plusieurs rÃ´les bÃ©nÃ©ficient dÃ©sormais du bon comportement

### Validation
- VÃ©rification du code effectuÃ©e sur les directives Blade et les vues impactÃ©es
- ExÃ©cution des commandes Laravel non rÃ©alisÃ©e dans ce terminal car `php` nâ€™est pas disponible dans le `PATH`
- Si un cache Blade persiste localement, exÃ©cuter `php artisan view:clear`

## Tests et validation
- **PHPUnit Tests** : `tests/Feature/AuthorizationTest.php`
  - 11/11 tests passent âœ…
  - Couvre tous les rÃ´les et permissions
- **Popup Fonctionnel** : TestÃ© manuellement
  - Bouton test sur dashboard âœ…
  - Formulaires avec classe expect-popup âœ…
  - Interception AJAX âœ…
  - Affichage popup âœ…
- **Debug Console** : Logs dÃ©taillÃ©s disponibles (F12)

## Module Utilisateurs (Session 4) â€” COMPLET âœ…

### FonctionnalitÃ©s livrÃ©es
- Espace manager `users.index` pour gÃ©rer le staff du tenant (hors `admin` et `manager`)
- CrÃ©ation de compte staff avec rÃ´le mÃ©tier, mot de passe, tÃ©lÃ©phone, statut actif/inactif
- Modification de profil staff (dont reset mot de passe optionnel)
- Activation/dÃ©sactivation de compte via bouton d'action
- Filtres: recherche live, rÃ´le, statut
- Double affichage: vue liste + vue cartes (toggle par icÃ´nes Lucide)
- Messages de succÃ¨s et d'erreurs visibles aprÃ¨s soumission des formulaires

### SÃ©curitÃ© / rÃ¨gles mÃ©tier
- Routes `users.*` protÃ©gÃ©es par `middleware('role:manager')`
- Isolation tenant stricte: un manager ne peut agir que sur les users de son tenant
- Interdiction de gÃ©rer les profils `admin` et `manager`
- Synchronisation des rÃ´les: colonne `users.role` + pivot `role_user`
- Blocage login des comptes inactifs:
  - si `is_active = false`, connexion refusÃ©e
  - message: "Votre compte a ete desactive. Veuillez contacter votre manager."

### Ajustements UX intÃ©grÃ©s
- Sidebar: gestion des noms longs (ellipsis `...`) sans casser le layout
- Nom utilisateur tronquÃ© avec `Str::limit(..., 13, '...')`
- Bouton de dÃ©connexion verrouillÃ© en largeur fixe pour Ã©viter les dÃ©calages
- Conservation du mode d'affichage (`view=list|cards`) aprÃ¨s create/update/toggle

### Fichiers crÃ©Ã©s/modifiÃ©s (module users)
- `app/Http/Controllers/UserManagementController.php` (nouveau)
- `resources/views/users/index.blade.php` (nouveau)
- `routes/web.php` (routes users manager)
- `resources/views/layouts/hotel.blade.php` (sidebar + lien module users)
- `app/Http/Requests/Auth/LoginRequest.php` (blocage compte inactif)

