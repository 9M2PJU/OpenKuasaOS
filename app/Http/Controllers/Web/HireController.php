<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\HireApplication;
use App\Models\HireInterview;
use App\Models\HireJob;
use App\Services\Hire\AiResumeScreeningService;
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
            'requirements' => ['nullable', 'string'],
            'salary_min' => ['nullable', 'numeric', 'min:0'],
            'salary_max' => ['nullable', 'numeric', 'min:0'],
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

        // 4. Sample Recent Applications (matching screenshot with AI screening insights)
        $sampleApplications = [
            [
                'id' => 'app-1',
                'candidate_name' => 'Li Fen Idris',
                'initial' => 'L',
                'job_title' => 'Customer Support',
                'email' => 'lifen.idris@example.com',
                'phone' => '012-4458921',
                'birth_date' => '12 Apr 2000',
                'age' => 26,
                'address' => 'Petaling Jaya, Selangor',
                'source' => 'LinkedIn',
                'status' => 'Shortlisted',
                'status_type' => 'shortlisted',
                'applied_date' => '13 Oct 2026',
                'is_active' => true,
                'ai_score' => 88,
                'ai_status' => 'layak',
                'ai_status_label' => 'Layak',
                'ai_badge_class' => 'bg-emerald-950/40 text-emerald-400 border-emerald-500/30',
                'cv_file_url' => '#',
                'ai_analysis' => [
                    'summary' => 'Calon mempunyai pengalaman 3 tahun dalam khidmat sokongan pelanggan e-dagang dan fasih bertutur dalam 3 bahasa.',
                    'strengths' => [
                        'Pengalaman khidmat pelanggan e-dagang 3 tahun',
                        'Fasih Bahasa Melayu, Inggeris & Mandarin',
                        'Kemahiran menyelesaikan aduan pelanggan pantas'
                    ],
                    'gaps' => [
                        'Perlu latihan sistem pengurusan tiket dalaman'
                    ],
                    'recommendation' => 'Disyorkan untuk Temuduga Saringan Pertama',
                    'interview_questions' => [
                        'Bagaimanakah anda mengendalikan pelanggan yang emosi akibat kelewatan penghantaran kurier?',
                        'Apakah sistem perkhidmatan pelanggan (CRM/Helpdesk) yang paling mahir anda gunakan?'
                    ]
                ]
            ],
            [
                'id' => 'app-2',
                'candidate_name' => 'Faiz Idris',
                'initial' => 'F',
                'job_title' => 'Graphic Designer',
                'email' => 'faiz.idris@example.com',
                'phone' => '013-9821345',
                'birth_date' => '18 Ogo 2002',
                'age' => 24,
                'address' => 'Cheras, Kuala Lumpur',
                'source' => 'LinkedIn',
                'status' => 'New',
                'status_type' => 'new',
                'applied_date' => '13 Oct 2026',
                'is_active' => true,
                'ai_score' => 64,
                'ai_status' => 'pertimbangan',
                'ai_status_label' => 'Pertimbangan',
                'ai_badge_class' => 'bg-amber-950/40 text-amber-400 border-amber-500/30',
                'cv_file_url' => '#',
                'ai_analysis' => [
                    'summary' => 'Kemahiran asas reka bentuk grafik yang baik, namun portfolio video motion grafik masih terhad.',
                    'strengths' => [
                        'Mahir perisian Adobe Photoshop & Illustrator',
                        'Susun atur tipografi poster media sosial kemas'
                    ],
                    'gaps' => [
                        'Pengalaman video motion & animasi After Effects masih asas'
                    ],
                    'recommendation' => 'KIV untuk Penilaian Ujian Praktikal Rekaan',
                    'interview_questions' => [
                        'Bolehkah anda kongsikan karya grafik komersial yang paling membanggakan anda?',
                        'Berapakah anggaran masa yang anda perlukan bagi menghasilkan 3 set visual iklan harian?'
                    ]
                ]
            ],
            [
                'id' => 'app-3',
                'candidate_name' => 'Siti Lim',
                'initial' => 'S',
                'job_title' => 'Graphic Designer',
                'email' => 'siti.lim@example.com',
                'phone' => '019-3321455',
                'birth_date' => '05 Nov 2005',
                'age' => 21,
                'address' => 'Johor Bahru, Johor',
                'source' => 'JobStreet',
                'status' => 'Rejected',
                'status_type' => 'rejected',
                'applied_date' => '13 Oct 2026',
                'is_active' => false,
                'ai_score' => 38,
                'ai_status' => 'tidak_layak',
                'ai_status_label' => 'Tidak Layak',
                'ai_badge_class' => 'bg-rose-950/40 text-rose-400 border-rose-500/30',
                'cv_file_url' => '#',
                'ai_analysis' => [
                    'summary' => 'Kemahiran reka bentuk tidak memenuhi syarat kelayakan perisian profesional yang diperlukan.',
                    'strengths' => [
                        'Berminat menceburi bidang rekaan kreatif digital'
                    ],
                    'gaps' => [
                        'Tiada pengalaman perisian Adobe CC atau portfolio rekaan komersial',
                        'Lokasi di Johor Bahru sedangkan posisi memerlukan kehadiran di pejabat KL'
                    ],
                    'recommendation' => 'Tidak disyorkan untuk jawatan ini',
                    'interview_questions' => [
                        'Apakah perisian rekaan yang anda pernah gunakan selain Canva?'
                    ]
                ]
            ],
            [
                'id' => 'app-4',
                'candidate_name' => 'Daniel Aminah',
                'initial' => 'D',
                'job_title' => 'Account Manager',
                'email' => 'daniel.a@example.com',
                'phone' => '017-6629910',
                'birth_date' => '22 Mac 1998',
                'age' => 28,
                'address' => 'Shah Alam, Selangor',
                'source' => 'JobStreet',
                'status' => 'New',
                'status_type' => 'new',
                'applied_date' => '13 Oct 2026',
                'is_active' => true,
                'ai_score' => 82,
                'ai_status' => 'layak',
                'ai_status_label' => 'Layak',
                'ai_badge_class' => 'bg-emerald-950/40 text-emerald-400 border-emerald-500/30',
                'cv_file_url' => '#',
                'ai_analysis' => [
                    'summary' => 'Pengalaman mantap dalam pengurusan akaun B2B dan mengekalkan perhubungan pelanggan korporat.',
                    'strengths' => [
                        'Rekod jualan B2B melebihi sasaran syarikat terdahulu',
                        'Kemahiran rundingan kontrak dan pembentangan kukuh'
                    ],
                    'gaps' => [
                        'Pendedahan kepada perisian pengurusan pelanggan moden (CRM)'
                    ],
                    'recommendation' => 'Disyorkan untuk Temuduga Pengurusan Akaun',
                    'interview_questions' => [
                        'Bagaimanakah strategi anda dalam mengekalkan pelanggan berisiko bertukar (churn)?'
                    ]
                ]
            ],
            [
                'id' => 'app-5',
                'candidate_name' => 'Imran Omar',
                'initial' => 'I',
                'job_title' => 'Account Manager',
                'email' => 'imran.omar@example.com',
                'phone' => '011-2334889',
                'birth_date' => '14 Jul 2004',
                'age' => 22,
                'address' => 'Klang, Selangor',
                'source' => 'JobStreet',
                'status' => 'Rejected',
                'status_type' => 'rejected',
                'applied_date' => '13 Oct 2026',
                'is_active' => false,
                'ai_score' => 42,
                'ai_status' => 'tidak_layak',
                'ai_status_label' => 'Tidak Layak',
                'ai_badge_class' => 'bg-rose-950/40 text-rose-400 border-rose-500/30',
                'cv_file_url' => '#',
                'ai_analysis' => [
                    'summary' => 'Calon adalah graduan baru tanpa pengalaman rundingan jualan korporat B2B.',
                    'strengths' => ['Latar belakang pendidikan pentadbiran perniagaan'],
                    'gaps' => ['Tiada pengalaman mengurus akaun pelanggan B2B berprofil tinggi'],
                    'recommendation' => 'Simpan untuk peranan Junior / Eksekutif Sokongan',
                    'interview_questions' => []
                ]
            ],
            [
                'id' => 'app-6',
                'candidate_name' => 'Mei Ling Omar',
                'initial' => 'M',
                'job_title' => 'Sales Executive',
                'email' => 'meiling@example.com',
                'phone' => '016-8877221',
                'birth_date' => '09 Feb 1999',
                'age' => 27,
                'address' => 'Subang Jaya, Selangor',
                'source' => 'Careers page',
                'status' => 'New',
                'status_type' => 'new',
                'applied_date' => '13 Oct 2026',
                'is_active' => true,
                'ai_score' => 91,
                'ai_status' => 'layak',
                'ai_status_label' => 'Layak',
                'ai_badge_class' => 'bg-emerald-950/40 text-emerald-400 border-emerald-500/30',
                'cv_file_url' => '#',
                'ai_analysis' => [
                    'summary' => "Calon cemerlang dengan rekod jualan produk konsumer dan penguasaan teknik 'closing' jualan WhatsApp.",
                    'strengths' => [
                        'Pengalaman closing jualan WhatsApp melebihi RM 150k sebulan',
                        'Pengalaman menggunakan sistem CRM jualan'
                    ],
                    'gaps' => [
                        'Memerlukan latihan produk spesifik syarikat'
                    ],
                    'recommendation' => 'Keutamaan Tinggi: Jadualkan Temuduga Segera',
                    'interview_questions' => [
                        'Apakah teknik penutupan jualan (closing technique) yang paling berkesan bagi anda?',
                        'Berapakah komisen tertinggi yang pernah anda capai dalam sebulan?'
                    ]
                ]
            ],
            [
                'id' => 'app-7',
                'candidate_name' => 'Wei Jie Huda',
                'initial' => 'W',
                'job_title' => 'Sales Executive',
                'email' => 'weijie@example.com',
                'phone' => '014-5556677',
                'birth_date' => '17 Mei 2001',
                'age' => 25,
                'address' => 'Kajang, Selangor',
                'source' => 'Referral',
                'status' => 'New',
                'status_type' => 'new',
                'applied_date' => '12 Oct 2026',
                'is_active' => true,
                'ai_score' => 70,
                'ai_status' => 'pertimbangan',
                'ai_status_label' => 'Pertimbangan',
                'ai_badge_class' => 'bg-amber-950/40 text-amber-400 border-amber-500/30',
                'cv_file_url' => '#',
                'ai_analysis' => [
                    'summary' => 'Mempunyai asas jualan telemarketing, berpotensi jika diberikan bimbingan jualan produk premium.',
                    'strengths' => ['Pengalaman tele-pemasaran 1 tahun', 'Kemahiran komunikasi ramah'],
                    'gaps' => ['Pengalaman jualan bersemuka terhad'],
                    'recommendation' => 'Disyorkan untuk Temuduga Saringan',
                    'interview_questions' => ['Apakah skrip panggilan jualan yang selalu anda gunakan?']
                ]
            ],
            [
                'id' => 'app-8',
                'candidate_name' => 'Arjun Yusof',
                'initial' => 'A',
                'job_title' => 'Accountant',
                'email' => 'arjun.y@example.com',
                'phone' => '012-7788990',
                'birth_date' => '30 Jan 1996',
                'age' => 30,
                'address' => 'Ampang, Selangor',
                'source' => 'JobStreet',
                'status' => 'Rejected',
                'status_type' => 'rejected',
                'applied_date' => '12 Oct 2026',
                'is_active' => false,
                'ai_score' => 45,
                'ai_status' => 'tidak_layak',
                'ai_status_label' => 'Tidak Layak',
                'ai_badge_class' => 'bg-rose-950/40 text-rose-400 border-rose-500/30',
                'cv_file_url' => '#',
                'ai_analysis' => [
                    'summary' => 'Pengalaman kerja audit firma terdahulu tidak sepadan dengan skop perakaunan pengurusan syarikat.',
                    'strengths' => ['Latar belakang ijazah perakaunan'],
                    'gaps' => ['Jangkaan gaji melebihi had bajet jawatan sebanyak 40%'],
                    'recommendation' => 'Tidak disyorkan buat masa ini',
                    'interview_questions' => []
                ]
            ],
            [
                'id' => 'app-9',
                'candidate_name' => 'Amira Kumar',
                'initial' => 'A',
                'job_title' => 'Sales Executive',
                'email' => 'amira.k@example.com',
                'phone' => '018-9900112',
                'birth_date' => '11 Dis 2003',
                'age' => 23,
                'address' => 'Puchong, Selangor',
                'source' => 'LinkedIn',
                'status' => 'Rejected',
                'status_type' => 'rejected',
                'applied_date' => '12 Oct 2026',
                'is_active' => false,
                'ai_score' => 48,
                'ai_status' => 'tidak_layak',
                'ai_status_label' => 'Tidak Layak',
                'ai_badge_class' => 'bg-rose-950/40 text-rose-400 border-rose-500/30',
                'cv_file_url' => '#',
                'ai_analysis' => [
                    'summary' => 'Tiada rekod pengalaman jualan dan tidak menyatakan kemahiran komunikasi komersial.',
                    'strengths' => ['Kemahiran pengkeranian am'],
                    'gaps' => ['Tiada latar belakang jualan atau perkhidmatan pelanggan'],
                    'recommendation' => 'Simpan dalam pangkalan data bakat am',
                    'interview_questions' => []
                ]
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

        // Prevent double submission / duplicate entry
        $existing = HireInterview::where('org_id', $orgId)
            ->where('application_id', $validated['application_id'])
            ->where('scheduled_at', $validated['scheduled_at'])
            ->first();

        if ($existing) {
            return back()->with('info', 'Sesi temu duga untuk calon ini pada masa tersebut telah pun didaftarkan.');
        }

        $interview = HireInterview::create(array_merge($validated, [
            'org_id' => $orgId,
            'meeting_link' => $validated['location_or_link'] ?? null,
            'status' => 'scheduled',
        ]));

        // Advance application stage to 'interview' if still in screening or applied
        $app = HireApplication::find($validated['application_id']);
        if ($app && in_array($app->stage, ['applied', 'screening'])) {
            $app->update(['stage' => 'interview']);
        }

        return back()->with('success', 'Jadual sesi temu duga berjaya ditetapkan.');
    }

    public function updateInterview(Request $request, $id)
    {
        $interview = HireInterview::findOrFail($id);

        $validated = $request->validate([
            'scheduled_at' => ['required', 'date'],
            'interviewer_name' => ['required', 'string', 'max:255'],
            'location_or_link' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'string', 'in:scheduled,completed,cancelled'],
            'notes' => ['nullable', 'string'],
        ], [
            'scheduled_at.required' => 'Tarikh & masa temu duga diperlukan.',
            'interviewer_name.required' => 'Nama penemuduga diperlukan.',
            'status.required' => 'Status temu duga diperlukan.',
        ]);

        $interview->update(array_merge($validated, [
            'meeting_link' => $validated['location_or_link'] ?? $interview->meeting_link,
        ]));

        return back()->with('success', 'Jadual sesi temu duga berjaya dikemaskini.');
    }

    public function destroyInterview($id)
    {
        $interview = HireInterview::findOrFail($id);
        $candidateName = $interview->application->candidate_name ?? 'Calon';
        $interview->delete();

        return back()->with('success', "Jadual sesi temu duga bersama {$candidateName} berjaya dipadamkan.");
    }

    /**
     * Trigger on-demand AI screening for an application.
     */
    public function aiScreenApplication(Request $request, $id, AiResumeScreeningService $screeningService)
    {
        $application = HireApplication::with('job')->findOrFail($id);
        $result = $screeningService->screen($application);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Saringan resume AI bagi calon {$application->candidate_name} selesai dinilai.",
                'ai_status' => $application->ai_status,
                'ai_score' => $application->ai_score,
                'analysis' => $result,
            ]);
        }

        return back()->with('success', "Saringan resume AI bagi calon {$application->candidate_name} selesai dinilai: {$application->ai_score}% ({$application->ai_status_label}).");
    }

    /**
     * Fetch AI screening breakdown details for modal display.
     */
    public function getAiAnalysis($id)
    {
        $application = HireApplication::with('job')->findOrFail($id);

        return response()->json([
            'success' => true,
            'application' => [
                'id' => $application->id,
                'candidate_name' => $application->candidate_name,
                'job_title' => $application->job?->title ?? 'Jawatan',
                'email' => $application->email,
                'phone' => $application->phone,
                'age' => $application->age,
                'birth_date' => $application->birth_date?->format('d M Y'),
                'address' => $application->address,
                'cover_letter' => $application->cover_letter,
                'cv_file_url' => $application->cv_file_url,
                'ai_score' => $application->ai_score,
                'ai_status' => $application->ai_status,
                'ai_status_label' => $application->ai_status_label,
                'ai_badge_class' => $application->ai_badge_class,
                'ai_analysis' => $application->ai_analysis,
                'ai_reviewed_at' => $application->ai_reviewed_at?->format('d M Y, h:i A'),
            ],
        ]);
    }
}
