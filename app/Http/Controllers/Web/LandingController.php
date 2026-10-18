<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LandingController extends Controller
{
    /**
     * Paparkan laman pendaratan utama KerjaAI.
     */
    public function index()
    {
        $modules = [
            [
                'code' => 'tuah',
                'name' => 'Tuah AI',
                'subtitle' => 'Pusat Kawalan & Co-Pilot C-Suite',
                'description' => 'Ejen kepimpinan kecerdasan pintar untuk pengasas dan pengurusan. Taklimat harian CEO, analisis silang modul masa nyata dan sembang berpandukan guardrails perniagaan.',
                'icon' => 'sparkles',
                'badge' => 'Teras AI',
                'color' => 'indigo',
                'route' => 'command.index',
                'apps' => [
                    ['name' => 'CEO Daily Briefing', 'desc' => 'Ringkasan prestasi syarikat setiap pagi'],
                    ['name' => 'Tanya Tuah (Voice & Chat)', 'desc' => 'Berbual dengan sistem perniagaan anda secara langsung'],
                    ['name' => 'Model Fleksibel', 'desc' => 'Sokongan Ollama tempatan (100% percuma) & Google Gemini'],
                    ['name' => 'Analisis Silang Modul', 'desc' => 'Membaca data jualan, staf dan akaun serentak'],
                ]
            ],
            [
                'code' => 'jebat',
                'name' => 'Jebat',
                'subtitle' => 'Pemasaran, Prospek & Studio Iklan',
                'description' => 'Lancar kempen pemasaran berbilang platform dan jana salinan iklan berkesan menggunakan Jebat Ad Studio untuk produk Kasturi mahupun tawaran jawatan Lekiu.',
                'icon' => 'megaphone',
                'badge' => 'Pemasaran',
                'color' => 'blue',
                'route' => 'reach.index',
                'apps' => [
                    ['name' => 'Jebat Ad Studio', 'desc' => 'Penjana copywriting iklan pintar untuk Meta, TikTok, WhatsApp & Google'],
                    ['name' => 'Magic Ads Autopilot', 'desc' => 'Pantau ROI dan hentikan kempen membazir secara automatik'],
                    ['name' => 'Saluran Prospek (Leads)', 'desc' => 'Tangkap prospek dan agihkan terus ke corong jualan Kasturi'],
                    ['name' => 'Analisis Metrik Iklan', 'desc' => 'Kiraan Kos Setiap Prospek (CPL) dan pulangan perbelanjaan iklan (ROAS)'],
                ]
            ],
            [
                'code' => 'kasturi',
                'name' => 'Kasturi',
                'subtitle' => 'Jualan, CRM & Katalog Produk',
                'description' => 'Dari klik pertama sehingga penutupan urus niaga. Susun corong jualan visual Kanban, urus pangkalan data pelanggan, serta selenggara katalog produk & perkhidmatan.',
                'icon' => 'user-group',
                'badge' => 'Jualan & CRM',
                'color' => 'emerald',
                'route' => 'crm.index',
                'apps' => [
                    ['name' => 'Corong Urus Niaga (Deals)', 'desc' => 'Visualisasikan perjalanan prospek dari fasa rundingan ke jualan berjaya'],
                    ['name' => 'Katalog Produk & Servis', 'desc' => 'Urus SKU, harga jualan, harga kos, margin dan kuantiti stok'],
                    ['name' => 'Pintasan Iklan ke Jebat', 'desc' => 'Hantar produk terus ke Ad Studio untuk menjana promosi pantas'],
                    ['name' => 'Pangkalan Data Pelanggan', 'desc' => 'Rekod profil prospek, syarikat dan sejarah interaksi teratur'],
                ]
            ],
            [
                'code' => 'lekiu',
                'name' => 'Lekiu',
                'subtitle' => 'Modal Insan, Penggajian & Pengambilan Staf',
                'description' => 'Sistem HR lengkap patuh akta statutori Malaysia: rekod pekerja, jam masuk GPS, cuti, penggajian automatik, serta portal pengiklanan jawatan (Hire / ATS).',
                'icon' => 'identification',
                'badge' => 'HR & Pengambilan',
                'color' => 'cyan',
                'route' => 'people.index',
                'apps' => [
                    ['name' => 'Penggajian Statutori Malaysia', 'desc' => 'Pengiraan automatik caruman KWSP (12%/13% & 11%), PERKESO, EIS & PCB'],
                    ['name' => 'Portal Pengambilan Staf (Hire)', 'desc' => 'Siarkan iklan jawatan kosong, kumpul resume calon dan corong temu duga'],
                    ['name' => 'Tapisan Resume Pintar', 'desc' => 'Skor padanan kemahiran calon mengikut deskripsi tugasan jawatan'],
                    ['name' => 'Kehadiran GPS & Pengurusan Cuti', 'desc' => 'Rekod lokasi masuk kerja selamat dan kelulusan cuti pekerja'],
                ]
            ],
            [
                'code' => 'lekir',
                'name' => 'Lekir',
                'subtitle' => 'Kewangan, Perakaunan & e-Invois LHDN',
                'description' => 'Penyandang amanah perbendaharaan syarikat: sebut harga ke invois jualan, perbelanjaan, cukai SST, lejar am berimbang Debit = Kredit, dan kesediaan e-Invois MyInvois LHDN.',
                'icon' => 'banknotes',
                'badge' => 'Kewangan & Cukai',
                'color' => 'amber',
                'route' => 'finance.index',
                'apps' => [
                    ['name' => 'Sebut Harga ke Invois', 'desc' => 'Jana sebut harga profesional dan tukar terus ke invois rasmi'],
                    ['name' => 'Kesediaan e-Invois MyInvois', 'desc' => 'Struktur data patuh garis panduan portal MyInvois LHDN terkini'],
                    ['name' => 'Cukai Perkhidmatan SST', 'desc' => 'Pengiraan dan pembundaran automatik kadar cukai SST 6% dan 8%'],
                    ['name' => 'Lejar Am Berimbang & Resit', 'desc' => 'Catatan jurnal berkembar automatik (Debit = Kredit) & Shoebox resit'],
                ]
            ]
        ];

        $faqs = [
            [
                'q' => 'Apakah itu KerjaAI?',
                'a' => 'KerjaAI ialah Sistem Operasi Perniagaan (Business Operating System) berkuasa AI pertama di Malaysia yang dibina secara 100% Sumber Terbuka (Open Source). Ia menggabungkan pemasaran & studio iklan (Jebat), jualan & katalog produk (Kasturi), modal insan & pengambilan staf (Lekiu), serta kewangan, perakaunan & e-Invois (Lekir) di bawah naungan pusat kawalan eksekutif Tuah AI.'
            ],
            [
                'q' => 'Adakah KerjaAI benar-benar percuma untuk digunakan?',
                'a' => 'Ya, 100% percuma di bawah lesen sumber terbuka. Anda boleh memasangnya pada pelayan (VPS/cloud) anda sendiri tanpa bayaran lesen bulanan per-pengguna yang membebankan syarikat yang sedang berkembang.'
            ],
            [
                'q' => 'Bagaimanakah enjin AI Tuah dikuasakan tanpa kos tersembunyi?',
                'a' => 'KerjaAI direka fleksibel mengikut keperluan anda: anda boleh menyambungkan model LLM tempatan percuma seperti Ollama (Llama 3 / Mistral) yang berjalan terus pada pelayan anda tanpa caj API, atau memasukkan kunci API Google Gemini / OpenAI anda sendiri secara terus tanpa mark-up harga.'
            ],
            [
                'q' => 'Adakah sistem ini patuh kepada undang-undang dan piawaian Malaysia?',
                'a' => 'Ya. Modul Lekiu (HR & Gaji) mematuhi peraturan caruman KWSP (EPF), PERKESO (SOCSO), EIS, dan PCB. Modul Lekir (Kewangan & Percukaian) pula dibina dengan struktur data yang bersedia untuk integrasi e-Invois MyInvois LHDN dan penyata cukai SST.'
            ],
            [
                'q' => 'Bolehkah saya menyesuaikan sistem ini mengikut proses syarikat saya sendiri?',
                'a' => 'Pasti. Disebabkan kod sumbernya berasaskan Laravel 13 dan PostgreSQL yang bersih dan standard, pasukan jurutera perisian atau pembangun dalaman anda bebas mengubah suai, menambah modul atau menyepadukan dengan sistem sedia ada tanpa sebarang kekangan vendor.'
            ],
            [
                'q' => 'Apakah spesifikasi pelayan yang disyorkan untuk memasang KerjaAI?',
                'a' => 'Untuk operasi perniagaan biasa, VPS Ubuntu dengan 2 vCPU dan 4GB RAM sudah lebih daripada memadai untuk mengendalikan puluhan staf dan beribu-ribu rekod. Jika anda ingin menjalankan model AI tempatan (Ollama), disyorkan spesifikasi dengan 8GB-16GB RAM atau GPU.'
            ]
        ];

        return view('landing', compact('modules', 'faqs'));
    }
}
