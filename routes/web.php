<?php

use App\Http\Controllers\CollaboratorController;
use App\Http\Controllers\PayrollCycleController;
use App\Http\Controllers\PayrollEntryController;
use App\Http\Controllers\SelfServiceController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    // Admin-only collaborator CRUD
    Route::resource('collaborators', CollaboratorController::class)
        ->except(['destroy'])
        ->middleware('can:admin');

    // Admin-only payroll
    Route::resource('payroll-cycles', PayrollCycleController::class)
        ->only(['index', 'store', 'show', 'update'])
        ->middleware('can:admin');

    Route::put('payroll-cycles/{payrollCycle}/entries/{payrollEntry}', [PayrollEntryController::class, 'update'])
        ->name('payroll-cycles.entries.update')
        ->middleware('can:admin');

    // Collaborator self-service
    Route::get('self-service/profile', [SelfServiceController::class, 'profile'])
        ->name('self-service.profile');
});

require __DIR__.'/settings.php';
