<?php

use App\Http\Controllers\Api\V1\Ai\CommandController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\Crm\ContactController;
use App\Http\Controllers\Api\V1\Crm\DealController;
use App\Http\Controllers\Api\V1\Finance\AccountController;
use App\Http\Controllers\Api\V1\Finance\InvoiceController;
use App\Http\Controllers\Api\V1\Finance\JournalController;
use App\Http\Controllers\Api\V1\Hire\HireController;
use App\Http\Controllers\Api\V1\People\AttendanceController;
use App\Http\Controllers\Api\V1\People\EmployeeController;
use App\Http\Controllers\Api\V1\People\LeaveController;
use App\Http\Controllers\Api\V1\People\PayrollController;
use App\Http\Controllers\Api\V1\Reach\ReachController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Public Auth Endpoints
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
    });

    // Public Hire & Reach endpoints (Candidate application & Lead forms)
    Route::post('hire/applications', [HireController::class, 'storeApplication']);
    Route::post('reach/forms/submit', [ReachController::class, 'submitForm']);

    // Authenticated & Multi-Tenant Scoped Endpoints
    Route::middleware(['auth:sanctum', 'tenant'])->group(function () {
        // Auth viewer
        Route::get('auth/viewer', [AuthController::class, 'viewer']);

        // Finance Module (Bendahara)
        Route::prefix('finance')->group(function () {
            Route::get('invoices', [InvoiceController::class, 'index']);
            Route::post('invoices', [InvoiceController::class, 'store']);
            Route::get('invoices/{id}', [InvoiceController::class, 'show']);
            Route::post('invoices/{id}/post', [InvoiceController::class, 'post']);
            Route::post('invoices/{id}/payments', [InvoiceController::class, 'recordPayment']);
            Route::post('invoices/{id}/void', [InvoiceController::class, 'void']);

            Route::get('accounts', [AccountController::class, 'index']);
            Route::post('accounts', [AccountController::class, 'store']);

            Route::get('journals', [JournalController::class, 'index']);
            Route::get('ledger-accounts', [JournalController::class, 'ledgerAccounts']);
        });

        // People & HR Module (Lekiu)
        Route::prefix('people')->group(function () {
            Route::get('employees', [EmployeeController::class, 'index']);
            Route::post('employees', [EmployeeController::class, 'store']);
            Route::get('employees/{id}', [EmployeeController::class, 'show']);

            Route::get('attendance', [AttendanceController::class, 'index']);
            Route::post('attendance/clock-in', [AttendanceController::class, 'clockIn']);
            Route::post('attendance/clock-out', [AttendanceController::class, 'clockOut']);

            Route::get('leave/requests', [LeaveController::class, 'index']);
            Route::post('leave/requests', [LeaveController::class, 'store']);
            Route::post('leave/requests/{id}/approve', [LeaveController::class, 'approve']);
            Route::post('leave/requests/{id}/reject', [LeaveController::class, 'reject']);

            Route::post('payroll/generate', [PayrollController::class, 'generate']);
            Route::get('payroll/runs', [PayrollController::class, 'runs']);
            Route::get('payslips', [PayrollController::class, 'payslips']);
            Route::get('payslips/{id}', [PayrollController::class, 'showPayslip']);
        });

        // CRM Module (Kasturi)
        Route::prefix('crm')->group(function () {
            Route::get('contacts', [ContactController::class, 'index']);
            Route::post('contacts', [ContactController::class, 'store']);

            Route::get('deals', [DealController::class, 'index']);
            Route::post('deals', [DealController::class, 'store']);
            Route::patch('deals/{id}/stage', [DealController::class, 'updateStage']);
        });

        // Reach Marketing Module (Jebat)
        Route::prefix('reach')->group(function () {
            Route::get('campaigns', [ReachController::class, 'campaigns']);
            Route::post('campaigns', [ReachController::class, 'storeCampaign']);

            Route::get('leads', [ReachController::class, 'leads']);
            Route::post('leads', [ReachController::class, 'storeLead']);
        });

        // Hire Module (Lekir)
        Route::prefix('hire')->group(function () {
            Route::get('jobs', [HireController::class, 'jobs']);
            Route::post('jobs', [HireController::class, 'storeJob']);
            Route::get('jobs/{id}', [HireController::class, 'showJob']);

            Route::get('applications', [HireController::class, 'applications']);
            Route::patch('applications/{id}/stage', [HireController::class, 'updateApplicationStage']);

            Route::get('interviews', [HireController::class, 'interviews']);
            Route::post('interviews', [HireController::class, 'storeInterview']);
        });

        // Tuah AI Command Center
        Route::prefix('command')->group(function () {
            Route::get('threads', [CommandController::class, 'threads']);
            Route::post('threads', [CommandController::class, 'storeThread']);
            Route::post('chat', [CommandController::class, 'chat']);
            Route::get('config', [CommandController::class, 'getConfig']);
            Route::match(['put', 'post'], 'config', [CommandController::class, 'updateConfig']);
            Route::post('test-connection', [CommandController::class, 'testConnection']);
            Route::get('models', [CommandController::class, 'fetchModels']);
        });
    });

    // Superadmin Telemetry API
    Route::middleware(['auth:sanctum', 'superadmin'])->prefix('superadmin')->name('api.superadmin.')->group(function () {
        Route::get('metrics', [\App\Http\Controllers\Api\V1\Superadmin\SuperadminApiController::class, 'metrics'])->name('metrics');
    });
});

