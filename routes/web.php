<?php 

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TimeTrackingController;
use App\Http\Controllers\TimesheetController;
use App\Http\Controllers\LeaveManagementController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\ClaimsController;
use App\Http\Controllers\DashboardController;

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

    Route::prefix('hr')->group(function () {
        //Time Tracking
        Route::get('/time-tracking', [TimeTrackingController::class, 'index'])->name('timetracking.index');
        Route::post('/time-tracking', [TimeTrackingController::class, 'store'])->name('timetracking.store');

        //Timesheet
        Route::get('/timesheet', [TimesheetController::class, 'index'])->name('timesheet.index');
        Route::post('/timesheet', [TimesheetController::class, 'store'])->name('timesheet.store');
        Route::put('/timesheet/{timesheet}', [TimesheetController::class, 'update'])->name('timesheet.update');
        Route::delete('/timesheet/{timesheet}', [TimesheetController::class, 'destroy'])->name('timesheet.destroy');

        //Timesheet Data
        Route::get('/timesheet/employee/{employeeId}/details', [TimesheetController::class, 'details'])->name('timesheet.employee.details');

        //Leave Management
        Route::get('/leave-management', [LeaveManagementController::class, 'index'])->name('leave.index');
        Route::post('/leave-type', [LeaveManagementController::class, 'storeType'])->name('leave.storeType');
        Route::post('/employee-leave', [LeaveManagementController::class, 'storeEmployeeLeave'])->name('leave.storeEmployeeLeave');

        // Shift and Schedule
        Route::get('/shifts', [ShiftController::class, 'index'])->name('shifts.index');
        Route::post('/shifts', [ShiftController::class, 'store'])->name('shifts.store');
        Route::put('/shifts/{shift}', [ShiftController::class, 'update'])->name('shifts.update');
        Route::delete('/shifts/{shift}', [ShiftController::class, 'destroy'])->name('shifts.destroy');

        //Claims and Reimbursment
        Route::get('/claims', [ClaimsController::class, 'index'])->name('claims.index');
        Route::post('/claims', [ClaimsController::class, 'store'])->name('claims.store');

        Route::get('/timesheet/report', [TimesheetController::class, 'report'])->name('timesheet.report');

        Route::get('/timesheet/download/{employee}', [TimesheetController::class, 'download'])->name('timesheet.download');
    });
});
Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
