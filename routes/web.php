<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\HousekeepingController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\GroupBookingController;

// ===== AUTH ROUTES (Breeze) =====
use App\Http\Controllers\Auth\AuthenticatedSessionController;
// Login
Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
Route::post('/login', [AuthenticatedSessionController::class, 'store']);

// Logout
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

// Page d'accueil → redirige vers le dashboard si connecté
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Toutes les routes de l'app nécessitent d'être connecté et vérifié
Route::middleware(['auth', 'verified'])->group(function () {

    // --- DASHBOARD ---
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // --- CHAMBRES ---
    Route::prefix('rooms')->name('rooms.')->middleware('role:manager,reception,housekeeping_leader,housekeeping')->group(function () {
        Route::get('/',                [RoomController::class, 'index'])->name('index');
        Route::post('/',               [RoomController::class, 'store'])->middleware('role:manager')->name('store');
        Route::get('/{room}',          [RoomController::class, 'show'])->name('show');
        Route::put('/{room}',          [RoomController::class, 'update'])->middleware('role:manager')->name('update');
        Route::delete('/{room}',       [RoomController::class, 'destroy'])->middleware('role:manager')->name('destroy');
        Route::post('/{room}/status',  [RoomController::class, 'updateStatus'])->middleware('role:manager,reception,housekeeping_leader,housekeeping_staff,housekeeping')->name('updateStatus');

        // Types de chambres - seulement manager
        Route::post('/types/store',         [RoomController::class, 'storeType'])->middleware('role:manager')->name('types.store');
        Route::put('/types/{roomType}',     [RoomController::class, 'updateType'])->middleware('role:manager')->name('types.update');
        Route::delete('/types/{roomType}',  [RoomController::class, 'destroyType'])->middleware('role:manager')->name('types.destroy');
    });

    // --- RÉSERVATIONS ---
    Route::prefix('bookings')->name('bookings.')->middleware('role:manager,reception')->group(function () {
        Route::get('/',                        [BookingController::class, 'index'])->name('index');
        Route::get('/create',                  [BookingController::class, 'create'])->name('create');
        Route::post('/',                       [BookingController::class, 'store'])->name('store');
        Route::get('/{booking}',               [BookingController::class, 'show'])->name('show');
        Route::get('/{booking}/edit',          [BookingController::class, 'edit'])->name('edit');
        Route::put('/{booking}',               [BookingController::class, 'update'])->name('update');
        Route::post('/{booking}/checkin',      [BookingController::class, 'checkIn'])->name('checkIn');
        Route::post('/{booking}/checkout',     [BookingController::class, 'checkOut'])->name('checkOut');
        Route::post('/{booking}/cancel',       [BookingController::class, 'cancel'])->name('cancel');
        Route::post('/{booking}/folio',        [BookingController::class, 'addFolioItem'])->middleware('role:reception,manager,restaurant_chief,cashier')->name('folio.add');
        Route::delete('/{booking}/folio/{folioItem}', [BookingController::class, 'removeFolioItem'])->middleware('role:reception,manager,restaurant_chief,cashier')->name('folio.remove');
        Route::post('/{booking}/payment', [BookingController::class, 'addPayment'])->middleware('role:reception,manager,cashier')->name('payment.add');
    });

    Route::prefix('groups')->name('groups.')->middleware('role:manager,reception')->group(function () {
        Route::get('/',                          [GroupBookingController::class, 'index'])->name('index');
        Route::get('/create',                    [GroupBookingController::class, 'create'])->middleware('role:manager')->name('create');
        Route::post('/',                         [GroupBookingController::class, 'store'])->middleware('role:manager')->name('store');
        Route::get('/{groupBooking}',            [GroupBookingController::class, 'show'])->name('show');
        Route::post('/{groupBooking}/room',      [GroupBookingController::class, 'addRoom'])->middleware('role:manager')->name('addRoom');
        Route::delete('/{groupBooking}/room/{booking}', [GroupBookingController::class, 'removeRoom'])->middleware('role:manager')->name('removeRoom');
        Route::post('/{groupBooking}/checkin',   [GroupBookingController::class, 'checkInAll'])->name('checkInAll');
        Route::post('/{groupBooking}/checkout',  [GroupBookingController::class, 'checkOutAll'])->name('checkOutAll');
        Route::post('/{groupBooking}/folio', [GroupBookingController::class, 'addGroupFolioItem'])->name('folio.add');
        Route::post('/{groupBooking}/payment', [GroupBookingController::class, 'addGroupPayment'])->middleware('role:manager,reception,cashier')->name('payment.add');
        Route::get('/{groupBooking}/invoice', [GroupBookingController::class, 'invoice'])->middleware('role:cashier,manager,reception')->name('invoice');
        Route::get('/{groupBooking}/edit',   [GroupBookingController::class, 'edit'])->middleware('role:manager')->name('edit');
        Route::put('/{groupBooking}',        [GroupBookingController::class, 'update'])->middleware('role:manager')->name('update');
        Route::post('/{groupBooking}/cancel', [GroupBookingController::class, 'cancel'])->middleware('role:manager')->name('cancel');
    });

    // --- CLIENTS ---
    Route::prefix('customers')->name('customers.')->middleware('role:manager,reception,cashier')->group(function () {
        Route::get('/',               [CustomerController::class, 'index'])->name('index');
        Route::get('/create',         [CustomerController::class, 'create'])->middleware('role:reception,manager')->name('create');
        Route::post('/',              [CustomerController::class, 'store'])->middleware('role:reception,manager')->name('store');
        Route::get('/{customer}',     [CustomerController::class, 'show'])->name('show');
        Route::get('/{customer}/edit', [CustomerController::class, 'edit'])->middleware('role:reception,manager')->name('edit');
        Route::put('/{customer}',     [CustomerController::class, 'update'])->middleware('role:reception,manager')->name('update');
    });

    // --- HOUSEKEEPING ---
    Route::prefix('housekeeping')->name('housekeeping.')->middleware('role:housekeeping_leader,housekeeping_staff,housekeeping,manager')->group(function () {
        Route::get('/',                    [HousekeepingController::class, 'index'])->name('index');
        Route::post('/teams',              [HousekeepingController::class, 'storeTeam'])->middleware('role:housekeeping_leader,manager')->name('teams.store');
        Route::post('/assignments',        [HousekeepingController::class, 'assignRooms'])->middleware('role:housekeeping_leader,manager')->name('assignments.store');
        Route::post('/{room}/clean',       [HousekeepingController::class, 'markCleaning'])->name('clean');
        Route::post('/{room}/ready',       [HousekeepingController::class, 'markReady'])->name('ready');
        Route::post('/{room}/issue',       [HousekeepingController::class, 'reportIssue'])->name('issue');
    });

    Route::prefix('invoices')->name('invoices.')->middleware('role:manager,reception,cashier')->group(function () {
        Route::get('/{invoice}', [InvoiceController::class, 'show'])->name('show');
    });

    // --- COMPTABILITÉ (futur module) ---
    // Routes préparées pour le développement futur du module comptable
    Route::prefix('accounting')->name('accounting.')->middleware('role:accountant,manager,admin')->group(function () {
        // Ces routes seront implémentées quand le module comptable sera développé
        // Route::get('/dashboard', [AccountingController::class, 'dashboard'])->name('dashboard');
        // Route::get('/reports', [ReportsController::class, 'index'])->name('reports');
        // Route::get('/budgets', [BudgetsController::class, 'index'])->name('budgets');
        // etc.
    });
});

// ===== ROUTE DE TEST POUR POPUP =====
Route::middleware(['auth'])->group(function () {
    Route::get('/test-popup', function () {
        return response()->json(['access_denied' => true, 'message' => 'Ceci est un test du popup d\'accès refusé']);
    })->middleware('role:admin')->name('test-popup');
});

// Routes Breeze (login, register, etc.) — déjà générées, ne pas toucher
//require __DIR__.'/auth.php';
