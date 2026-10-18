GNOME_KEYRING_CONTROL=/run/user/1000/keyring

[bold cyan]⚡ [AUTO-APPROVED] Tool (run_command):[/] [dim]ls -la[/]
[bold green]✔ Finished run_command[/]

[bold cyan]⚡ [AUTO-APPROVED] Tool (view_file):[/] [dim][/]
[bold green]✔ Finished view_file[/]

[bold cyan]⚡ [AUTO-APPROVED] Tool (run_command):[/] [dim]ls -la app/Models database/migrations database/seeders routes/[/]
[bold green]✔ Finished run_command[/]

[bold cyan]⚡ [AUTO-APPROVED] Tool (view_file):[/] [dim][/]
[bold green]✔ Finished view_file[/]

[bold cyan]⚡ [AUTO-APPROVED] Tool (view_file):[/] [dim][/]
[bold green]✔ Finished view_file[/]

[bold cyan]⚡ [AUTO-APPROVED] Tool (view_file):[/] [dim][/]
[bold green]✔ Finished view_file[/]

[bold cyan]⚡ [AUTO-APPROVED] Tool (view_file):[/] [dim][/]
[bold green]✔ Finished view_file[/]

[bold cyan]⚡ [AUTO-APPROVED] Tool (view_file):[/] [dim][/]
[bold green]✔ Finished view_file[/]

[bold cyan]⚡ [AUTO-APPROVED] Tool (view_file):[/] [dim][/]
[bold green]✔ Finished view_file[/]

[bold cyan]⚡ [AUTO-APPROVED] Tool (view_file):[/] [dim][/]
[bold green]✔ Finished view_file[/]

[bold cyan]⚡ [AUTO-APPROVED] Tool (view_file):[/] [dim][/]
[bold green]✔ Finished view_file[/]

[bold cyan]⚡ [AUTO-APPROVED] Tool (run_command):[/] [dim]php artisan tinker --execute="echo App\Models\User::count() . ' users, ' . App\Models\Org::count() . ' orgs' . PHP_EOL;"[/]
[bold green]✔ Finished run_command[/]

[bold cyan]⚡ [AUTO-APPROVED] Tool (run_command):[/] [dim]php artisan tinker --execute="foreach (App\Models\User::all() as \$u) { echo \$u->id . ' | ' . \$u->email . ' | ' . \$u->name . ' | created_at: ' . \$u->created_at . PHP_EOL; }"[/]
[bold green]✔ Finished run_command[/]

[bold cyan]⚡ [AUTO-APPROVED] Tool (run_command):[/] [dim]find app -type f -name "*Tenant*" -o -name "*Org*"[/]
[bold green]✔ Finished run_command[/]

[bold cyan]⚡ [AUTO-APPROVED] Tool (view_file):[/] [dim][/]
[bold green]✔ Finished view_file[/]

[bold cyan]⚡ [AUTO-APPROVED] Tool (run_command):[/] [dim]find resources/views -type f[/]
[bold green]✔ Finished run_command[/]

[bold cyan]⚡ [AUTO-APPROVED] Tool (view_file):[/] [dim][/]
[bold green]✔ Finished view_file[/]

[bold cyan]⚡ [AUTO-APPROVED] Tool (view_file):[/] [dim][/]
[bold green]✔ Finished view_file[/]

[bold cyan]⚡ [AUTO-APPROVED] Tool (run_command):[/] [dim]php artisan tinker --execute="echo 'Roles: ' . \Spatie\Permission\Models\Role::count() . ', Permissions: ' . \Spatie\Permission\Models\Permission::count() . PHP_EOL;"[/]
[bold green]✔ Finished run_command[/]

[bold cyan]⚡ [AUTO-APPROVED] Tool (run_command):[/] [dim]php artisan tinker --execute="foreach (\App\Models\User::with('orgs')->get() as \$u) { echo \$u->id . ' | ' . \$u->email . ' | ' . \$u->name . ' | orgs: ' . \$u->orgs->pluck('name')->implode(', ') . PHP_EOL; }"[/]
[bold green]✔ Finished run_command[/]

[bold cyan]⚡ [AUTO-APPROVED] Tool (run_command):[/] [dim]php artisan test[/]
[bold green]✔ Finished run_command[/]
[dim red]discover_other_daemon: 1dbus-daemon[341545]: [session uid=1000 pid=341545 pidfd=5] Failed to activate service 'org.freedesktop.secrets': timed out (service_start_timeout=120000ms)
[/]GNOME_KEYRING_CONTROL=/run/user/1000/keyring

[bold cyan]⚡ [AUTO-APPROVED] Tool (view_file):[/] [dim][/]
[bold green]✔ Finished view_file[/]

[bold cyan]⚡ [AUTO-APPROVED] Tool (view_file):[/] [dim][/]
[bold green]✔ Finished view_file[/]
# Seni Bina Teknikal & Spesifikasi Pelaksanaan: Superadmin & Pengurusan Pemilik Self-Hosted OpenKuasaOS

---

## 1. Sintesis Keperluan & Reka Bentuk Seni Bina (Architectural Synthesis)

### 1.1 Latar Belakang & Pernyataan Masalah
OpenKuasaOS beroperasi sebagai sistem monolitik multi-tenant (Laravel 13 + PostgreSQL). Walau bagaimanapun, pada masa ini:
1. **Tiada Lapisan Superadmin Global:** Semua kawalan terhad pada peringkat organisasi (`org_members.role = owner/admin`). Pemilik self-hosted (server host owner) tidak mempunyai konsol berpusat untuk memantau metrik global (jumlah pengguna berdaftar, jumlah syarikat/organisasi berdaftar, status kesihatan pelayan).
2. **Kekeliruan Akaun Demo vs Akaun Sebenar:**
   - **Akaun Pertama** (`admin@openkuasa.my` / Ahmad Zulkifli / Rimba Ventures Sdn Bhd) adalah **Akaun Demo** yang dijana oleh seeder dengan data tiruan bagi tujuan paparan produk.
   - **Akaun Kedua** (`admin@sabily.info` / Maui Sabily / Sabily Enterprise) adalah **Pemilik Sebenar Self-Hosted (Main Owner)** yang perlu dinaik taraf sebagai **Superadmin Penuh** untuk mengawal sistem.

### 1.2 Matlamat & Reka Bentuk Tahap Tinggi
```
+---------------------------------------------------------------------------------+
|                                OPENKUASA OS                                     |
+---------------------------------------------------------------------------------+
                                      |
       +------------------------------+------------------------------+
       |                                                             |
+--------------------------------+            +-----------------------------------+
|     LAPISAN 1: AKAUN DEMO       |            |     LAPISAN 2: SUPERADMIN UTAMA   |
|   (admin@openkuasa.my)         |            |        (admin@sabily.info)        |
+--------------------------------+            +-----------------------------------+
| • Flag: `is_demo = true`       |            | • Flag: `is_superadmin = true`    |
| • Terhad kepada tenant demo    |            | • Memintas had tenant (Global)   |
| • Label badge visual [DEMO]    |            | • Konsol Utama: `/superadmin`     |
| • Tiada akses `/superadmin`    |            | • Pantau pendaftaran pengguna & org|
+--------------------------------+            +-----------------------------------+
                                                                 |
                                       +-------------------------+-------------------------+
                                       |                                                   |
                         +---------------------------+                       +---------------------------+
                         |  PEMANTAUAN SISTEM GLOBAL |                       |  KAWALAN TENANT & PENGGUNA |
                         | • Kaunter Pendaftaran     |                       | • Senarai & Status User   |
                         | • Kaunter Organisasi      |                       | • Senarai Tenant & Metrik |
                         | • Kesihatan Server/DB/AI  |                       | • Impersonasi / Bantuan   |
                         +---------------------------+                       +---------------------------+
```

### 1.3 Pemisahan Peranan (Dual-Tier Authorization)
| Tahap | Peranan | Skop Tanggungjawab | Entiti Pangkalan Data |
|---|---|---|---|
| **Peringkat Sistem (Global)** | **Superadmin** | Memantau seluruh instalasi self-hosted, melihat bilangan pendaftaran, mengurus pengguna global, status pelayan. | `users.is_superadmin = true` |
| **Peringkat Sistem (Demo)** | **Demo User** | Akses contoh untuk ujian fungsi tanpa menjejaskan integriti platform self-hosted. | `users.is_demo = true` |
| **Peringkat Tenant (Organisasi)** | **Org Owner / Member** | Mengendalikan data perniagaan syarikat masing-masing (Invois, HR, CRM, Iklan). | `org_members.role` (owner/admin/member) |

---

## 2. Spesifikasi Teknikal & Model Data (Technical Specification)

### 2.1 Skema Pangkalan Data & Migrasi

#### A. Kemas Kini Jadual `users`
Migrasi: `database/migrations/2026_10_13_010000_add_superadmin_and_demo_to_users_table.php`
```sql
ALTER TABLE users 
ADD COLUMN is_superadmin BOOLEAN NOT NULL DEFAULT FALSE,
ADD COLUMN is_demo BOOLEAN NOT NULL DEFAULT FALSE,
ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'active'; -- 'active', 'suspended', 'pending'

CREATE INDEX idx_users_is_superadmin ON users(is_superadmin);
CREATE INDEX idx_users_is_demo ON users(is_demo);
```

#### B. Jadual Tetapan Sistem Global `system_settings`
Migrasi: `database/migrations/2026_10_13_010100_create_system_settings_table.php`
```sql
CREATE TABLE system_settings (
    id UUID PRIMARY KEY,
    key VARCHAR(100) UNIQUE NOT NULL,
    value JSONB NULL,
    description TEXT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);
```
Nilai piawai:
- `registration_enabled`: `true` (membolehkan atau menutup pendaftaran awam pada login/register)
- `instance_name`: `'OpenKuasaOS Self-Hosted'`
- `owner_user_id`: `'01a130a3-db17-73c4-8584-ea2d1dd4ea4e'` (ID akaun kedua)

---

### 2.2 Kontrak Laluan & API (Route & API Contracts)

Laluan di bawah dilindungi oleh middleware: `['auth', 'superadmin']`.

| Kaedah | Laluan (Route Name) | Fungsi | Parameter / Payload | Respon / Tindakan |
|---|---|---|---|---|
| **GET** | `/superadmin`<br>(`superadmin.dashboard`) | Papan pemuka analitis & pemantauan global | Tiada | HTML Blade: Ringkasan statistik pendaftaran, carta tren, kesihatan pelayan |
| **GET** | `/superadmin/users`<br>(`superadmin.users.index`) | Direktori semua pengguna | Query: `search`, `status`, `role`, `page` | HTML Blade: Jadual pengguna dengan pagination & penapis status |
| **POST** | `/superadmin/users/{id}/toggle-superadmin`<br>(`superadmin.users.toggle-superadmin`) | Lantik atau lucut jawatan Superadmin | CSRF Token | Redirect dengan flash notification |
| **POST** | `/superadmin/users/{id}/status`<br>(`superadmin.users.status`) | Tukar status akaun (Aktif / Gantung) | `status`: `'active'` \| `'suspended'` | Redirect dengan flash notification |
| **GET** | `/superadmin/organizations`<br>(`superadmin.organizations.index`) | Direktori semua organisasi/penyewa | Query: `search`, `page` | HTML Blade: Senarai syarikat, bilangan staf, jumlah invois, pemilik |
| **POST** | `/superadmin/organizations/{id}/impersonate`<br>(`superadmin.organizations.impersonate`) | Log masuk paparan tenant untuk bantuan sokongan | CSRF Token | Set `current_org_id`, simpan `impersonated_by`, redirect ke `/dashboard` |
| **POST** | `/superadmin/impersonate/leave`<br>(`superadmin.impersonate.leave`) | Keluar dari paparan bantuan sokongan tenant | CSRF Token | Pulihkan `current_org_id` asal, redirect ke `/superadmin/organizations` |
| **GET** | `/superadmin/system`<br>(`superadmin.system.index`) | Status pelayan, DB, AI & Tetapan | Tiada | HTML Blade: PHP/PGSQL version, disk space, suis pendaftaran |
| **POST** | `/superadmin/system/settings`<br>(`superadmin.system.settings.update`) | Kemas kini parameter platform self-hosted | `registration_enabled`: boolean,<br>`instance_name`: string | Redirect / JSON `{ success: true }` |
| **GET** | `/api/v1/superadmin/metrics`<br>(`api.superadmin.metrics`) | Metrik telemetri JSON (Headless/Monitor) | Bearer Token (Sanctum) | JSON `{ total_users, total_orgs, active_users_24h, storage_mb }` |

---

## 3. [BACKEND_TASK]

### 3.1 Pangkalan Data & Migrasi
- [ ] Cipta fail migrasi `database/migrations/2026_10_13_010000_add_superadmin_and_demo_to_users_table.php`:
  - Tambah lajur `is_superadmin` (boolean, default: `false`).
  - Tambah lajur `is_demo` (boolean, default: `false`).
  - Tambah lajur `status` (string, default: `'active'`).
- [ ] Cipta fail migrasi `database/migrations/2026_10_13_010100_create_system_settings_table.php`:
  - Cipta jadual `system_settings` (`id`, `key`, `value`, `description`, timestamps).
- [ ] Cipta migrasi penentukuran automatik `2026_10_13_010200_assign_superadmin_to_second_user.php`:
  - **Akaun Pertama** (`admin@openkuasa.my`): Tetapkan `is_demo = true`, `is_superadmin = false`.
  - **Akaun Kedua** (`admin@sabily.info` / akaun ke-2 berdaftar): Tetapkan `is_superadmin = true`, `is_demo = false`, `status = 'active'`.

### 3.2 Model & Logik Domain
- [ ] Kemas kini [`User`](file:///home/miqa/github/OpenKuasaOS/app/Models/User.php):
  - Tambah `is_superadmin`, `is_demo`, dan `status` ke dalam `$fillable`.
  - Tambah casting: `'is_superadmin' => 'boolean'`, `'is_demo' => 'boolean'`.
  - Tambah pembantu mudah (helpers):
    - `public function isSuperadmin(): bool`
    - `public function isDemo(): bool`
    - `public function isActive(): bool`
  - Tambah Query Scopes: `scopeSuperadmins()`, `scopeDemoUsers()`, `scopeRealUsers()`.
- [ ] Cipta Model [`SystemSetting`](file:///home/miqa/github/OpenKuasaOS/app/Models/SystemSetting.php) dengan kaedah statik `get(key, default)` dan `set(key, value)`.

### 3.3 Keselamatan & Middleware
- [ ] Cipta middleware [`EnsureSuperadmin`](file:///home/miqa/github/OpenKuasaOS/app/Http/Middleware/EnsureSuperadmin.php):
  - Semak `Auth::check() && Auth::user()->is_superadmin`.
  - Sekiranya gagal, kembalikan HTTP 403 atau redirect dengan mesej ralat akses.
- [ ] Daftarkan alias `'superadmin' => \App\Http\Middleware\EnsureSuperadmin::class` di dalam [`bootstrap/app.php`](file:///home/miqa/github/OpenKuasaOS/bootstrap/app.php).
- [ ] Sekat pengguna yang digantung (`status === 'suspended'`) daripada log masuk dalam [`AuthController::login`](file:///home/miqa/github/OpenKuasaOS/app/Http/Controllers/Web/AuthController.php#L28-L56).

### 3.4 Perkhidmatan Sistem (Service Layer)
- [ ] Cipta [`App\Services\Superadmin\SuperadminMetricsService`](file:///home/miqa/github/OpenKuasaOS/app/Services/Superadmin/SuperadminMetricsService.php):
  - `getTotalUsersCount(): int` (Pecahan: Real vs Demo).
  - `getTotalOrganizationsCount(): int`.
  - `getRegistrationTrends(int $days = 30): array` (Kumpulan pendaftaran mengikut hari).
  - `getSystemHealth(): array` (Versi PHP 8.3+, Laravel 13, PostgreSQL connection, saiz disk/storage, model AI Tuah aktif).
- [ ] Cipta [`App\Services\Superadmin\ImpersonationService`](file:///home/miqa/github/OpenKuasaOS/app/Services/Superadmin/ImpersonationService.php):
  - Membolehkan Superadmin menukar konteks ke mana-mana organisasi dengan selamat untuk bantuan teknikal tanpa perlu memadamkan data sesi asal.

### 3.5 Pengawal (Controllers)
- [ ] Cipta [`App\Http\Controllers\Web\Superadmin\SuperadminDashboardController`](file:///home/miqa/github/OpenKuasaOS/app/Http/Controllers/Web/Superadmin/SuperadminDashboardController.php).
- [ ] Cipta [`App\Http\Controllers\Web\Superadmin\SuperadminUserController`](file:///home/miqa/github/OpenKuasaOS/app/Http/Controllers/Web/Superadmin/SuperadminUserController.php).
- [ ] Cipta [`App\Http\Controllers\Web\Superadmin\SuperadminOrgController`](file:///home/miqa/github/OpenKuasaOS/app/Http/Controllers/Web/Superadmin/SuperadminOrgController.php).
- [ ] Cipta [`App\Http\Controllers\Web\Superadmin\SuperadminSettingsController`](file:///home/miqa/github/OpenKuasaOS/app/Http/Controllers/Web/Superadmin/SuperadminSettingsController.php).

### 3.6 Perintah Artisan (CLI Commands)
- [ ] Cipta perintah `php artisan openkuasa:make-superadmin {email}`:
  - Membolehkan pentadbir menaikkan mana-mana emel menjadi Superadmin terus dari terminal sekiranya terkunci daripada web UI.
- [ ] Cipta perintah `php artisan openkuasa:status`:
  - Memaparkan ringkasan terminal: Superadmin Semasa, Bilangan Pendaftaran, Status Demo, dan Sambungan DB.

### 3.7 Ujian Automasi Backend (PHPUnit Test Matrix)
- [ ] `tests/Feature/Superadmin/SuperadminAccessTest.php`:
  - Pengguna biasa (non-superadmin) disekat daripada mengakses `/superadmin` (HTTP 403).
  - Pengguna demo (`is_demo = true`) disekat daripada mengakses `/superadmin` (HTTP 403).
  - Superadmin (`is_superadmin = true`) dibenarkan mengakses `/superadmin` (HTTP 200).
- [ ] `tests/Feature/Superadmin/SuperadminUserManagementTest.php`:
  - Superadmin boleh menukar status pengguna (Active -> Suspended).
  - Pengguna dengan status 'suspended' gagal log masuk.
  - Superadmin boleh melantik pengguna lain sebagai Superadmin.
- [ ] `tests/Feature/Superadmin/SuperadminMetricsTest.php`:
  - Metrik mengira jumlah sebenar pendaftaran pengguna dan organisasi dengan tepat.
- [ ] `tests/Feature/Superadmin/ImpersonationTest.php`:
  - Superadmin boleh bertukar konteks organisasi dan kembali ke sesi asal dengan selamat.

---

## 4. [FRONTEND_TASK]

### 4.1 Navigasi & Sidebar Terpelihara ([`resources/views/layouts/app.blade.php`](file:///home/miqa/github/OpenKuasaOS/resources/views/layouts/app.blade.php))
- [ ] Tambah bahagian menu eksklusif **"Pentadbiran Utama" (Self-Hosted Master Console)** di bahagian atas atau bawah navigasi:
  - Hanya dipaparkan sekiranya `@if(Auth::user()->isSuperadmin())`.
  - Ikon: Perisai / Mahkota Emas (`text-amber-500`).
  - Pautan menu:
    - **Pusat Kawalan Superadmin** (`route('superadmin.dashboard')`)
    - **Pengurusan Pengguna** (`route('superadmin.users.index')`)
    - **Pengurusan Organisasi** (`route('superadmin.organizations.index')`)
    - **Kesihatan Pelayan** (`route('superadmin.system.index')`)
- [ ] Tambah **Lencana Pengguna (Badge)** di sebelah nama pengguna:
  - Jika Superadmin: Lencana Emas/Indigo `[SUPERADMIN]`.
  - Jika Akaun Demo: Lencana Amber/Kuning `[DEMO MODE]`.

### 4.2 Banner Notifikasi Demo & Impersonasi
- [ ] Tambah banner kekal di bahagian atas skrin apabila log masuk sebagai Akaun Demo (`Auth::user()->isDemo()`):
  - Mesej mesra: *"Anda sedang menggunakan Akaun Demo (Ahmad Zulkifli / Rimba Ventures). Data di sini adalah simulasi."*
- [ ] Tambah banner apabila Superadmin sedang dalam mod impersonasi organisasi lain:
  - Butang merah/oren *"Keluar Dari Mod Bantuan Organisasi"* untuk kembali terus ke `/superadmin/organizations`.

### 4.3 Papan Pemuka Superadmin ([`resources/views/superadmin/dashboard.blade.php`](file:///home/miqa/github/OpenKuasaOS/resources/views/superadmin/dashboard.blade.php))
- [ ] **4 Kad KPI Utama Sistem:**
  1. **Jumlah Pendaftaran Pengguna:** Angka langsung (contoh: 2 pengguna) berserta perincian (1 Pemilik Sebenar, 1 Demo).
  2. **Jumlah Organisasi / Syarikat:** Angka langsung syarikat berdaftar (contoh: Sabily Enterprise, Rimba Ventures).
  3. **Pengguna Aktif:** Jumlah log masuk dalam tempoh 7 hari terakhir.
  4. **Status Kesihatan Pelayan:** Penunjuk hijau *PostgreSQL Connected / Tuah AI Ready / Storage OK*.
- [ ] **Carta Tren Pendaftaran (Registration Trends):**
  - Graf visual (Chart.js / Tailwind Bars) memaparkan jumlah pendaftaran mengikut tarikh.
- [ ] **Jadual Aktiviti Terkini (Recent Signups):**
  - Paparan 5 pendaftaran terbaharu (Nama, Emel, Syarikat, Tarikh Daftar, Status).

### 4.4 Pengurusan Pengguna ([`resources/views/superadmin/users/index.blade.php`](file:///home/miqa/github/OpenKuasaOS/resources/views/superadmin/users/index.blade.php))
- [ ] **Bar Carian & Penapis:** Carian mengikut emel/nama, penapis status (Semua, Aktif, Digantung, Demo).
- [ ] **Jadual Pengguna Penuh:**
  - Avatar, Nama, Emel, Organisasi Dipautkan, Status Superadmin (Badge Hijau/Kelabu), Status Akaun.
  - **Tindakan Interaktif (Alpine.js Action Dropdown):**
    - Butang *Jadikan Superadmin* / *Lucutkan Superadmin*.
    - Butang *Gantung Akaun (Suspend)* / *Aktifkan Akaun*.
    - Penunjuk khas bagi Akaun Demo (tidak boleh dipadam secara tidak sengaja).

### 4.5 Pengurusan Organisasi ([`resources/views/superadmin/organizations/index.blade.php`](file:///home/miqa/github/OpenKuasaOS/resources/views/superadmin/organizations/index.blade.php))
- [ ] Jadual Organisasi Berdaftar:
  - Nama Syarikat, Slug, Tarikh Dicipta, Pemilik Utama (Owner Email), Bilangan Ahli (`OrgMember`).
  - Metrik pantas: Jumlah Invois dikeluarkan, Jumlah Staf.
  - Butang Tindakan: *"Buka Paparan Organisasi (Impersonate Support)"*.

### 4.6 Tetapan Pelayan & Self-Hosted ([`resources/views/superadmin/system/index.blade.php`](file:///home/miqa/github/OpenKuasaOS/resources/views/superadmin/system/index.blade.php))
- [ ] Suis Toggle Alpine.js: **Buka/Tutup Pendaftaran Baharu (`registration_enabled`)**.
- [ ] Kad Maklumat Pelayan: PHP Version (8.3+), Framework (Laravel 13), Pangkalan Data (PostgreSQL 16), IP Pelayan, Masa Aktif (Uptime).
- [ ] Butang Penyelenggaraan Pantas:
  - *Kosongkan Cache Sistem (`artisan optimize:clear`)* dengan maklum balas toast notifikasi.

---

## 5. Pelan Pengesahan & Jaminan Kualiti (QA Testing Plan)

1. **Ujian Penetapan Hak:**
   - Log masuk sebagai `admin@openkuasa.my` &rarr; Navigasi ke `/superadmin` &rarr; Jangkaan: **Disekat (HTTP 403 Forbidden)**.
   - Log masuk sebagai `admin@sabily.info` &rarr; Navigasi ke `/superadmin` &rarr; Jangkaan: **Akses Penuh (HTTP 200 OK)** dan menu Superadmin kelihatan di sidebar.
2. **Ujian Kaunter & Metrik:**
   - Cipta pendaftaran akaun baru melalui borang register (`/register`).
   - Semak di papan pemuka `/superadmin` &rarr; Kaunter pengguna dan organisasi bertambah sebanyak +1 serta-merta.
3. **Ujian Penggantungan Pengguna:**
   - Superadmin menggantung akaun pengguna baru.
   - Pengguna cuba log masuk &rarr; Jangkaan: Ditendang balik dengan ralat *"Akaun anda telah digantung oleh pentadbir sistem."*
