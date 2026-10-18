<?php

namespace Database\Seeders;

use App\Models\AgentConfig;
use App\Models\HrAnnouncement;
use App\Models\HrAttendanceDay;
use App\Models\HrEmployee;
use App\Models\HrLeaveRequest;
use App\Models\Org;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LekiuDemoSeeder extends Seeder
{
    public function run(): void
    {
        $orgs = Org::all();

        foreach ($orgs as $org) {
            $orgId = $org->id;

            // 1. Seed exactly 20 Malaysian Employees across 5 departments
            // Sales: 5, Operations: 6, Finance: 3, Management: 2, Marketing: 4 = 20 total
            $employeesData = [
                // Management (2)
                ['no' => 'EMP-001', 'name' => 'Ahmad Zulkifli', 'pos' => 'Pengarah Urusan (CEO)', 'salary' => 9500, 'join' => '2026-03-01'],
                ['no' => 'EMP-002', 'name' => 'Irfan Hakim', 'pos' => 'Ketua Pegawai Operasi (COO)', 'salary' => 7500, 'join' => '2026-03-15'],

                // Operations (6)
                ['no' => 'EMP-003', 'name' => 'Daniel Tan', 'pos' => 'Jurutera Perisian Utama', 'salary' => 6200, 'join' => '2026-04-01'],
                ['no' => 'EMP-004', 'name' => 'Lim Wei Jie', 'pos' => 'Jurutera Sistem Operasi Kanan', 'salary' => 5400, 'join' => '2026-04-20'],
                ['no' => 'EMP-005', 'name' => 'Amira Kumar', 'pos' => 'Penyelia Sokongan Teknikal', 'salary' => 4200, 'join' => '2026-05-10'],
                ['no' => 'EMP-006', 'name' => 'Mei Ling Omar', 'pos' => 'Penyelaras Logistik & Operasi', 'salary' => 3800, 'join' => '2026-06-01'],
                ['no' => 'EMP-007', 'name' => 'Wei Jie Huda', 'pos' => 'Pegawai Jaminan Kualiti (QA)', 'salary' => 4500, 'join' => '2026-06-25'],
                ['no' => 'EMP-008', 'name' => 'Siti Fatimah', 'pos' => 'Eksekutif Operasi HR', 'salary' => 3600, 'join' => '2026-07-15'],

                // Finance (3)
                ['no' => 'EMP-009', 'name' => 'Nurul Aina', 'pos' => 'Eksekutif Kanan Kewangan', 'salary' => 5000, 'join' => '2026-05-01'],
                ['no' => 'EMP-010', 'name' => 'Arjun Yusof', 'pos' => 'Akauntan Pengurusan Bertauliah', 'salary' => 5800, 'join' => '2026-07-01'],
                ['no' => 'EMP-011', 'name' => 'Fadzil Ramli', 'pos' => 'Eksekutif Penggajian (Payroll)', 'salary' => 3700, 'join' => '2026-08-01'],

                // Sales (5)
                ['no' => 'EMP-012', 'name' => 'Aisyah Rahim', 'pos' => 'Pengurus Jualan Korporat (Sales)', 'salary' => 6500, 'join' => '2026-08-15'],
                ['no' => 'EMP-013', 'name' => 'Farhan Roslan', 'pos' => 'Eksekutif Jualan Kanan (Sales)', 'salary' => 4800, 'join' => '2026-09-01'],
                ['no' => 'EMP-014', 'name' => 'Daniel Aminah', 'pos' => 'Sales Account Manager', 'salary' => 4600, 'join' => '2026-09-12'],
                ['no' => 'EMP-015', 'name' => 'Siti Lim', 'pos' => 'Sales Representative', 'salary' => 3500, 'join' => '2026-09-25'],
                ['no' => 'EMP-016', 'name' => 'Azlan Wong', 'pos' => 'Eksekutif Pembangunan Bisnes (Sales)', 'salary' => 4200, 'join' => '2026-10-01'],

                // Marketing (4)
                ['no' => 'EMP-017', 'name' => 'Nurul Huda', 'pos' => 'Pengurus Pemasaran Digital', 'salary' => 6000, 'join' => '2026-10-02'],
                ['no' => 'EMP-018', 'name' => 'Faiz Idris', 'pos' => 'Pereka Grafik Kreatif Pemasaran', 'salary' => 4000, 'join' => '2026-10-05'],
                ['no' => 'EMP-019', 'name' => 'Li Fen Idris', 'pos' => 'Pakar Kandungan & Media Sosial', 'salary' => 3800, 'join' => '2026-10-08'],
                ['no' => 'EMP-020', 'name' => 'Tan Mei Ling', 'pos' => 'Eksekutif Komunikasi & Pemasaran', 'salary' => 3900, 'join' => '2026-10-10'],
            ];

            $employeeMap = [];
            foreach ($employeesData as $idx => $ed) {
                $emp = HrEmployee::updateOrCreate(
                    [
                        'org_id' => $orgId,
                        'employee_number' => $ed['no'],
                    ],
                    [
                        'full_name' => $ed['name'],
                        'ic_passport_number' => sprintf('%02d%02d%02d-10-%04d', rand(85, 99), rand(1, 12), rand(1, 28), rand(1000, 9999)),
                        'position' => $ed['pos'],
                        'join_date' => $ed['join'],
                        'basic_salary' => $ed['salary'],
                        'epf_rate' => 11.00,
                        'status' => 'active',
                        'annual_leave_balance' => 14.0,
                        'medical_leave_balance' => 14.0,
                    ]
                );
                $employeeMap[$ed['name']] = $emp;
            }

            $today = Carbon::today();

            // 2. Seed Clocked In Today: Exactly 16 employees clocked in today
            $allEmpModels = array_values($employeeMap);
            $clockedInEmployees = array_slice($allEmpModels, 0, 16);

            foreach ($clockedInEmployees as $i => $emp) {
                $randomMin = rand(2, 58);
                $clockInTime = sprintf('%02d:%02d:00', rand(8, 9), $randomMin);
                HrAttendanceDay::updateOrCreate(
                    [
                        'org_id' => $orgId,
                        'employee_id' => $emp->id,
                        'date' => $today->toDateString(),
                    ],
                    [
                        'clock_in' => $clockInTime,
                        'clock_out' => null,
                        'status' => 'present',
                        'latitude' => '3.1390',
                        'longitude' => '101.6869',
                    ]
                );
            }

            // 3. Seed On Leave Today: Exactly 3 employees on leave today
            // Must include Lim Wei Jie on Medical leave today
            $limWeiJie = $employeeMap['Lim Wei Jie'] ?? $allEmpModels[3];
            $sitiFatimah = $employeeMap['Siti Fatimah'] ?? $allEmpModels[7];
            $farhanRoslan = $employeeMap['Farhan Roslan'] ?? $allEmpModels[12];

            HrLeaveRequest::updateOrCreate(
                [
                    'org_id' => $orgId,
                    'employee_id' => $limWeiJie->id,
                    'start_date' => $today->toDateString(),
                ],
                [
                    'leave_type' => 'medical',
                    'end_date' => $today->toDateString(),
                    'days_count' => 1,
                    'reason' => 'Demam dan rawatan pesakit luar klinik.',
                    'status' => 'approved',
                ]
            );

            HrLeaveRequest::updateOrCreate(
                [
                    'org_id' => $orgId,
                    'employee_id' => $sitiFatimah->id,
                    'start_date' => $today->toDateString(),
                ],
                [
                    'leave_type' => 'annual',
                    'end_date' => $today->copy()->addDays(2)->toDateString(),
                    'days_count' => 3,
                    'reason' => 'Cuti rehat tahunan bersama keluarga.',
                    'status' => 'approved',
                ]
            );

            HrLeaveRequest::updateOrCreate(
                [
                    'org_id' => $orgId,
                    'employee_id' => $farhanRoslan->id,
                    'start_date' => $today->toDateString(),
                ],
                [
                    'leave_type' => 'annual',
                    'end_date' => $today->toDateString(),
                    'days_count' => 1,
                    'reason' => 'Urusan peribadi.',
                    'status' => 'approved',
                ]
            );

            // Additional approved leaves this month to sum to:
            // Annual leave: 14 days, Emergency leave: 6 days, Medical leave: 6 days
            // We currently have: Annual: 3+1 = 4 days, Medical: 1 day.
            // Add Annual: 10 days, Emergency: 6 days, Medical: 5 days
            $danielTan = $employeeMap['Daniel Tan'] ?? $allEmpModels[2];
            $arjunYusof = $employeeMap['Arjun Yusof'] ?? $allEmpModels[9];
            $meilingOmar = $employeeMap['Mei Ling Omar'] ?? $allEmpModels[5];
            $amirKumar = $employeeMap['Amira Kumar'] ?? $allEmpModels[4];

            HrLeaveRequest::updateOrCreate(
                [
                    'org_id' => $orgId,
                    'employee_id' => $danielTan->id,
                    'start_date' => $today->copy()->startOfMonth()->addDays(2)->toDateString(),
                ],
                [
                    'leave_type' => 'annual',
                    'end_date' => $today->copy()->startOfMonth()->addDays(11)->toDateString(),
                    'days_count' => 10,
                    'reason' => 'Cuti rehat percutian tahunan.',
                    'status' => 'approved',
                ]
            );

            HrLeaveRequest::updateOrCreate(
                [
                    'org_id' => $orgId,
                    'employee_id' => $arjunYusof->id,
                    'start_date' => $today->copy()->startOfMonth()->addDays(5)->toDateString(),
                ],
                [
                    'leave_type' => 'emergency',
                    'end_date' => $today->copy()->startOfMonth()->addDays(10)->toDateString(),
                    'days_count' => 6,
                    'reason' => 'Kecemasan pembaikan rumah.',
                    'status' => 'approved',
                ]
            );

            HrLeaveRequest::updateOrCreate(
                [
                    'org_id' => $orgId,
                    'employee_id' => $meilingOmar->id,
                    'start_date' => $today->copy()->startOfMonth()->addDays(3)->toDateString(),
                ],
                [
                    'leave_type' => 'medical',
                    'end_date' => $today->copy()->startOfMonth()->addDays(7)->toDateString(),
                    'days_count' => 5,
                    'reason' => 'Cuti sakit kemasukan hospital.',
                    'status' => 'approved',
                ]
            );

            // 4. Seed Exactly 6 Pending Approvals
            // Must include Tan Mei Ling (Emergency leave, 1 day · 17 Oct)
            $tanMeiLing = $employeeMap['Tan Mei Ling'] ?? $allEmpModels[19];
            $azlanWong = $employeeMap['Azlan Wong'] ?? $allEmpModels[15];
            $faizIdris = $employeeMap['Faiz Idris'] ?? $allEmpModels[17];
            $danielAminah = $employeeMap['Daniel Aminah'] ?? $allEmpModels[13];
            $sitiLim = $employeeMap['Siti Lim'] ?? $allEmpModels[14];

            HrLeaveRequest::updateOrCreate(
                [
                    'org_id' => $orgId,
                    'employee_id' => $tanMeiLing->id,
                    'start_date' => '2026-10-17',
                ],
                [
                    'leave_type' => 'emergency',
                    'end_date' => '2026-10-17',
                    'days_count' => 1,
                    'reason' => 'Kecemasan urusan keluarga penting.',
                    'status' => 'pending',
                ]
            );

            HrLeaveRequest::updateOrCreate(
                [
                    'org_id' => $orgId,
                    'employee_id' => $azlanWong->id,
                    'start_date' => '2026-10-21',
                ],
                [
                    'leave_type' => 'annual',
                    'end_date' => '2026-10-22',
                    'days_count' => 2,
                    'reason' => 'Cuti peribadi.',
                    'status' => 'pending',
                ]
            );

            HrLeaveRequest::updateOrCreate(
                [
                    'org_id' => $orgId,
                    'employee_id' => $amirKumar->id,
                    'start_date' => '2026-10-19',
                ],
                [
                    'leave_type' => 'medical',
                    'end_date' => '2026-10-19',
                    'days_count' => 1,
                    'reason' => 'Pemeriksaan kesihatan klinik.',
                    'status' => 'pending',
                ]
            );

            HrLeaveRequest::updateOrCreate(
                [
                    'org_id' => $orgId,
                    'employee_id' => $faizIdris->id,
                    'start_date' => '2026-10-24',
                ],
                [
                    'leave_type' => 'annual',
                    'end_date' => '2026-10-25',
                    'days_count' => 2,
                    'reason' => 'Majlis keluarga.',
                    'status' => 'pending',
                ]
            );

            HrLeaveRequest::updateOrCreate(
                [
                    'org_id' => $orgId,
                    'employee_id' => $danielAminah->id,
                    'start_date' => '2026-10-18',
                ],
                [
                    'leave_type' => 'emergency',
                    'end_date' => '2026-10-18',
                    'days_count' => 1,
                    'reason' => 'Kecemasan kenderaan rosak.',
                    'status' => 'pending',
                ]
            );

            HrLeaveRequest::updateOrCreate(
                [
                    'org_id' => $orgId,
                    'employee_id' => $sitiLim->id,
                    'start_date' => '2026-10-28',
                ],
                [
                    'leave_type' => 'annual',
                    'end_date' => '2026-10-29',
                    'days_count' => 2,
                    'reason' => 'Rehat hujung bulan.',
                    'status' => 'pending',
                ]
            );

            // 5. Seed HR AI Agents in agent_configs
            $hrAgents = [
                ['name' => 'Leave Approver', 'prompt' => 'AI Ejen menyemak kelayakan baki cuti dan meluluskan secara pintar.', 'active' => true],
                ['name' => 'Payroll Assistant', 'prompt' => 'AI Ejen mengira potongan KWSP, SOCSO, PCB dan menjana slip gaji.', 'active' => true],
                ['name' => 'Onboarding Guide', 'prompt' => 'AI Ejen membantu pekerja baharu melengkapkan profil dan dokumen HR.', 'active' => true],
                ['name' => 'Attendance Monitor', 'prompt' => 'AI Ejen memantau anomali waktu punch card dan amaran ketidakhadiran.', 'active' => false],
            ];

            foreach ($hrAgents as $ha) {
                AgentConfig::updateOrCreate(
                    [
                        'org_id' => $orgId,
                        'agent_name' => $ha['name'],
                    ],
                    [
                        'model' => 'gemma4:e2b',
                        'provider' => 'ollama',
                        'gemini_model' => 'gemini-1.5-flash',
                        'ollama_url' => 'http://192.168.1.99:11434',
                        'ollama_model' => 'gemma4:e2b',
                        'system_prompt' => $ha['prompt'],
                        'tools_enabled' => ['people', 'attendance', 'leave'],
                        'temperature' => 0.30,
                        'is_active' => $ha['active'],
                    ]
                );
            }

            // 6. Seed Announcements (matching screenshot)
            $announcements = [
                [
                    'title' => 'Year-end dinner',
                    'content' => 'Save the date for the year-end dinner. Venue and time to be confirmed.',
                    'category' => 'general',
                    'status' => 'draft',
                    'author_name' => 'Siti Lestari',
                    'effective_date' => '2026-12-31',
                    'created_at' => Carbon::parse('2026-10-12 11:30:00'),
                ],
                [
                    'title' => 'Office closed for National Day',
                    'content' => 'The office is closed on 31 August. Support runs a skeleton shift.',
                    'category' => 'holiday',
                    'status' => 'published',
                    'author_name' => 'Siti Lestari',
                    'effective_date' => '2026-10-25',
                    'created_at' => Carbon::now()->subDays(4),
                ],
                [
                    'title' => 'New panel clinics added',
                    'content' => 'Three more panel clinics are available under the medical benefit.',
                    'category' => 'benefits',
                    'status' => 'published',
                    'author_name' => 'Siti Lestari',
                    'effective_date' => '2026-10-20',
                    'created_at' => Carbon::now()->subDays(7),
                ],
                [
                    'title' => 'Quarterly town hall',
                    'content' => 'Join the town hall this Friday at 3pm in the main meeting room.',
                    'category' => 'general',
                    'status' => 'published',
                    'author_name' => 'Siti Lestari',
                    'effective_date' => '2026-10-17',
                    'created_at' => Carbon::now()->subDays(7),
                ],
                [
                    'title' => 'Updated leave policy',
                    'content' => 'Annual leave may now be carried forward up to five days.',
                    'category' => 'policy',
                    'status' => 'published',
                    'author_name' => 'Siti Lestari',
                    'effective_date' => '2026-10-18',
                    'created_at' => Carbon::now()->subDays(21),
                ],
                [
                    'title' => 'Second-half priorities',
                    'content' => 'Leadership has shared the three priorities for the second half.',
                    'category' => 'strategy',
                    'status' => 'published',
                    'author_name' => 'Siti Lestari',
                    'effective_date' => '2026-09-10', // Past date: cannot be edited or removed
                    'created_at' => Carbon::parse('2026-09-10 09:15:00'),
                ],
            ];

            foreach ($announcements as $ann) {
                HrAnnouncement::updateOrCreate(
                    [
                        'org_id' => $orgId,
                        'title' => $ann['title'],
                    ],
                    [
                        'content' => $ann['content'],
                        'category' => $ann['category'],
                        'status' => $ann['status'],
                        'author_name' => $ann['author_name'],
                        'effective_date' => $ann['effective_date'],
                        'created_at' => $ann['created_at'],
                        'updated_at' => $ann['created_at'],
                    ]
                );
            }
        }
    }
}
