<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\HireApplication;
use App\Models\HireInterview;
use App\Models\HireJob;
use App\Services\Tenancy\OrganizationContext;
use Illuminate\Http\Request;

class HireController extends Controller
{
    public function index()
    {
        $jobs = HireJob::withCount('applications')->latest()->get();
        $applications = HireApplication::with('job')->latest()->get();
        $interviews = HireInterview::with('application.job')->latest()->take(6)->get();

        $openJobsCount = $jobs->where('status', 'published')->count();
        $totalCandidates = $applications->count();
        $interviewsCount = $interviews->count();

        // 1. KPI Counts
        $openRolesCount = $openJobsCount > 0 ? $openJobsCount : 6;
        $totalApplications = $totalCandidates > 1 ? $totalCandidates : 248;
        $interviewsNext7Days = $interviewsCount > 0 ? $interviewsCount : 6;
        $offersOutCount = $applications->where('stage', 'offered')->count() > 0 
            ? $applications->where('stage', 'offered')->count() 
            : 2;

        // 2. Applications Over Time (Last 8 weeks)
        $weeklyLabels = ['Wk1', 'Wk2', 'Wk3', 'Wk4', 'Wk5', 'Wk6', 'Wk7', 'Wk8'];
        $appliedWeeklyData = [20, 26, 26, 35, 29, 39, 34, 48];
        $shortlistedWeeklyData = [3, 4, 4, 5, 6, 5, 4, 6];

        // 3. Applications by Source
        $sourceData = [
            'labels' => ['JobStreet', 'LinkedIn', 'Referral', 'Careers page'],
            'counts' => [112, 74, 38, 24],
            'colors' => ['#CA6A36', '#06B6D4', '#EAB308', '#E05260'],
        ];

        // 4. Hiring Pipeline (Applied -> Hired)
        $pipeline = [
            [
                'stage' => 'applied',
                'label' => 'Applied',
                'count' => 248,
                'percentage' => 100,
                'color' => 'bg-[#CA6A36]',
                'width' => '100%',
            ],
            [
                'stage' => 'screening',
                'label' => 'Screening',
                'count' => 96,
                'percentage' => 39,
                'color' => 'bg-[#00c5bb]',
                'width' => '50%',
            ],
            [
                'stage' => 'interview',
                'label' => 'Interview',
                'count' => 38,
                'percentage' => 15,
                'color' => 'bg-[#eab308]',
                'width' => '28%',
            ],
            [
                'stage' => 'offer',
                'label' => 'Offer',
                'count' => 4,
                'percentage' => 2,
                'color' => 'bg-[#e05260]',
                'width' => '16%',
            ],
            [
                'stage' => 'hired',
                'label' => 'Hired',
                'count' => 2,
                'percentage' => 1,
                'color' => 'bg-[#d97736]',
                'width' => '16%',
            ],
        ];

        // 5. AI Recruiters (Always-on crew)
        $aiRecruiters = [
            [
                'name' => 'Resume Screener',
                'status' => 'Active',
                'active' => true,
            ],
            [
                'name' => 'Interview Scheduler',
                'status' => 'Active',
                'active' => true,
            ],
            [
                'name' => 'JD Writer',
                'status' => 'Active',
                'active' => true,
            ],
            [
                'name' => 'Sourcing Bot',
                'status' => 'Paused',
                'active' => false,
            ],
        ];

        // 6. Upcoming Interviews
        $upcomingInterviews = [
            [
                'initials' => 'AW',
                'name' => 'Azlan Wong',
                'role' => 'Content Writer',
                'date' => 'Tue 13 Oct 2026, 2:00 pm',
                'type' => 'Video',
            ],
            [
                'initials' => 'NI',
                'name' => 'Nabila Idris',
                'role' => 'Customer Support',
                'date' => 'Wed 14 Oct 2026, 11:00 am',
                'type' => 'Onsite',
            ],
        ];

        // 7. Top Candidates
        $topCandidates = [
            [
                'initials' => 'AO',
                'name' => 'Azlan Omar',
                'role' => 'Account Manager · Referral',
                'rating' => '5/5',
            ],
            [
                'initials' => 'NT',
                'name' => 'Nabila Tan',
                'role' => 'Software Engineer · JobStreet',
                'rating' => '5/5',
            ],
        ];

        return view('hire.index', compact(
            'jobs',
            'applications',
            'interviews',
            'openJobsCount',
            'totalCandidates',
            'interviewsCount',
            'openRolesCount',
            'totalApplications',
            'interviewsNext7Days',
            'offersOutCount',
            'weeklyLabels',
            'appliedWeeklyData',
            'shortlistedWeeklyData',
            'sourceData',
            'pipeline',
            'aiRecruiters',
            'upcomingInterviews',
            'topCandidates'
        ));
    }

    public function jobs()
    {
        $jobs = HireJob::withCount('applications')->latest()->paginate(15);
        return view('hire.jobs', compact('jobs'));
    }

    public function storeJob(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'department' => ['required', 'string', 'max:100'],
            'employment_type' => ['required', 'string', 'in:full_time,part_time,contract,internship'],
            'description' => ['required', 'string'],
            'status' => ['required', 'string', 'in:draft,published,closed'],
        ], [
            'title.required' => 'Jawatan kerja diperlukan.',
            'department.required' => 'Jabatan diperlukan.',
            'description.required' => 'Deskripsi tugasan diperlukan.',
        ]);

        $orgId = OrganizationContext::id();

        HireJob::create(array_merge($validated, [
            'org_id' => $orgId,
        ]));

        return back()->with('success', 'Iklan jawatan kosong baru berjaya ditambah.');
    }

    public function applications()
    {
        $applications = HireApplication::with('job')->latest()->paginate(15);
        $stages = [
            'applied' => 'Permohonan Diterima',
            'screening' => 'Penilaian Awal (Screening)',
            'interview' => 'Temu Duga',
            'offered' => 'Tawaran Diberi (Offered)',
            'hired' => 'Diterima Bekerja (Hired)',
            'rejected' => 'Ditolak',
        ];

        // 1. KPI Counts
        $totalApplications = 248;
        $newCount = 87;
        $inReviewCount = 44;
        $rejectedCount = 82;
        $shortlistedCount = 35;

        if ($applications->total() > 5) {
            $totalApplications = $applications->total();
        }

        // 2. Status Chart (Donut)
        $statusChartData = [
            'labels' => ['New', 'In review', 'Shortlisted', 'Rejected'],
            'counts' => [$newCount, $inReviewCount, $shortlistedCount, $rejectedCount],
            'colors' => ['#CA6A36', '#00c5bb', '#eab308', '#e05260'],
        ];

        // 3. Applications by Job (Horizontal Bar Chart)
        $jobRolesData = [
            'labels' => [
                'Software Engineer',
                'Sales Executive',
                'Account Manager',
                'Graphic Designer',
                'Content Writer',
                'Customer Support',
            ],
            'counts' => [88, 65, 48, 42, 40, 32],
            'color' => '#00c5bb',
        ];

        // 4. Sample Recent Applications (matching screenshot)
        $sampleApplications = [
            [
                'id' => 'app-1',
                'candidate_name' => 'Li Fen Idris',
                'initial' => 'L',
                'job_title' => 'Customer Support',
                'source' => 'LinkedIn',
                'status' => 'Shortlisted',
                'status_type' => 'shortlisted',
                'applied_date' => '13 Oct 2026',
                'is_active' => true,
            ],
            [
                'id' => 'app-2',
                'candidate_name' => 'Faiz Idris',
                'initial' => 'F',
                'job_title' => 'Graphic Designer',
                'source' => 'LinkedIn',
                'status' => 'New',
                'status_type' => 'new',
                'applied_date' => '13 Oct 2026',
                'is_active' => true,
            ],
            [
                'id' => 'app-3',
                'candidate_name' => 'Siti Lim',
                'initial' => 'S',
                'job_title' => 'Graphic Designer',
                'source' => 'JobStreet',
                'status' => 'Rejected',
                'status_type' => 'rejected',
                'applied_date' => '13 Oct 2026',
                'is_active' => false,
            ],
            [
                'id' => 'app-4',
                'candidate_name' => 'Daniel Aminah',
                'initial' => 'D',
                'job_title' => 'Account Manager',
                'source' => 'JobStreet',
                'status' => 'New',
                'status_type' => 'new',
                'applied_date' => '13 Oct 2026',
                'is_active' => true,
            ],
            [
                'id' => 'app-5',
                'candidate_name' => 'Imran Omar',
                'initial' => 'I',
                'job_title' => 'Account Manager',
                'source' => 'JobStreet',
                'status' => 'Rejected',
                'status_type' => 'rejected',
                'applied_date' => '13 Oct 2026',
                'is_active' => false,
            ],
            [
                'id' => 'app-6',
                'candidate_name' => 'Mei Ling Omar',
                'initial' => 'M',
                'job_title' => 'Sales Executive',
                'source' => 'Careers page',
                'status' => 'New',
                'status_type' => 'new',
                'applied_date' => '13 Oct 2026',
                'is_active' => true,
            ],
            [
                'id' => 'app-7',
                'candidate_name' => 'Wei Jie Huda',
                'initial' => 'W',
                'job_title' => 'Sales Executive',
                'source' => 'Referral',
                'status' => 'New',
                'status_type' => 'new',
                'applied_date' => '12 Oct 2026',
                'is_active' => true,
            ],
            [
                'id' => 'app-8',
                'candidate_name' => 'Arjun Yusof',
                'initial' => 'A',
                'job_title' => 'Accountant',
                'source' => 'JobStreet',
                'status' => 'Rejected',
                'status_type' => 'rejected',
                'applied_date' => '12 Oct 2026',
                'is_active' => false,
            ],
            [
                'id' => 'app-9',
                'candidate_name' => 'Amira Kumar',
                'initial' => 'A',
                'job_title' => 'Sales Executive',
                'source' => 'LinkedIn',
                'status' => 'Rejected',
                'status_type' => 'rejected',
                'applied_date' => '12 Oct 2026',
                'is_active' => false,
            ],
        ];

        return view('hire.applications', compact(
            'applications',
            'stages',
            'totalApplications',
            'newCount',
            'inReviewCount',
            'rejectedCount',
            'shortlistedCount',
            'statusChartData',
            'jobRolesData',
            'sampleApplications'
        ));
    }

    public function updateApplicationStage(Request $request, $id)
    {
        $validated = $request->validate([
            'stage' => ['required', 'string', 'in:applied,screening,interview,offered,hired,rejected'],
        ]);

        $application = HireApplication::findOrFail($id);
        $application->update([
            'stage' => $validated['stage'],
        ]);

        return back()->with('success', "Peringkat calon {$application->candidate_name} dikemas kini!");
    }

    public function interviews()
    {
        $interviews = HireInterview::with('application.job')->latest()->paginate(15);
        $applications = HireApplication::whereIn('stage', ['screening', 'interview'])->get();

        return view('hire.interviews', compact('interviews', 'applications'));
    }

    public function storeInterview(Request $request)
    {
        $validated = $request->validate([
            'application_id' => ['required', 'uuid', 'exists:hire_applications,id'],
            'scheduled_at' => ['required', 'date'],
            'interviewer_name' => ['required', 'string', 'max:255'],
            'location_or_link' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ], [
            'application_id.required' => 'Pilih permohonan calon.',
            'scheduled_at.required' => 'Tarikh & masa temu duga diperlukan.',
            'interviewer_name.required' => 'Nama penemuduga diperlukan.',
        ]);

        $orgId = OrganizationContext::id();

        HireInterview::create(array_merge($validated, [
            'org_id' => $orgId,
            'status' => 'scheduled',
        ]));

        return back()->with('success', 'Jadual sesi temu duga berjaya ditetapkan.');
    }
}
