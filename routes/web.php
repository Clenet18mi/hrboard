<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::livewire('departments', 'pages::departments.index')->name('departments.index');
    Route::livewire('employees', 'pages::employees.index')->name('employees.index');
    Route::livewire('employees/create', 'pages::employees.upsert')->name('employees.create');
    Route::livewire('employees/{employee}/edit', 'pages::employees.upsert')->name('employees.edit');
    Route::livewire('leaves', 'pages::leaves.index')->name('leaves.index');
});

require __DIR__.'/settings.php';
