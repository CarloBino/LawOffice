<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\StaffUserController;
use App\Http\Controllers\CaseController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\LawyerController;
use App\Http\Controllers\HearingController;
use App\Http\Controllers\CaseActionController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\OpposingPartyController;
use App\Http\Controllers\OfficeExpenseController;
use App\Http\Middleware\EnsureUserIsActive;
use App\Models\Billing;
use App\Models\BillingPayment;
use App\Models\Client;
use App\Models\Document;
use App\Models\Hearing;
use App\Models\Lawyer;
use App\Models\LegalCase;
use App\Models\OfficeExpense;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $publicStats = [
        'clients' => Client::count(),
        'activeCases' => LegalCase::whereNotIn('case_status', ['Closed', 'Archived'])->count(),
        'documents' => Document::count(),
    ];

    return view('welcome', compact('publicStats'));
});

Route::get('/dashboard', function () {
    $user = Auth::user();
    $isLawyer = ($user?->role ?: 'staff') === 'lawyer';
    $isAdmin = $user?->role === 'admin';
    $currentLawyerId = $isLawyer
        ? Lawyer::query()
            ->where('user_id', $user->id)
            ->orWhere('email', $user->email)
            ->orWhere('full_name', $user->name)
            ->value('id')
        : null;
    $caseScope = function ($query) use ($isLawyer, $currentLawyerId) {
        if (! $isLawyer) {
            return $query;
        }

        return $currentLawyerId
            ? $query->where('assigned_lawyer_id', $currentLawyerId)
            : $query->whereRaw('1 = 0');
    };

    $stats = [
        'clients' => Client::when($isLawyer, fn ($query) => $query->whereHas('cases', $caseScope))->count(),
        'openCases' => LegalCase::when($isLawyer, $caseScope)->whereNotIn('case_status', ['Closed', 'Archived'])->count(),
        'upcomingHearings' => Hearing::when($isLawyer, fn ($query) => $query->whereHas('case', $caseScope))->whereDate('hearing_date', '>=', now()->toDateString())->count(),
        'unpaidBalance' => Billing::when($isLawyer, fn ($query) => $query->whereHas('case', $caseScope))->sum('balance'),
        'officeExpensesUnpaid' => $isAdmin
            ? OfficeExpense::where('payment_status', 'Unpaid')->sum('amount')
            : 0,
        'documents' => Document::when($isLawyer, fn ($query) => $query->whereHas('case', $caseScope))->count(),
    ];

    $upcomingHearings = Hearing::with('case.client')
        ->when($isLawyer, fn ($query) => $query->whereHas('case', $caseScope))
        ->whereDate('hearing_date', '>=', now()->toDateString())
        ->orderBy('hearing_date')
        ->orderBy('hearing_time')
        ->take(5)
        ->get();

    $priorityCases = LegalCase::with(['client', 'assignedLawyer'])
        ->when($isLawyer, $caseScope)
        ->whereNotIn('case_status', ['Closed', 'Archived'])
        ->orderByRaw("CASE priority_level WHEN 'High' THEN 1 WHEN 'Medium' THEN 2 ELSE 3 END")
        ->latest()
        ->take(5)
        ->get();

    $recentPayments = BillingPayment::with('billing.case.client')
        ->when($isLawyer, fn ($query) => $query->whereHas('billing.case', $caseScope))
        ->latest('date_received')
        ->latest('id')
        ->take(5)
        ->get();

    $unpaidOfficeExpenses = $isAdmin
        ? OfficeExpense::where('payment_status', 'Unpaid')
            ->orderByRaw('due_date IS NULL')
            ->orderBy('due_date')
            ->take(5)
            ->get()
        : collect();

    return view('dashboard', compact('stats', 'upcomingHearings', 'priorityCases', 'recentPayments', 'unpaidOfficeExpenses', 'isAdmin'));
})->middleware(['auth', EnsureUserIsActive::class, 'verified'])->name('dashboard');

Route::middleware(['auth', EnsureUserIsActive::class])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/search', [SearchController::class, 'index'])->name('search.index');
    Route::get('/clients/{client}/statement', [ClientController::class, 'printStatement'])->name('clients.statement');
    Route::get('/cases/{case}/print', [CaseController::class, 'printSummary'])->name('cases.print');
    Route::get('/billings-export', [BillingController::class, 'export'])->name('billings.export');
    Route::get('/office-expenses-export', [OfficeExpenseController::class, 'export'])->name('office-expenses.export');

    // Resource routes for main modules
    Route::resource('cases', CaseController::class);
    Route::resource('clients', ClientController::class);
    Route::resource('lawyers', LawyerController::class);
    Route::resource('hearings', HearingController::class);
    Route::resource('case-actions', CaseActionController::class);
    Route::patch('billings/{billing}/toggle-paid', [BillingController::class, 'togglePaid'])->name('billings.toggle-paid');
    Route::post('billings/{billing}/payments', [BillingController::class, 'storePayment'])->name('billings.payments.store');
    Route::delete('billings/{billing}/payments/{payment}', [BillingController::class, 'destroyPayment'])->name('billings.payments.destroy');
    Route::resource('billings', BillingController::class);
    Route::patch('office-expenses/{officeExpense}/toggle-paid', [OfficeExpenseController::class, 'togglePaid'])->name('office-expenses.toggle-paid');
    Route::resource('office-expenses', OfficeExpenseController::class);
    Route::get('documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
    Route::resource('documents', DocumentController::class);
    Route::resource('opposing-parties', OpposingPartyController::class);
    Route::get('activity-log', [ActivityLogController::class, 'index'])->name('activity-logs.index');
    Route::get('staff-users', [StaffUserController::class, 'index'])->name('staff-users.index');
    Route::get('staff-users/create', [StaffUserController::class, 'create'])->name('staff-users.create');
    Route::post('staff-users', [StaffUserController::class, 'store'])->name('staff-users.store');
    Route::get('staff-users/{staffUser}/edit', [StaffUserController::class, 'edit'])->name('staff-users.edit');
    Route::patch('staff-users/{staffUser}', [StaffUserController::class, 'update'])->name('staff-users.update');
    Route::delete('staff-users/{staffUser}', [StaffUserController::class, 'destroy'])->name('staff-users.destroy');
});

require __DIR__.'/auth.php';
