<?php

use App\Http\Controllers\AdmissionChecklistController;
use App\Http\Controllers\AdmissionChecklistItemController;
use App\Http\Controllers\AssistiveConventionController;
use App\Http\Controllers\CollaboratorController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DissidioController;
use App\Http\Controllers\DpAssistantController;
use App\Http\Controllers\PayrollCycleController;
use App\Http\Controllers\PayrollDiscrepancyController;
use App\Http\Controllers\PayrollEntryController;
use App\Http\Controllers\PjInvoiceController;
use App\Http\Controllers\PlrController;
use App\Http\Controllers\SelfServiceController;
use App\Http\Controllers\TerminationController;
use App\Http\Controllers\ThirteenthSalaryController;
use App\Http\Controllers\VacationBatchController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

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

    // Admin vacation batches
    Route::resource('vacation-batches', VacationBatchController::class)
        ->only(['index', 'store', 'show', 'update'])
        ->middleware('can:admin');

    // Admission checklists
    Route::get('/admission-checklists/{admissionChecklist}', [AdmissionChecklistController::class, 'show'])
        ->name('admission-checklists.show')
        ->middleware('can:admin');
    Route::put('/admission-checklist-items/{admissionChecklistItem}', [AdmissionChecklistItemController::class, 'update'])
        ->name('admission-checklist-items.update')
        ->middleware('can:admin');

    // Termination workflow
    Route::get('/collaborators/{collaborator}/termination/create', [TerminationController::class, 'create'])
        ->name('terminations.create')
        ->middleware('can:admin');
    Route::post('/collaborators/{collaborator}/termination', [TerminationController::class, 'store'])
        ->name('terminations.store')
        ->middleware('can:admin');
    Route::get('/termination-records/{terminationRecord}', [TerminationController::class, 'show'])
        ->name('termination-records.show')
        ->middleware('can:admin');
    Route::put('/termination-records/{terminationRecord}', [TerminationController::class, 'update'])
        ->name('termination-records.update')
        ->middleware('can:admin');

    // Dissídio workflow
    Route::get('/dissidio-rounds', [DissidioController::class, 'index'])->name('dissidio-rounds.index')->middleware('can:admin');
    Route::get('/dissidio-rounds/create', [DissidioController::class, 'create'])->name('dissidio-rounds.create')->middleware('can:admin');
    Route::post('/dissidio-rounds', [DissidioController::class, 'store'])->name('dissidio-rounds.store')->middleware('can:admin');
    Route::get('/dissidio-rounds/{dissidioRound}', [DissidioController::class, 'show'])->name('dissidio-rounds.show')->middleware('can:admin');
    Route::post('/dissidio-rounds/{dissidioRound}/simulate', [DissidioController::class, 'simulate'])->name('dissidio-rounds.simulate')->middleware('can:admin');
    Route::post('/dissidio-rounds/{dissidioRound}/apply', [DissidioController::class, 'apply'])->name('dissidio-rounds.apply')->middleware('can:admin');

    // 13th Salary
    Route::get('/thirteenth-salary', [ThirteenthSalaryController::class, 'index'])->name('thirteenth-salary.index')->middleware('can:admin');
    Route::get('/thirteenth-salary/create', [ThirteenthSalaryController::class, 'create'])->name('thirteenth-salary.create')->middleware('can:admin');
    Route::post('/thirteenth-salary', [ThirteenthSalaryController::class, 'store'])->name('thirteenth-salary.store')->middleware('can:admin');
    Route::get('/thirteenth-salary/{thirteenthSalaryRound}', [ThirteenthSalaryController::class, 'show'])->name('thirteenth-salary.show')->middleware('can:admin');
    Route::post('/thirteenth-salary/{thirteenthSalaryRound}/simulate', [ThirteenthSalaryController::class, 'simulate'])->name('thirteenth-salary.simulate')->middleware('can:admin');

    // PLR
    Route::get('/plr', [PlrController::class, 'index'])->name('plr.index')->middleware('can:admin');
    Route::get('/plr/create', [PlrController::class, 'create'])->name('plr.create')->middleware('can:admin');
    Route::post('/plr', [PlrController::class, 'store'])->name('plr.store')->middleware('can:admin');
    Route::get('/plr/{plrRound}', [PlrController::class, 'show'])->name('plr.show')->middleware('can:admin');
    Route::post('/plr/{plrRound}/simulate', [PlrController::class, 'simulate'])->name('plr.simulate')->middleware('can:admin');

    // Union Obligations
    Route::get('/union/opposition', [AssistiveConventionController::class, 'index'])->name('union.opposition.index')->middleware('can:admin');
    Route::post('/union/opposition', [AssistiveConventionController::class, 'store'])->name('union.opposition.store')->middleware('can:admin');

    // DP Assistant — dedicated module + conversation management
    Route::get('/dp-assistant', [DpAssistantController::class, 'index'])->name('dp-assistant.index');
    Route::post('/dp-assistant/ask', [DpAssistantController::class, 'ask'])->name('dp-assistant.ask');
    Route::post('/dp-assistant/stream', [DpAssistantController::class, 'streamResponse'])->name('dp-assistant.stream');
    Route::get('/dp-assistant/conversations', [DpAssistantController::class, 'conversations'])->name('dp-assistant.conversations');
    Route::get('/dp-assistant/conversations/{id}', [DpAssistantController::class, 'loadConversation'])->name('dp-assistant.conversations.show');
    Route::delete('/dp-assistant/conversations/{id}', [DpAssistantController::class, 'destroyConversation'])->name('dp-assistant.conversations.destroy');
    Route::patch('/dp-assistant/conversations/{id}/favorite', [DpAssistantController::class, 'toggleFavorite'])->name('dp-assistant.conversations.favorite');
    Route::patch('/dp-assistant/conversations/{id}/title', [DpAssistantController::class, 'renameConversation'])->name('dp-assistant.conversations.rename');

    // Payroll Discrepancy Analysis (AJAX)
    Route::post('/payroll-cycles/{payrollCycle}/discrepancy-analysis', [PayrollDiscrepancyController::class, 'analyze'])->name('payroll-cycles.discrepancy-analysis')->middleware('can:admin');

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
