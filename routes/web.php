<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\CommandController;
use App\Http\Controllers\Web\CrmController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\FinanceController;
use App\Http\Controllers\Web\HireController;
use App\Http\Controllers\Web\PeopleController;
use App\Http\Controllers\Web\ReachController;
use App\Http\Controllers\Web\SettingsController;
use App\Http\Controllers\Web\Superadmin\SuperadminDashboardController;
use App\Http\Controllers\Web\Superadmin\SuperadminOrgController;
use App\Http\Controllers\Web\Superadmin\SuperadminSettingsController;
use App\Http\Controllers\Web\Superadmin\SuperadminUserController;
use App\Http\Controllers\Web\LandingController;
use Illuminate\Support\Facades\Route;

// Official Landing Page
Route::get('/', [LandingController::class, 'index'])->name('landing');

// Guest Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login']);
    Route::get('register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('register', [AuthController::class, 'register']);
});

// Authenticated Business OS Routes
Route::middleware(['auth', 'tenant'])->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('switch-org/{orgId}', [AuthController::class, 'switchOrg'])->name('org.switch');

    // Dashboard Overview
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Tuah AI Command Center
    Route::prefix('command')->name('command.')->group(function () {
        Route::get('/', [CommandController::class, 'index'])->name('index');
        Route::post('chat', [CommandController::class, 'sendPrompt'])->name('chat');
        Route::post('settings', [CommandController::class, 'updateSettings'])->name('settings.update');
        Route::post('test-connection', [CommandController::class, 'testConnection'])->name('test-connection');
        Route::get('models', [CommandController::class, 'fetchModels'])->name('models');
        Route::delete('threads/{id}', [CommandController::class, 'deleteThread'])->name('thread.delete');
    });

    // Bendahara (Finance & Invoicing)
    Route::prefix('finance')->name('finance.')->group(function () {
        Route::get('/', [FinanceController::class, 'index'])->name('index');
        Route::get('invoices', [FinanceController::class, 'invoices'])->name('invoices');
        Route::get('invoices/create', [FinanceController::class, 'createInvoice'])->name('invoices.create');
        Route::post('invoices', [FinanceController::class, 'storeInvoice'])->name('invoices.store');
        Route::get('invoices/{id}', [FinanceController::class, 'showInvoice'])->name('invoices.show');
        Route::post('invoices/{id}/post', [FinanceController::class, 'postInvoice'])->name('invoices.post');
        Route::post('invoices/{id}/payments', [FinanceController::class, 'recordPayment'])->name('invoices.pay');
        Route::post('invoices/{id}/void', [FinanceController::class, 'voidInvoice'])->name('invoices.void');
        Route::get('accounts', [FinanceController::class, 'accounts'])->name('accounts');
        Route::post('accounts', [FinanceController::class, 'storeAccount'])->name('accounts.store');
        Route::get('journals', [FinanceController::class, 'journals'])->name('journals');
        Route::get('expenses', [FinanceController::class, 'expenses'])->name('expenses');
        Route::get('expenses/create', [FinanceController::class, 'createExpense'])->name('expenses.create');
        Route::post('expenses', [FinanceController::class, 'storeExpense'])->name('expenses.store');
        Route::get('expenses/{id}', [FinanceController::class, 'showExpense'])->name('expenses.show');
        Route::post('expenses/{id}/void', [FinanceController::class, 'voidExpense'])->name('expenses.void');
        Route::get('quotations', [FinanceController::class, 'quotations'])->name('quotations');
        Route::get('quotations/create', [FinanceController::class, 'createQuotation'])->name('quotations.create');
        Route::post('quotations', [FinanceController::class, 'storeQuotation'])->name('quotations.store');
        Route::get('quotations/{id}', [FinanceController::class, 'showQuotation'])->name('quotations.show');
        Route::post('quotations/{id}/convert', [FinanceController::class, 'convertQuotation'])->name('quotations.convert');
        Route::post('quotations/{id}/status', [FinanceController::class, 'updateQuotationStatus'])->name('quotations.status');
    });

    // Lekiu (People, HR & Payroll)
    Route::prefix('people')->name('people.')->group(function () {
        Route::get('/', [PeopleController::class, 'index'])->name('index');
        Route::get('employees', [PeopleController::class, 'employees'])->name('employees');
        Route::post('employees', [PeopleController::class, 'storeEmployee'])->name('employees.store');
        Route::get('attendance', [PeopleController::class, 'attendance'])->name('attendance');
        Route::post('attendance/clock-in', [PeopleController::class, 'clockIn'])->name('attendance.clock-in');
        Route::post('attendance/clock-out', [PeopleController::class, 'clockOut'])->name('attendance.clock-out');
        Route::get('leave', [PeopleController::class, 'leave'])->name('leave');
        Route::post('leave', [PeopleController::class, 'storeLeave'])->name('leave.store');
        Route::post('leave/{id}/approve', [PeopleController::class, 'approveLeave'])->name('leave.approve');
        Route::post('leave/{id}/reject', [PeopleController::class, 'rejectLeave'])->name('leave.reject');
        Route::get('payroll', [PeopleController::class, 'payroll'])->name('payroll');
        Route::post('payroll/generate', [PeopleController::class, 'generatePayroll'])->name('payroll.generate');
        Route::get('payroll/payslips/{id}', [PeopleController::class, 'showPayslip'])->name('payroll.payslip');
        Route::get('announcements', [PeopleController::class, 'announcements'])->name('announcements');
        Route::post('announcements', [PeopleController::class, 'storeAnnouncement'])->name('announcements.store');
        Route::put('announcements/{id}', [PeopleController::class, 'updateAnnouncement'])->name('announcements.update');
        Route::delete('announcements/{id}', [PeopleController::class, 'destroyAnnouncement'])->name('announcements.destroy');
    });

    // Kasturi (CRM & Pipeline)
    Route::prefix('crm')->name('crm.')->group(function () {
        Route::get('/', [CrmController::class, 'index'])->name('index');
        Route::get('deals', [CrmController::class, 'deals'])->name('deals');
        Route::post('deals', [CrmController::class, 'storeDeal'])->name('deals.store');
        Route::post('deals/{id}/stage', [CrmController::class, 'updateDealStage'])->name('deals.update-stage');
        Route::get('contacts', [CrmController::class, 'contacts'])->name('contacts');
        Route::post('contacts', [CrmController::class, 'storeContact'])->name('contacts.store');
        Route::get('calendar', [CrmController::class, 'calendar'])->name('calendar');
        Route::post('appointments', [CrmController::class, 'storeAppointment'])->name('appointments.store');
        Route::post('appointments/{id}/status', [CrmController::class, 'updateAppointmentStatus'])->name('appointments.update-status');
        Route::delete('appointments/{id}', [CrmController::class, 'destroyAppointment'])->name('appointments.destroy');

        // Produk & Servis (Katalog Jualan)
        Route::get('products', [CrmController::class, 'products'])->name('products');
        Route::post('products', [CrmController::class, 'storeProduct'])->name('products.store');
        Route::put('products/{id}', [CrmController::class, 'updateProduct'])->name('products.update');
        Route::delete('products/{id}', [CrmController::class, 'deleteProduct'])->name('products.delete');
    });

    // Jebat (Reach & Ads)
    Route::prefix('reach')->name('reach.')->group(function () {
        Route::get('/', [ReachController::class, 'index'])->name('index');
        Route::get('campaigns', [ReachController::class, 'campaigns'])->name('campaigns');
        Route::post('campaigns', [ReachController::class, 'storeCampaign'])->name('campaigns.store');
        Route::get('leads', [ReachController::class, 'leads'])->name('leads');
        Route::get('ad-settings', [ReachController::class, 'adSettings'])->name('settings');
        Route::post('ad-settings', [ReachController::class, 'updateAdSettings'])->name('settings.update');
        Route::post('ad-settings/connect', [ReachController::class, 'connectProvider'])->name('settings.connect');
        Route::post('ad-settings/disconnect', [ReachController::class, 'disconnectProvider'])->name('settings.disconnect');

        // Studio Iklan Pintar (AI Ad & Copywriting Generator untuk Produk & Jawatan)
        Route::get('studio', [ReachController::class, 'adStudio'])->name('studio');
        Route::post('generate-copy', [ReachController::class, 'generateAdCopy'])->name('generate-copy');
    });

    // Lekir (Hire & ATS)
    Route::prefix('hire')->name('hire.')->group(function () {
        Route::get('/', [HireController::class, 'index'])->name('index');
        Route::get('jobs', [HireController::class, 'jobs'])->name('jobs');
        Route::post('jobs', [HireController::class, 'storeJob'])->name('jobs.store');
        Route::get('applications', [HireController::class, 'applications'])->name('applications');
        Route::post('applications/{id}/stage', [HireController::class, 'updateApplicationStage'])->name('applications.update-stage');
        Route::get('interviews', [HireController::class, 'interviews'])->name('interviews');
        Route::post('interviews', [HireController::class, 'storeInterview'])->name('interviews.store');
    });

    // Tetapan (Settings & Preferences)
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::post('language', [SettingsController::class, 'updateLanguage'])->name('language');
        Route::post('profile', [SettingsController::class, 'updateProfile'])->name('profile');
        Route::post('password', [SettingsController::class, 'updatePassword'])->name('password');
    });
});

// Superadmin Master Console Routes
Route::middleware(['auth', 'superadmin'])->prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('/', [SuperadminDashboardController::class, 'index'])->name('dashboard');

    // Pengurusan Pengguna
    Route::get('users', [SuperadminUserController::class, 'index'])->name('users.index');
    Route::post('users/{id}/toggle-superadmin', [SuperadminUserController::class, 'toggleSuperadmin'])->name('users.toggle-superadmin');
    Route::post('users/{id}/status', [SuperadminUserController::class, 'updateStatus'])->name('users.status');

    // Pengurusan Organisasi & Bantuan Impersonasi
    Route::get('organizations', [SuperadminOrgController::class, 'index'])->name('organizations.index');
    Route::post('organizations/{id}/impersonate', [SuperadminOrgController::class, 'impersonate'])->name('organizations.impersonate');
    Route::post('impersonate/leave', [SuperadminOrgController::class, 'leaveImpersonation'])->name('impersonate.leave');

    // Tetapan Sistem & Pelayan
    Route::get('system', [SuperadminSettingsController::class, 'index'])->name('system.index');
    Route::post('system/settings', [SuperadminSettingsController::class, 'update'])->name('system.settings.update');
    Route::post('system/cache-clear', [SuperadminSettingsController::class, 'clearCache'])->name('system.cache.clear');
    Route::post('system/clear-cache', [SuperadminSettingsController::class, 'clearCache'])->name('system.clear-cache');
});

