<?php

namespace Database\Seeders;

use App\Models\Campaign;
use App\Models\Lead;
use App\Models\Org;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class JebatDemoSeeder extends Seeder
{
    public function run(): void
    {
        $orgs = Org::all();

        foreach ($orgs as $org) {
            // Check if already seeded with full dataset
            if (Lead::where('org_id', $org->id)->count() >= 342) {
                continue;
            }

            // 1. Create the 5 Top Campaigns summing to RM 5,007.58 & 342 leads
            $campaignData = [
                [
                    'name' => 'Lead Magnet - eBook AI Automasi',
                    'platform' => 'facebook',
                    'budget' => 800.00,
                    'spend' => 414.80,
                    'status' => 'active',
                    'impressions' => 14250,
                    'clicks' => 1120,
                    'conversions' => 61,
                    'channel' => 'facebook',
                    'lead_count' => 61,
                ],
                [
                    'name' => 'Webinar Automasi PKS & e-Invois',
                    'platform' => 'whatsapp',
                    'budget' => 1500.00,
                    'spend' => 940.80,
                    'status' => 'active',
                    'impressions' => 18500,
                    'clicks' => 1430,
                    'conversions' => 84,
                    'channel' => 'whatsapp',
                    'lead_count' => 84,
                ],
                [
                    'name' => 'Retargeting Tawaran Merdeka VIP',
                    'platform' => 'instagram',
                    'budget' => 1000.00,
                    'spend' => 652.50,
                    'status' => 'active',
                    'impressions' => 9800,
                    'clicks' => 820,
                    'conversions' => 45,
                    'channel' => 'instagram',
                    'lead_count' => 45,
                ],
                [
                    'name' => 'TikTok Viral - Demo Sistem 60s',
                    'platform' => 'tiktok',
                    'budget' => 2000.00,
                    'spend' => 1783.60,
                    'status' => 'active',
                    'impressions' => 32400,
                    'clicks' => 2850,
                    'conversions' => 98,
                    'channel' => 'tiktok',
                    'lead_count' => 98,
                ],
                [
                    'name' => 'Kempen Kesedaran e-Invois LHDN 2026',
                    'platform' => 'facebook',
                    'budget' => 2000.00,
                    'spend' => 1215.88,
                    'status' => 'completed',
                    'impressions' => 24000,
                    'clicks' => 1960,
                    'conversions' => 54,
                    'channel' => 'whatsapp',
                    'lead_count' => 54,
                ],
            ];

            $campaigns = [];
            foreach ($campaignData as $c) {
                $camp = Campaign::updateOrCreate(
                    ['org_id' => $org->id, 'name' => $c['name']],
                    [
                        'platform' => $c['platform'],
                        'budget' => $c['budget'],
                        'spend' => $c['spend'],
                        'status' => $c['status'],
                        'impressions' => $c['impressions'],
                        'clicks' => $c['clicks'],
                        'conversions' => $c['conversions'],
                    ]
                );
                $campaigns[] = [
                    'model' => $camp,
                    'target_leads' => $c['lead_count'],
                    'primary_channel' => $c['channel'],
                ];
            }

            // 2. Generate Malaysian Leads
            $malaysianNames = [
                'Mohd Zaki Ismail', 'Nurul Ain Hisham', 'Farhan Roslan', 'Siti Fatimah Bakar',
                'Khairul Anwar', 'Tan Wei Loon', 'Chong Mei Ling', 'Suresh Kumar',
                'Kavitha Arumugam', 'Ahmad Danial', 'Syahirah Razak', 'Hafizuddin Yusof',
                'Azman Hashim', 'Diana Abdullah', 'Irfan Hakim', 'Natasha Qistina',
                'Muhammad Faizal', 'Lee Jian Hao', 'Muthu Raman', 'Noraini Jamil',
                'Fadzil Ramli', 'Zainab Mokhtar', 'Shahrul Nizam', 'Amira Sofea',
                'Alif Syafiq', 'Hazim Nazri', 'Nadia Sulaiman', 'Razif Baharom',
                'Wong Chee Keong', 'Anand Raj', 'Suraya Daud', 'Syed Muzaffar',
                'Fatin Liyana', 'Hariz Haikal', 'Badrul Hisham', 'Nurul Izzah',
                'Adam Harith', 'Siti Sarah', 'Danish Iskandar', 'Aisyah Humaira',
            ];

            $leadStatuses = [
                // Distribution: 78 new, 106 contacted, 62 qualified, 48 booked, 48 won = 342 total
                'won' => 48,
                'booked' => 48,
                'qualified' => 62,
                'contacted' => 106,
                'new' => 78,
            ];

            $statusList = [];
            foreach ($leadStatuses as $status => $count) {
                for ($i = 0; $i < $count; $i++) {
                    $statusList[] = $status;
                }
            }
            shuffle($statusList);

            // Channel allocation: WhatsApp: 138, Facebook: 112, Instagram: 58, TikTok: 34 = 342 total
            $channelPool = array_merge(
                array_fill(0, 138, 'whatsapp'),
                array_fill(0, 112, 'facebook'),
                array_fill(0, 58, 'instagram'),
                array_fill(0, 34, 'tiktok')
            );
            shuffle($channelPool);

            // Weekly distribution over 8 weeks: Wk1: 24, Wk2: 28, Wk3: 30, Wk4: 36, Wk5: 42, Wk6: 45, Wk7: 48, Wk8: 54 = 342
            $weeklyCounts = [24, 28, 30, 36, 42, 45, 48, 54];
            $leadIndex = 0;

            for ($wk = 0; $wk < 8; $wk++) {
                $weeksAgo = 7 - $wk;
                $countInWeek = $weeklyCounts[$wk];

                for ($i = 0; $i < $countInWeek; $i++) {
                    $name = $malaysianNames[($leadIndex + $i) % count($malaysianNames)] . ' ' . ($leadIndex + 1);
                    $emailSlug = Str::slug(explode(' ', $name)[0] . (rand(10, 99)));
                    $channel = $channelPool[$leadIndex % count($channelPool)];
                    $campIndex = $leadIndex % count($campaigns);
                    $campModel = $campaigns[$campIndex]['model'];
                    $status = $statusList[$leadIndex % count($statusList)];

                    $randomDay = rand(0, 6);
                    $randomHour = rand(9, 22);
                    $createdAt = Carbon::now()->subWeeks($weeksAgo)->startOfWeek()->addDays($randomDay)->setHour($randomHour)->setMinute(rand(0, 59));

                    Lead::create([
                        'id' => (string) Str::uuid(),
                        'org_id' => $org->id,
                        'campaign_id' => $campModel->id,
                        'name' => $name,
                        'email' => "{$emailSlug}@syarikat.my",
                        'phone' => '+601' . rand(1, 9) . '-' . rand(1000000, 9999999),
                        'source' => $channel,
                        'status' => $status,
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]);

                    $leadIndex++;
                }
            }
        }
    }
}
