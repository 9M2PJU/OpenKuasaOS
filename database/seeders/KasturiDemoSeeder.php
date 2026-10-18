<?php

namespace Database\Seeders;

use App\Models\AgentConfig;
use App\Models\AgentRun;
use App\Models\CrmContact;
use App\Models\CrmDeal;
use App\Models\Org;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class KasturiDemoSeeder extends Seeder
{
    public function run(): void
    {
        $orgs = Org::all();

        foreach ($orgs as $org) {
            $orgId = $org->id;

            // 1. Seed or update 6 AI Agents for CRM Kasturi
            $agentsMeta = [
                [
                    'name' => 'Deal Scorer',
                    'system_prompt' => 'AI Agent pakar menilai kelayakan prospek (lead scoring) dan kebarangkalian penutupan deal (win probability).',
                    'tools' => ['crm', 'deal_scoring', 'pipeline_analysis'],
                    'temp' => 0.30,
                    'is_active' => true,
                    'target_completed' => 480,
                ],
                [
                    'name' => 'Follow-up Writer',
                    'system_prompt' => 'AI Agent merangka emel dan mesej susulan (follow-up) berkesan berdasarkan interaksi pelanggan.',
                    'tools' => ['crm', 'email_composer', 'whatsapp_templates'],
                    'temp' => 0.70,
                    'is_active' => true,
                    'target_completed' => 390,
                ],
                [
                    'name' => 'Churn Predictor',
                    'system_prompt' => 'AI Agent menganalisis pola tingkah laku pelanggan dan memberi amaran awal risiko pembatalan (churn risk).',
                    'tools' => ['crm', 'churn_analytics', 'risk_alerts'],
                    'temp' => 0.20,
                    'is_active' => true,
                    'target_completed' => 290,
                ],
                [
                    'name' => 'Meeting Summarizer',
                    'system_prompt' => 'AI Agent merumuskan nota perbincangan jualan dan menjana senarai tindakan seterusnya (action items).',
                    'tools' => ['crm', 'transcript_summary', 'task_extractor'],
                    'temp' => 0.40,
                    'is_active' => true,
                    'target_completed' => 220,
                ],
                [
                    'name' => 'Pipeline Forecaster',
                    'system_prompt' => 'AI Agent meramal unjuran hasil jualan bulanan dan suku tahunan berdasarkan halaju deal.',
                    'tools' => ['crm', 'revenue_forecast', 'pipeline_velocity'],
                    'temp' => 0.25,
                    'is_active' => true,
                    'target_completed' => 140,
                ],
                [
                    'name' => 'Data Enricher',
                    'system_prompt' => 'AI Agent mencari dan melengkapkan profil syarikat, industri, dan maklumat kontak prospek.',
                    'tools' => ['crm', 'enrichment', 'company_lookup'],
                    'temp' => 0.50,
                    'is_active' => false, // 1 inactive -> 5 / 6 active!
                    'target_completed' => 70,
                ],
            ];

            $agentMap = [];
            foreach ($agentsMeta as $meta) {
                $agent = AgentConfig::updateOrCreate(
                    [
                        'org_id' => $orgId,
                        'agent_name' => $meta['name'],
                    ],
                    [
                        'model' => 'gemma4:e2b',
                        'provider' => 'ollama',
                        'gemini_model' => 'gemini-1.5-flash',
                        'ollama_url' => 'http://192.168.1.99:11434',
                        'ollama_model' => 'gemma4:e2b',
                        'system_prompt' => $meta['system_prompt'],
                        'tools_enabled' => $meta['tools'],
                        'temperature' => $meta['temp'],
                        'is_active' => $meta['is_active'],
                    ]
                );
                $agentMap[$meta['name']] = [
                    'id' => $agent->id,
                    'target' => $meta['target_completed'],
                ];
            }

            // 2. Check if agent runs already seeded for this org
            $existingRunsCount = DB::table('agent_runs')->where('org_id', $orgId)->count();
            if ($existingRunsCount < 1000) {
                // Generate 1,590 completed runs + 120 failed runs = 1,710 total runs (93% success rate)
                $samplePrompts = [
                    'Deal Scorer' => [
                        'Nilai skor prospek untuk tawaran e-Invois bernilai RM 65,000.',
                        'Kira kebarangkalian penutupan deal dengan Syarikat Maju Holdings.',
                        'Analisis data interaksi lead WhatsApp bagi menilai kualiti intent pembeli.',
                        'Kenal pasti faktor penentu kelayakan bagi tender penyelesaian digital.',
                    ],
                    'Follow-up Writer' => [
                        'Tulis draf mesej WhatsApp susulan bagi sesi demo sistem semalam.',
                        'Jana emel jemputan mesyuarat semakan cadangan teknikal kewangan.',
                        'Hantar peringatan mesra tarikh akhir tawaran diskaun langganan.',
                        'Rangka jawapan pantas bagi soalan integrasi sistem pelanggan.',
                    ],
                    'Churn Predictor' => [
                        'Semak trend penggunaan modul HR & lejar bagi mengesan tanda churn.',
                        'Analisis kelewatan bayaran dan interaksi pengguna suku tahun ketiga.',
                        'Beri amaran risiko akaun korporat dengan log masuk menurun 40%.',
                        'Cadangkan pakej pengekalan (retention) berasaskan corak penggunaan.',
                    ],
                    'Meeting Summarizer' => [
                        'Rumuskan transkrip perbincangan jualan dengan Kumpulan Senibina Utara.',
                        'Ekstrak tindakan susulan (action items) dari minit mesyuarat zoom tadi.',
                        'Susun ringkasan kehendak pelanggan untuk pasukan pelaksanaan.',
                        'Katalogkan maklum balas harga dan cadangan modul tambahan.',
                    ],
                    'Pipeline Forecaster' => [
                        'Unjurkan hasil jualan saluran CRM bagi bulan Oktober 2026.',
                        'Analisis halaju pergerakan transaksi dari Proposal ke Negotiation.',
                        'Bandingkan sasaran hasil kuartal dengan jangkaan nilai wajaran.',
                        'Kira purata kitaran jualan bagi pakej korporat RM 50k ke atas.',
                    ],
                    'Data Enricher' => [
                        'Cari maklumat SSM dan saiz pekerja bagi syarikat Selangor Tech.',
                        'Lengkapkan profil pengarah urusan dan industri sasaran.',
                        'Verifikasi nombor telefon rasmi dan domain korporat prospek.',
                        'Kemaskini alamat ibu pejabat dan cawangan operasi pelanggan.',
                    ],
                ];

                $now = Carbon::now();
                $runsToInsert = [];

                // Weekly distribution of completed tasks over 8 weeks summing to 1,590:
                // Wk1: 90, Wk2: 120, Wk3: 150, Wk4: 185, Wk5: 225, Wk6: 255, Wk7: 280, Wk8: 285 = 1,590
                $weeklyCompletedTarget = [90, 120, 150, 185, 225, 255, 280, 285];

                // Build completed tasks per agent:
                // Deal Scorer: 480, Follow-up Writer: 390, Churn Predictor: 290, Meeting Summarizer: 220, Pipeline Forecaster: 140, Data Enricher: 70
                $agentTaskPool = [];
                foreach ($agentMap as $agentName => $data) {
                    for ($k = 0; $k < $data['target']; $k++) {
                        $agentTaskPool[] = [
                            'agent_name' => $agentName,
                            'agent_id' => $data['id'],
                        ];
                    }
                }
                shuffle($agentTaskPool);

                $poolIndex = 0;
                for ($wk = 0; $wk < 8; $wk++) {
                    $weeksAgo = 7 - $wk;
                    $targetCount = $weeklyCompletedTarget[$wk];

                    for ($i = 0; $i < $targetCount && $poolIndex < count($agentTaskPool); $i++) {
                        $item = $agentTaskPool[$poolIndex++];
                        $agentName = $item['agent_name'];
                        $agentId = $item['agent_id'];

                        $prompts = $samplePrompts[$agentName] ?? ['Analisis data CRM'];
                        $prompt = $prompts[array_rand($prompts)];

                        $randomDay = rand(0, 6);
                        $randomHour = rand(8, 21);
                        $randomMinute = rand(0, 59);
                        $createdAt = $now->copy()->subWeeks($weeksAgo)->startOfWeek()->addDays($randomDay)->setHour($randomHour)->setMinute($randomMinute);

                        $runsToInsert[] = [
                            'id' => (string) Str::uuid(),
                            'org_id' => $orgId,
                            'agent_config_id' => $agentId,
                            'thread_id' => null,
                            'prompt' => $prompt,
                            'output' => "Analisis selesai. Tindakan automatik direkodkan dengan jayanya oleh {$agentName}.",
                            'status' => 'completed',
                            'tokens_used' => rand(180, 650),
                            'created_at' => $createdAt,
                            'updated_at' => $createdAt,
                        ];
                    }
                }

                // Add 120 failed runs spread across the 8 weeks (giving 1,590 / (1,590 + 120) = 93.0% success rate!)
                for ($f = 0; $f < 120; $f++) {
                    $weeksAgo = rand(0, 7);
                    $randomAgentName = array_rand($agentMap);
                    $agentId = $agentMap[$randomAgentName]['id'];
                    $prompts = $samplePrompts[$randomAgentName] ?? ['Tugasan automasi'];
                    $prompt = $prompts[array_rand($prompts)];

                    $createdAt = $now->copy()->subWeeks($weeksAgo)->startOfWeek()->addDays(rand(0, 6))->setHour(rand(8, 21));

                    $runsToInsert[] = [
                        'id' => (string) Str::uuid(),
                        'org_id' => $orgId,
                        'agent_config_id' => $agentId,
                        'thread_id' => null,
                        'prompt' => $prompt,
                        'output' => 'Ralat API sementara: masa tamat sambungan pelayan atau kekangan kuota.',
                        'status' => 'failed',
                        'tokens_used' => rand(40, 110),
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ];
                }

                foreach (array_chunk($runsToInsert, 400) as $chunk) {
                    DB::table('agent_runs')->insert($chunk);
                }
            }

            // 3. Seed Realistic CRM Contacts and Deals if less than 8 deals exist
            if (CrmDeal::where('org_id', $orgId)->count() < 8) {
                $contactsData = [
                    ['name' => 'Dato Seri Azlan Hashim', 'company' => 'Mega Holdings Berhad', 'email' => 'azlan@megaholdings.my', 'phone' => '+6012-3344556'],
                    ['name' => 'Puan Hajah Rohani Yusof', 'company' => 'Kencana Retail Group', 'email' => 'rohani@kencanaretail.com.my', 'phone' => '+6013-9988112'],
                    ['name' => 'Dr. Chen Wei Kang', 'company' => 'Apex Health Solutions', 'email' => 'chen@apexhealth.my', 'phone' => '+6016-7788990'],
                    ['name' => 'Encik Shahril Nizam', 'company' => 'Pantai Timur Logistics', 'email' => 'shahril@ptlogistics.com', 'phone' => '+6019-5544332'],
                    ['name' => 'Cik Nadia Qistina', 'company' => 'Venture Capital Asia MY', 'email' => 'nadia@vcasiagroup.my', 'phone' => '+6017-8877665'],
                    ['name' => 'Tuan Haji Badrul Zaman', 'company' => 'Badrul Zaman Engineering', 'email' => 'badrul@bze.com.my', 'phone' => '+6012-4455667'],
                ];

                $contactModels = [];
                foreach ($contactsData as $cd) {
                    $c = CrmContact::firstOrCreate(
                        ['org_id' => $orgId, 'email' => $cd['email']],
                        [
                            'name' => $cd['name'],
                            'company' => $cd['company'],
                            'phone' => $cd['phone'],
                        ]
                    );
                    $contactModels[] = $c;
                }

                $dealsData = [
                    ['title' => 'Integrasi Sistem e-Invois LHDN ERP Enterprise', 'amount' => 65000.00, 'stage' => 'negotiation', 'prob' => 80],
                    ['title' => 'Pembangunan Portal B2B Pembekal & Vendor', 'amount' => 48000.00, 'stage' => 'proposal', 'prob' => 65],
                    ['title' => 'Langganan Tahunan Kuasa AI HR & Payroll Lekiu', 'amount' => 28800.00, 'stage' => 'won', 'prob' => 100],
                    ['title' => 'Automasi Inventori & POS Runcit 10 Cawangan', 'amount' => 52000.00, 'stage' => 'qualified', 'prob' => 50],
                    ['title' => 'Sistem Tempahan & Tiket Digital Acara Korporat', 'amount' => 18500.00, 'stage' => 'lead', 'prob' => 30],
                    ['title' => 'Pakej Transformasi Digital Perusahaan Mikro', 'amount' => 12000.00, 'stage' => 'won', 'prob' => 100],
                    ['title' => 'Konsultasi Keselamatan Siber & Audit Pematuhan PDPA', 'amount' => 35000.00, 'stage' => 'won', 'prob' => 100],
                    ['title' => 'Penyelarasan API WhatsApp Gateway & Saluran Jualan', 'amount' => 22000.00, 'stage' => 'lost', 'prob' => 0],
                    ['title' => 'Sistem Pengurusan Armada & Telematik Logistik', 'amount' => 84000.00, 'stage' => 'proposal', 'prob' => 60],
                    ['title' => 'Automasi Aliran Kerja Kewangan & Pemadanan Resit', 'amount' => 16500.00, 'stage' => 'qualified', 'prob' => 45],
                    ['title' => 'Pakej Papan Iklan AI & Enjin Prospek Jebat', 'amount' => 45000.00, 'stage' => 'negotiation', 'prob' => 75],
                    ['title' => 'Langganan Sistem Penggajian Lekiu Enterprise', 'amount' => 32000.00, 'stage' => 'won', 'prob' => 100],
                ];

                foreach ($dealsData as $i => $dd) {
                    $assignedContact = $contactModels[$i % count($contactModels)];
                    CrmDeal::firstOrCreate(
                        ['org_id' => $orgId, 'title' => $dd['title']],
                        [
                            'contact_id' => $assignedContact->id,
                            'amount' => $dd['amount'],
                            'stage_id' => $dd['stage'],
                            'probability' => $dd['prob'],
                            'expected_close_date' => Carbon::now()->addDays(rand(7, 45))->toDateString(),
                        ]
                    );
                }
            }

            // 4. Seed CRM Appointments (Matching Screenshot: Wed 14 Oct, Thu 15 Oct, Fri 16 Oct)
            $aisyahContact = CrmContact::firstOrCreate(
                ['org_id' => $orgId, 'email' => 'aisyah.rahim@globaltech.my'],
                [
                    'name' => 'Aisyah Rahim',
                    'company' => 'Global Technology Holdings',
                    'phone' => '+6012-8877221',
                ]
            );

            $faizContact = CrmContact::firstOrCreate(
                ['org_id' => $orgId, 'email' => 'faiz.hakim@perdanalogistics.com'],
                [
                    'name' => 'Faiz Hakim',
                    'company' => 'Perdana Logistics Sdn Bhd',
                    'phone' => '+6013-6655443',
                ]
            );

            $nurulContact = CrmContact::firstOrCreate(
                ['org_id' => $orgId, 'email' => 'nurul.huda@kencana.my'],
                [
                    'name' => 'Nurul Huda',
                    'company' => 'Kencana Retail Group',
                    'phone' => '+6017-3322998',
                ]
            );

            \App\Models\CrmAppointment::updateOrCreate(
                [
                    'org_id' => $orgId,
                    'contact_name' => 'Aisyah Rahim',
                    'title' => 'Discovery call',
                ],
                [
                    'contact_id' => $aisyahContact->id,
                    'type' => 'appointment',
                    'scheduled_at' => Carbon::create(2026, 10, 14, 4, 0, 0),
                    'duration_minutes' => 45,
                    'location' => 'Google Meet',
                    'status' => 'scheduled',
                    'notes' => 'Perbincangan awal kehendak sistem ERP dan pengautomatan aliran kerja.',
                ]
            );

            \App\Models\CrmAppointment::updateOrCreate(
                [
                    'org_id' => $orgId,
                    'contact_name' => 'Faiz Hakim',
                    'title' => 'Product demo',
                ],
                [
                    'contact_id' => $faizContact->id,
                    'type' => 'appointment',
                    'scheduled_at' => Carbon::create(2026, 10, 15, 1, 0, 0),
                    'duration_minutes' => 60,
                    'location' => 'Google Meet & Demo Live',
                    'status' => 'scheduled',
                    'notes' => 'Demonstrasi modul lejar am Bendahara dan papan iklan Jebat.',
                ]
            );

            \App\Models\CrmAppointment::updateOrCreate(
                [
                    'org_id' => $orgId,
                    'contact_name' => 'Nurul Huda',
                    'title' => 'Follow-up',
                ],
                [
                    'contact_id' => $nurulContact->id,
                    'type' => 'appointment',
                    'scheduled_at' => Carbon::create(2026, 10, 16, 23, 0, 0),
                    'duration_minutes' => 30,
                    'location' => 'WhatsApp Call',
                    'status' => 'scheduled',
                    'notes' => 'Susulan semakan cadangan sebut harga dan pengesahan bajet.',
                ]
            );
        }
    }
}
