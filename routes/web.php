<?php

use App\Http\Controllers\PdfController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::livewire('leaves', 'pages::leaves.index')->name('leaves.index');
    Route::livewire('notifications', 'pages::notifications.index')->name('notifications.index');
    Route::get('leaves/{leave}/pdf', [PdfController::class, 'leaveBulletin'])->name('leaves.pdf');

    // Management Routes (RH & Super Admin only)
    Route::middleware(['role:hr|super_admin'])->group(function () {
        Route::livewire('departments', 'pages::departments.index')->name('departments.index');
        Route::livewire('employees', 'pages::employees.index')->name('employees.index');
        Route::livewire('employees/create', 'pages::employees.upsert')->name('employees.create');
        Route::livewire('employees/{employee}', 'pages::employees.show')->name('employees.show');
        Route::livewire('employees/{employee}/edit', 'pages::employees.upsert')->name('employees.edit');
        Route::livewire('leaves/calendar', 'pages::leaves.calendar')->name('leaves.calendar');
        
        // Reporting & Admin PDF Routes
        Route::get('employees/{employee}/pdf', [PdfController::class, 'employeeSheet'])->name('employees.pdf');
        Route::get('employees/{employee}/attestation', [PdfController::class, 'attestation'])->name('employees.attestation');
        Route::get('reports/department/{department}/pdf', [PdfController::class, 'departmentList'])->name('reports.department.pdf');
        Route::get('reports/monthly-leaves/pdf', [PdfController::class, 'monthlyLeavesReport'])->name('reports.monthly-leaves.pdf');
    });
});

require __DIR__.'/settings.php';
