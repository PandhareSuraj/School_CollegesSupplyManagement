<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Volt::route('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');

    Volt::route('requests', 'requests.index')->name('requests.index');
    Volt::route('requests/create', 'requests.create')->name('requests.create');
    Volt::route('requests/{request}', 'requests.show')->name('requests.show');

    Volt::route('orders', 'orders.index')->name('orders.index');
    Volt::route('orders/{order}', 'orders.show')->name('orders.show');

    Volt::route('reports', 'reports.index')->name('reports.index')->middleware('role:admin|trust_head|principal');
});

require __DIR__.'/auth.php';
