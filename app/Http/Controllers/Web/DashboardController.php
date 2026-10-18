<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\CrmDeal;
use App\Models\FinanceInvoice;
use App\Models\HireJob;
use App\Models\HrAttendanceDay;
use App\Models\HrEmployee;
use App\Models\Lead;
use App\Services\Tenancy\OrganizationContext;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $orgId = OrganizationContext::id();

        $stats = [
            'total_revenue' => FinanceInvoice::where('status', 'paid')->sum('total_amount') ?? 0,
            'unpaid_invoices' => FinanceInvoice::whereIn('status', ['posted', 'overdue'])->sum('total_amount') ?? 0,
            'active_deals_sum' => CrmDeal::whereNotIn('stage_id', ['won', 'lost'])->sum('amount') ?? 0,
            'active_deals_count' => CrmDeal::whereNotIn('stage_id', ['won', 'lost'])->count(),
            'total_employees' => HrEmployee::where('status', 'active')->count(),
            'attendance_today' => HrAttendanceDay::whereDate('date', Carbon::today())->count(),
            'active_campaigns' => Campaign::where('status', 'active')->count(),
            'total_leads' => Lead::count(),
            'open_jobs' => HireJob::where('status', 'published')->count(),
        ];

        $recentInvoices = FinanceInvoice::latest()->take(5)->get();
        $recentDeals = CrmDeal::latest()->take(5)->get();
        $recentEmployees = HrEmployee::where('status', 'active')->latest()->take(5)->get();

        return view('dashboard', compact('stats', 'recentInvoices', 'recentDeals', 'recentEmployees'));
    }
}
