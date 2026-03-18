<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// ✅ AJOUTER CECI : La route manquante pour le Dashboard
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Route de test pour le Design System
Route::get('/test-design', function () {
    return view('test-design');
});

