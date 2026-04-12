<?php

use App\Http\Controllers\CollaboratorController;
use App\Http\Controllers\PayrollCycleController;
use App\Http\Controllers\PayrollEntryController;
use App\Http\Controllers\PjInvoiceController;
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

    Route::put('/payroll-entries/{payrollEntry}', [PayrollEntryController::class, 'update'])
        ->name('payroll-entries.update')
        ->middleware('can:admin');

    Route::get('/payroll-cycles/{payrollCycle}/commissions', [PayrollCycleController::class, 'commissions'])
        ->name('payroll-cycles.commissions')
        ->middleware('can:admin');

    // Admin PJ invoice management
    Route::get('payroll-cycles/{payrollCycle}/pj-invoices', [PjInvoiceController::class, 'index'])
        ->name('pj-invoices.index')
        ->middleware('can:admin');
    Route::put('pj-invoices/{pjInvoice}', [PjInvoiceController::class, 'update'])
        ->name('pj-invoices.update')
        ->middleware('can:admin');
    Route::get('pj-invoices/{pjInvoice}/signed-url', [PjInvoiceController::class, 'show'])
        ->name('pj-invoices.show')
        ->middleware('can:admin');

    // Collaborator self-service
    Route::get('self-service/profile', [SelfServiceController::class, 'profile'])
        ->name('self-service.profile');
    Route::get('self-service/invoices', [PjInvoiceController::class, 'selfServiceIndex'])
        ->name('self-service.invoices');
    Route::post('self-service/invoices', [PjInvoiceController::class, 'store'])
        ->name('self-service.invoices.store');
});

// Signed download route (auth required, signed URL handles authorization)
Route::get('pj-invoices/{pjInvoice}/download', [PjInvoiceController::class, 'download'])
    ->name('pj-invoices.download')
    ->middleware('auth');

require __DIR__.'/settings.php';
