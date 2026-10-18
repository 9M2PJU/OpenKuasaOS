# OpenKuasa OS (Laravel 13 + PostgreSQL)

**The open-source business OS for Malaysian SMEs — one self-hostable app
instead of a stack of separate CRM, HR, payroll and accounting subscriptions.**
Ads, CRM, HR, recruitment and accounting in one place, with an AI command center
on top — dikuasakan oleh **Laravel 13**, **PostgreSQL**, dan **Tailwind CSS v4** Blade templates.

OpenKuasa is crowd-sourced: it is developed in the open by its maintainers and
volunteer contributors.

**Free to self-host, always.** AGPL-3.0 Licensed.

---

## Modul Teras (Core Modules)

6 modul teras operasi perniagaan khusus untuk pasaran PKS Malaysia:

| Modul | Nama Hang | Fungsi Utama | Laluan Web (Route) |
| --- | --- | --- | --- |
| **Tuah** | Command Center | Pusat arahan pintar AI merentas semua modul perniagaan | `/command` |
| **Bendahara** | Finance & Accounting | Invois jualan, pengiraan cukai SST (6%/8%), integrasi e-Invois LHDN, lejar am berimbang (Debit = Kredit) & akaun bank | `/finance` |
| **Lekiu** | People & HR | Punch card kehadiran (Clock In/Out), permohonan & kelulusan cuti, enjin statutori gaji KWSP (11%/12%/13%), PERKESO, EIS & PCB | `/people` |
| **Kasturi** | CRM & Deals | Papan saluran urus niaga (Kanban Deals), direktori perhubungan pelanggan, dan automasi rundingan jualan | `/crm` |
| **Jebat** | Reach & Ads | Pengurusan kempen pemasaran berbilang platform (Meta, Google, TikTok), tangkapan borang prospek (leads) | `/reach` |
| **Lekir** | Hire & ATS | Iklan jawatan kosong syarikat, penjejakan saluran pemohon resume dan kalendar temu duga berjadual | `/hire` |

---

## Keperluan Sistem (System Requirements)

- **PHP 8.3+** (disyorkan PHP 8.4 / 8.5) bersama sambungan: `pdo_pgsql`, `pgsql`, `mbstring`, `bcmath`, `curl`, `dom`, `tokenizer`, `xml`.
- **PostgreSQL 15+** (berjalan pada port piawai 5432 atau 5433).
- **Composer 2.x**
- **Node.js 20+** & **npm** (untuk kompilasi aset Vite & Tailwind CSS v4).

---

## Panduan Pemasangan & Pelaksanaan (Quickstart)

### 1. Klon Repositori & Pasang Kebergantungan
```bash
git clone https://github.com/OpenKuasa/OpenKuasaOS.git
cd OpenKuasaOS

# Pasang pakej PHP & Composer
composer install

# Pasang aset frontend Vite
npm install
npm run build
```

### 2. Konfigurasi Fail Persekitaran (`.env`)
Salin templat persekitaran dan jana kunci aplikasi:
```bash
cp .env.example .env
php artisan key:generate
```

Pastikan tetapan pangkalan data PostgreSQL anda ditetapkan di dalam `.env`:
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5433
DB_DATABASE=openkuasa
DB_USERNAME=postgres
DB_PASSWORD=
```

### 3. Migrasi Skema & Masukkan Data Demo Malaysia (Seeder)
Jalankan migrasi jadual multi-tenant dan data permulaan syarikat SME Malaysia (*Rimba Ventures Sdn Bhd*):
```bash
php artisan migrate:fresh --seed
```

Data log masuk laluan demo:
- **Emel:** `admin@openkuasa.my`
- **Kata Laluan:** `password`

### 4. Mulakan Pelayan Pembangunan
```bash
php artisan serve
```
Akses sistem di pelayar web anda di: `http://localhost:8000`.

---

## Ujian Automasi & Jaminan Kualiti (Test Suite)

Semua logik statutori perniagaan Malaysia dilindungi oleh ujian unit & feature PHPUnit:
```bash
php artisan test
```

Ujian merangkumi:
- Formula caruman statutori KWSP, PERKESO, EIS & PCB LHDN (`PayrollCalculationTest`).
- Pengiraan dan pembundaran cukai SST 6% & 8% (`SstCalculationTest`).
- Pengesahan imbangan lejar am Debit = Kredit (`BendaharaPostingValidationTest`).
- Kitaran penuh invois: Draf &rarr; Pos &rarr; Bayar &rarr; Void (`InvoiceLifecycleTest`).
- Pengasingan multi-tenant mengikut UUID organisasi (`MultiTenantIsolationTest`).
- Pengesahan kuota dan pencegahan pertindihan cuti staf (`LeaveApprovalTest`).
- Kesemua 7 papan pemuka modul Blade & auth web (`WebModuleViewsTest`).

---

## Lesen

Dilesenkan di bawah [AGPL-3.0](LICENSE).
