<?php 

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\BudgetRequestController;
use App\Http\Controllers\AllocationController;
use App\Http\Controllers\ChartOfAccountsController;
use App\Http\Controllers\JournalEntryController;
use App\Http\Controllers\AccountsController;
use App\Http\Controllers\DisbursementController;

//Employee Auth
use App\Http\Controllers\EmployeeAuthController;
use App\Http\Controllers\EmployeeBudgetController;

// Authentication routes
use Illuminate\Support\Facades\Auth;


// Custom register view and POST handler
Route::get('/register', function () {
    return view('auth-register');
})->name('register');
Route::post('/register', [App\Http\Controllers\Auth\RegisterController::class, 'register']);

// Search route
Route::get('/search', [App\Http\Controllers\DashboardController::class, 'search'])->name('search');
// Disable default register route
Auth::routes(['register' => false]);

// Custom login view
Route::get('/login', function () {
    return view('auth-login');
})->name('login');

// Redirect root to login if not authenticated, else to dashboard
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

// Protect dashboard and profile routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::get('/profile', function () {
        return view('profile');
    })->name('profile');

    Route::get('/profile-settings', function () {
        return view('profile-settings');
    })->name('profile-settings');

    Route::get('/profile-security', function () {
        return view('profile-security');
    })->name('profile-security');

    Route::get('/profile-notification', function () {
        return view('profile-notification');
    })->name('profile-notification');

    Route::get('/profile-posts', function () {
        return view('profile-posts');
    })->name('profile-posts');

    // File Manager
    Route::get('/files-list', function () {
        return view('files-list');
    })->name('files-list');

    Route::get('/files-grid', function () {
        return view('files-grid');
    })->name('files-grid');

    Route::prefix('finance')->group(function () {
        Route::resource('collections', CollectionController::class);
        Route::resource('budget_requests', BudgetRequestController::class);

        Route::get('allocations', [AllocationController::class, 'index'])->name('finance.allocations.index');
        Route::post('allocations', [AllocationController::class, 'store'])->name('finance.allocations.store');
        Route::put('allocations/{allocation}', [AllocationController::class, 'update'])->name('finance.allocations.update');
        Route::delete('allocations/{allocation}', [AllocationController::class, 'destroy'])->name('finance.allocations.destroy');

        // optional route to update used via small POST/PUT from modal
        Route::put('allocations/{allocation}/used', [AllocationController::class, 'updateUsed'])->name('finance.allocations.updateUsed');

        Route::put('/budget_requests/{id}/approve', [BudgetRequestController::class, 'approve'])->name('budget_requests.approve');

        Route::get('/chart-of-accounts', [ChartOfAccountsController::class, 'index'])->name('chart.index');
        Route::get('/chart-of-accounts/{id}', [ChartOfAccountsController::class, 'show'])->name('chart.show');
        Route::post('/chart-of-accounts', [ChartOfAccountsController::class, 'store'])->name('chart.store');
        Route::put('/chart-of-accounts/{id}', [ChartOfAccountsController::class, 'update'])->name('chart.update');
        Route::delete('/chart-of-accounts/{id}', [ChartOfAccountsController::class, 'destroy'])->name('chart.destroy');

        Route::resource('journal_entries', JournalEntryController::class);

        Route::get('/accounts', [AccountsController::class, 'index'])->name('accounts.index');

        // Receivables
        Route::post('/accounts/receivables', [AccountsController::class, 'storeReceivable'])->name('receivables.store');
        Route::put('/accounts/receivables/{receivable}', [AccountsController::class, 'updateReceivable'])->name('receivables.update');
        Route::delete('/accounts/receivables/{receivable}', [AccountsController::class, 'destroyReceivable'])->name('receivables.destroy');

        // Payables
        Route::post('/accounts/payables', [AccountsController::class, 'storePayable'])->name('payables.store');
        Route::put('/accounts/payables/{payable}', [AccountsController::class, 'updatePayable'])->name('payables.update');
        Route::delete('/accounts/payables/{payable}', [AccountsController::class, 'destroyPayable'])->name('payables.destroy');

        Route::post('/collections/{collection}/approve', [CollectionController::class, 'approve'])->name('collections.approve');

        Route::resource('disbursements', DisbursementController::class);
    });
});
Auth::routes();

Route::get('/export/payables-pdf', [AccountsController::class, 'exportPayablesPDF'])->name('export.payables.pdf');
Route::get('/export/receivables-pdf', [AccountsController::class, 'exportReceivablesPDF'])->name('export.receivables.pdf');

Route::prefix('employee')->group(function () {
    Route::get('/login', [EmployeeAuthController::class, 'showLoginForm'])->name('employee.login');
    Route::post('/login', [EmployeeAuthController::class, 'login'])->name('employee.login.post');
    Route::get('/logout', [EmployeeAuthController::class, 'logout'])->name('employee.logout');

    Route::get('/dashboard', [EmployeeBudgetController::class, 'index'])->name('employee.dashboard');
    Route::post('/budget-requests', [EmployeeBudgetController::class, 'store'])->name('employee.budget.store');
    Route::post('/payment', [EmployeeBudgetController::class, 'paymentstore'])->name('employee.payment.store');
});

// Attendance Portal
Route::get('/attendance', [AttendancePortalController::class, 'index'])->name('attendance.portal');
Route::post('/attendance/check-name', [AttendancePortalController::class, 'checkName'])->name('attendance.checkName');
Route::post('/attendance/verify', [AttendancePortalController::class, 'verify'])->name('attendance.verify');

Route::get('/home', [HomeController::class, 'index'])->name('home');
