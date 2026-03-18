<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\HousekeepingController;

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
    Route::prefix('rooms')->name('rooms.')->group(function () {
        Route::get('/',              [RoomController::class, 'index'])->name('index');
        Route::get('/{room}',        [RoomController::class, 'show'])->name('show');
        Route::post('/{room}/status',[RoomController::class, 'updateStatus'])->name('updateStatus');
    });

    // --- RÉSERVATIONS ---
    Route::prefix('bookings')->name('bookings.')->group(function () {
        Route::get('/',                        [BookingController::class, 'index'])->name('index');
        Route::get('/create',                  [BookingController::class, 'create'])->name('create');
        Route::post('/',                       [BookingController::class, 'store'])->name('store');
        Route::get('/{booking}',               [BookingController::class, 'show'])->name('show');
        Route::get('/{booking}/edit',          [BookingController::class, 'edit'])->name('edit');
        Route::put('/{booking}',               [BookingController::class, 'update'])->name('update');
        Route::post('/{booking}/checkin',      [BookingController::class, 'checkIn'])->name('checkIn');
        Route::post('/{booking}/checkout',     [BookingController::class, 'checkOut'])->name('checkOut');
        Route::post('/{booking}/cancel',       [BookingController::class, 'cancel'])->name('cancel');
    });

    // --- CLIENTS ---
    Route::prefix('customers')->name('customers.')->group(function () {
        Route::get('/',               [CustomerController::class, 'index'])->name('index');
        Route::get('/create',         [CustomerController::class, 'create'])->name('create');
        Route::post('/',              [CustomerController::class, 'store'])->name('store');
        Route::get('/{customer}',     [CustomerController::class, 'show'])->name('show');
        Route::get('/{customer}/edit',[CustomerController::class, 'edit'])->name('edit');
        Route::put('/{customer}',     [CustomerController::class, 'update'])->name('update');
    });

    // --- HOUSEKEEPING ---
    Route::prefix('housekeeping')->name('housekeeping.')->group(function () {
        Route::get('/',                    [HousekeepingController::class, 'index'])->name('index');
        Route::post('/{room}/clean',       [HousekeepingController::class, 'markCleaning'])->name('clean');
        Route::post('/{room}/ready',       [HousekeepingController::class, 'markReady'])->name('ready');
    });
});

// Routes Breeze (login, register, etc.) — déjà générées, ne pas toucher
//require __DIR__.'/auth.php';