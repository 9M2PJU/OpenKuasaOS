<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LandingController extends Controller
{
    /**
     * Paparkan laman pendaratan utama OpenKuasa.
     */
    public function index()
    {
        $modules = [
            [
                'code' => 'tuah',
                'name' => 'Tuah AI',
                'subtitle' => 'Pusat Kawalan & Co-Pilot C-Suite',
                'description' => 'Ejen kepimpinan kecerdasan pintar untuk pengasas dan pengurusan. Taklimat harian CEO, analisis prestasi perniagaan dan perbualan suara masa nyata.',
                'icon' => 'sparkles',
                'badge' => 'Teras AI',
                'color' => 'indigo',
                'route' => 'command.index',
                'apps' => [
                    ['name' => 'CEO Daily Briefing', 'desc' => 'Ringkasan prestasi syarikat setiap pagi'],
                    ['name' => 'Tanya Tuah (Voice & Chat)', 'desc' => 'Berbual dengan sistem perniagaan anda secara langsung'],
                    ['name' => 'Model Fleksibel', 'desc' => 'Sokongan Ollama tempatan & Google Gemini tanpa kos lesen tambahan'],
                    ['name' => 'Analisis Silang Modul', 'desc' => 'Membaca data jualan, staf dan akaun serentak'],
                ]
            ],
            [
                'code' => 'jebat',
                'name' => 'Jebat',
                'subtitle' => 'Pemasaran & Pengiklanan Pintar',
                'description' => 'Lancar dan optimumkan kempen iklan berbilang platform dengan bantuan AI. Autopilot pemantau bajet dan penjanaan salinan iklan kreatif.',
                'icon' => 'megaphone',
                'badge' => 'Marketing',
                'color' => 'blue',
                'route' => 'reach.index',
                'apps' => [
                    ['name' => 'Magic Ads Autopilot', 'desc' => 'Pantau ROI dan hentikan iklan yang membazir secara automatik'],
                    ['name' => 'Creative Bank', 'desc' => 'Jana salinan copywriting dan cadangan visual berimpak'],
                    ['name' => 'Automasi Kempen', 'desc' => 'Pemasaran bersasar merentasi WhatsApp, SMS dan Emel'],
                    ['name' => 'Analisis Metrik Iklan', 'desc' => 'Kiraan Kos Setiap Prospek (CPL) dan pulangan perbelanjaan iklan (ROAS)'],
                ]
            ],
            [
                'code' => 'kasturi',
                'name' => 'Kasturi',
                'subtitle' => 'CRM & Corong Jualan Pintar',
                'description' => 'Dari klik pertama sehingga penutupan urus niaga. Tangkap prospek dengan borang responsif dan susun corong jualan dengan saluran visual tersusun.',
                'icon' => 'user-group',
                'badge' => 'Sales & CRM',
                'color' => 'emerald',
                'route' => 'crm.index',
                'apps' => [
                    ['name' => 'Borang Tangkapan Prospek', 'desc' => 'Borang web pintar untuk kumpul dan skor prospek segera'],
                    ['name' => 'Corong Urus Niaga (Deals)', 'desc' => 'Visualisasikan perjalanan prospek dari fasa rundingan ke jualan berjaya'],
                    ['name' => 'Kelayakan Prospek AI', 'desc' => 'Tapis keperluan pelanggan dan agihkan tugas jualan automatik'],
                    ['name' => 'WhatsApp Lead Connect', 'desc' => 'Integrasi sembang langsung untuk tindakan susulan sepantas kilat'],
                ]
            ],
            [
                'code' => 'lekiu',
                'name' => 'Lekiu',
                'subtitle' => 'Pengurusan Pekerja, HR & Gaji Tempatan',
                'description' => 'Sistem HR lengkap patuh akta buruh Malaysia. Dari rekod peribadi pekerja, jam masuk GPS, pengurusan cuti, tuntutan elaun sehingga slip gaji automatik.',
                'icon' => 'identification',
                'badge' => 'HR & Payroll',
                'color' => 'cyan',
                'route' => 'people.index',
                'apps' => [
                    ['name' => 'HRIS & Dokumen Staf', 'desc' => 'Pangkalan data digital pekerja dengan rekod kontrak teratur'],
                    ['name' => 'Kehadiran GPS & Jam Masuk', 'desc' => 'Pengecaman lokasi selamat untuk waktu kerja harian dan syif'],
                    ['name' => 'Slip Gaji Berkanun Malaysia', 'desc' => 'Pengiraan automatik caruman KWSP, PERKESO, SIP (EIS) & PCB'],
                    ['name' => 'Permohonan Cuti & Tuntutan', 'desc' => 'Proses kelulusan pantas tanpa borang kertas yang berselerak'],
                ]
            ],
            [
                'code' => 'lekir',
                'name' => 'Lekir',
                'subtitle' => 'Pengambilan Pekerja & AI Talent Screener',
                'description' => 'Perekrut pintar 24/7 untuk organisasi berkembang. Siarkan iklan kerja, tapis resume calon secara automatik dan jadualkan temuduga dalam satu corong.',
                'icon' => 'briefcase',
                'badge' => 'Hiring',
                'color' => 'purple',
                'route' => 'hire.index',
                'apps' => [
                    ['name' => 'Portal Iklan Jawatan', 'desc' => 'Cipta dan siarkan kekosongan jawatan dengan borang permohonan tersuai'],
                    ['name' => 'Tapisan Resume Pintar', 'desc' => 'Skor padanan kemahiran resume calon mengikut keperluan jawatan'],
                    ['name' => 'Corong Pengambilan Calon', 'desc' => 'Aliran visual dari permohonan baru, temuduga, ujian ke tawaran kerja'],
                    ['name' => 'Arkib Bakat Berpusat', 'desc' => 'Simpan bank bakat untuk peluang pengambilan masa hadapan'],
                ]
            ],
            [
                'code' => 'bendahara',
                'name' => 'Bendahara',
                'subtitle' => 'Kewangan, Perakaunan, Cukai & e-Invois',
                'description' => 'Pengurusan tunai, sebut harga ke invois, lejar am, pengimbas resit Shoebox digital dan kesediaan integrasi MyInvois LHDN.',
                'icon' => 'banknotes',
                'badge' => 'Finance & Tax',
                'color' => 'amber',
                'route' => 'finance.index',
                'apps' => [
                    ['name' => 'Sebut Harga ke Invois', 'desc' => 'Jana sebut harga profesional dan tukar ke invois dengan satu klik'],
                    ['name' => 'Kesediaan e-Invois LHDN', 'desc' => 'Struktur medan patuh garis panduan MyInvois LHDN terkini'],
                    ['name' => 'Digital Shoebox Resit', 'desc' => 'Simpan dan kategorikan resit perbelanjaan untuk rekod cukai kemas'],
                    ['name' => 'Cetak PDF & Hantar Terus', 'desc' => 'Cetak dokumen atau kongsi terus ke WhatsApp dan Emel pelanggan'],
                ]
            ]
        ];

        $faqs = [
            [
                'q' => 'Apakah itu OpenKuasa?',
                'a' => 'OpenKuasa ialah Sistem Operasi Perniagaan (Business Operating System) berkuasa AI pertama di Malaysia yang dibina secara 100% Sumber Terbuka (Open Source). Ia menggabungkan pemasaran (Jebat), jualan (Kasturi), pengurusan pekerja & gaji (Lekiu), pengambilan staf (Lekir), kewangan & perakaunan (Bendahara) di bawah satu bumbung dengan bantuan ejen Tuah AI.'
            ],
            [
                'q' => 'Adakah OpenKuasa benar-benar percuma untuk digunakan?',
                'a' => 'Ya, 100% percuma di bawah lesen sumber terbuka. Anda boleh memasangnya pada pelayan (VPS/cloud) anda sendiri tanpa bayaran lesen bulanan per-pengguna yang membebankan syarikat yang sedang berkembang.'
            ],
            [
                'q' => 'Bagaimanakah enjin AI Tuah dikuasakan tanpa kos tersembunyi?',
                'a' => 'OpenKuasa direka fleksibel mengikut keperluan anda: anda boleh menyambungkan model LLM tempatan percuma seperti Ollama (Llama 3 / Mistral) yang berjalan terus pada pelayan anda tanpa caj API, atau memasukkan kunci API Google Gemini / OpenAI anda sendiri secara terus tanpa mark-up harga.'
            ],
            [
                'q' => 'Adakah sistem ini patuh kepada undang-undang dan piawaian Malaysia?',
                'a' => 'Ya. Modul Lekiu (HR & Gaji) mematuhi peraturan caruman KWSP (EPF), PERKESO (SOCSO), EIS, dan PCB. Modul Bendahara pula dibina dengan struktur data yang bersedia untuk integrasi e-Invois MyInvois LHDN dan penyata cukai SST.'
            ],
            [
                'q' => 'Bolehkah saya menyesuaikan sistem ini mengikut proses syarikat saya sendiri?',
                'a' => 'Pasti. Disebabkan kod sumbernya berasaskan Laravel 13 dan PostgreSQL yang bersih dan standard, pasukan jurutera perisian atau pembangun dalaman anda bebas mengubah suai, menambah modul atau menyepadukan dengan sistem sedia ada tanpa sebarang kekangan vendor.'
            ],
            [
                'q' => 'Apakah spesifikasi pelayan yang disyorkan untuk memasang OpenKuasa?',
                'a' => 'Untuk operasi perniagaan biasa, VPS Ubuntu dengan 2 vCPU dan 4GB RAM sudah lebih daripada memadai untuk mengendalikan puluhan staf dan beribu-ribu rekod. Jika anda ingin menjalankan model AI tempatan (Ollama), disyorkan spesifikasi dengan 8GB-16GB RAM atau GPU.'
            ]
        ];

        return view('landing', compact('modules', 'faqs'));
    }
}
