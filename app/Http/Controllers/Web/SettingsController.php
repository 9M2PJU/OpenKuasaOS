<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\OrgSmtpSetting;
use App\Services\Mail\TenantMailer;
use App\Services\Tenancy\OrganizationContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SettingsController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $org = OrganizationContext::get();
        if (!$org && $user) {
            $org = $user->orgs()->first();
            if ($org) {
                OrganizationContext::set($org);
            }
        }
        $smtp = $org ? $org->smtpSetting : null;
        $currentLocale = App::getLocale();

        return view('settings.index', compact('user', 'org', 'smtp', 'currentLocale'));
    }

    public function updateLanguage(Request $request)
    {
        $validated = $request->validate([
            'locale' => ['required', 'string', 'in:ms,en'],
        ]);

        $locale = $validated['locale'];

        $request->session()->put('locale', $locale);

        if ($request->user()) {
            $request->user()->update(['locale' => $locale]);
        }

        App::setLocale($locale);

        $message = $locale === 'ms'
            ? 'Tetapan bahasa berjaya ditukar kepada Bahasa Melayu.'
            : 'Language settings successfully switched to English.';

        return redirect()->route('settings.index')->with('success', $message);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'org_name' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp,svg', 'max:2048'],
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        $org = OrganizationContext::get();
        if (!$org && $request->user()) {
            $org = $request->user()->orgs()->first();
            if ($org) {
                OrganizationContext::set($org);
            }
        }

        if ($org) {
            $updates = [];
            if (!empty($validated['org_name'])) {
                $updates['name'] = $validated['org_name'];
            }

            if ($request->hasFile('logo')) {
                if (!Storage::disk('public')->exists('logos')) {
                    Storage::disk('public')->makeDirectory('logos');
                }
                if ($org->logo_url) {
                    $oldPath = preg_replace('/^.*?\/storage\//', '', $org->logo_url);
                    if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                        Storage::disk('public')->delete($oldPath);
                    }
                }
                $path = $request->file('logo')->store('logos', 'public');
                $updates['logo_url'] = '/storage/' . $path;
            }

            if (!empty($updates)) {
                $org->update($updates);
            }
        }

        $isMalay = App::getLocale() === 'ms';
        $message = $isMalay ? 'Profil pengguna dan organisasi berjaya dikemas kini.' : 'User and organization profile updated successfully.';

        return redirect()->route('settings.index')->with('success', $message);
    }

    public function uploadLogo(Request $request)
    {
        $isMalay = App::getLocale() === 'ms';

        $messages = $isMalay ? [
            'logo.required' => 'Sila pilih fail logo untuk dimuat naik.',
            'logo.file' => 'Fail yang dimuat naik tidak sah.',
            'logo.mimes' => 'Format yang disyorkan dan dibenarkan ialah PNG, JPG, JPEG, WEBP, atau SVG.',
            'logo.max' => 'Saiz fail logo tidak boleh melebihi 2MB (2048 KB).',
        ] : [
            'logo.required' => 'Please select a logo file to upload.',
            'logo.file' => 'The uploaded file is not valid.',
            'logo.mimes' => 'Allowed formats are PNG, JPG, JPEG, WEBP, or SVG.',
            'logo.max' => 'Logo file size must not exceed 2MB (2048 KB).',
        ];

        $validator = Validator::make($request->all(), [
            'logo' => ['required', 'file', 'mimes:png,jpg,jpeg,webp,svg', 'max:2048'],
        ], $messages);

        if ($validator->fails()) {
            $firstError = $validator->errors()->first('logo') ?: $validator->errors()->first();
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $firstError,
                    'errors' => $validator->errors(),
                    'error' => [
                        'code' => 'VALIDATION_FAILED',
                        'message' => $firstError,
                        'details' => $validator->errors()->toArray(),
                    ],
                ], 422);
            }

            return back()->withErrors($validator);
        }

        $org = OrganizationContext::get();
        if (!$org && $request->user()) {
            $org = $request->user()->orgs()->first();
            if ($org) {
                OrganizationContext::set($org);
            }
        }

        if (!$org) {
            $notFoundMsg = $isMalay ? 'Tiada organisasi aktif ditemui.' : 'No active organization found.';
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $notFoundMsg,
                ], 404);
            }
            return back()->withErrors(['logo' => $notFoundMsg]);
        }

        // Pastikan folder logos wujud dalam storan awam
        if (!Storage::disk('public')->exists('logos')) {
            Storage::disk('public')->makeDirectory('logos');
        }

        // Padam logo lama jika wujud dalam storan tempatan
        if ($org->logo_url) {
            $oldPath = preg_replace('/^.*?\/storage\//', '', $org->logo_url);
            if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }
        }

        $path = $request->file('logo')->store('logos', 'public');
        $logoUrl = '/storage/' . $path;

        $org->update(['logo_url' => $logoUrl]);

        $message = $isMalay
            ? 'Logo syarikat berjaya dimuat naik dan disimpan terus.'
            : 'Company logo uploaded and saved successfully.';

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'logo_url' => $logoUrl,
                'message' => $message,
            ]);
        }

        return redirect()->route('settings.index')->with('success', $message);
    }

    public function removeLogo(Request $request)
    {
        $isMalay = App::getLocale() === 'ms';
        $org = OrganizationContext::get();
        if (!$org && $request->user()) {
            $org = $request->user()->orgs()->first();
            if ($org) {
                OrganizationContext::set($org);
            }
        }

        if ($org) {
            if ($org->logo_url) {
                $oldPath = preg_replace('/^.*?\/storage\//', '', $org->logo_url);
                if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }
            $org->update(['logo_url' => null]);
        }

        $message = $isMalay
            ? 'Logo syarikat telah dipadam.'
            : 'Company logo has been removed.';

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }

        return redirect()->route('settings.index')->with('success', $message);
    }

    public function updateSmtp(Request $request)
    {
        $isMalay = App::getLocale() === 'ms';
        $org = OrganizationContext::get();
        if (!$org && $request->user()) {
            $org = $request->user()->orgs()->first();
            if ($org) {
                OrganizationContext::set($org);
            }
        }

        if (!$org) {
            $msg = $isMalay ? 'Tiada organisasi aktif ditemui.' : 'No active organization found.';
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 404);
            }
            return back()->withErrors(['smtp' => $msg]);
        }

        $messages = $isMalay ? [
            'mail_port.integer' => 'Port SMTP mestilah nombor bulat (contoh: 587 atau 465).',
            'mail_port.min' => 'Port SMTP tidak sah.',
            'mail_port.max' => 'Port SMTP tidak sah.',
            'mail_from_address.email' => 'Alamat emel penghantar mestilah format emel yang sah.',
            'mail_encryption.in' => 'Penyulitan mestilah tls, ssl, atau none.',
        ] : [
            'mail_port.integer' => 'SMTP port must be an integer (e.g., 587 or 465).',
            'mail_port.min' => 'Invalid SMTP port.',
            'mail_port.max' => 'Invalid SMTP port.',
            'mail_from_address.email' => 'From address must be a valid email format.',
            'mail_encryption.in' => 'Encryption must be tls, ssl, or none.',
        ];

        $validated = $request->validate([
            'is_active' => ['nullable'],
            'mail_host' => ['nullable', 'string', 'max:255'],
            'mail_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'mail_username' => ['nullable', 'string', 'max:255'],
            'mail_password' => ['nullable', 'string', 'max:500'],
            'mail_encryption' => ['nullable', 'string', 'in:tls,ssl,none'],
            'mail_from_address' => ['nullable', 'email', 'max:255'],
            'mail_from_name' => ['nullable', 'string', 'max:255'],
        ], $messages);

        $smtp = $org->smtpSetting()->firstOrNew(['org_id' => $org->id]);

        $smtp->mail_mailer = 'smtp';
        $smtp->is_active = $request->boolean('is_active');
        $smtp->mail_host = !empty($validated['mail_host']) ? trim($validated['mail_host']) : null;
        $smtp->mail_port = !empty($validated['mail_port']) ? (int) $validated['mail_port'] : 587;
        $smtp->mail_username = !empty($validated['mail_username']) ? trim($validated['mail_username']) : null;
        $smtp->mail_encryption = !empty($validated['mail_encryption']) ? $validated['mail_encryption'] : 'tls';
        $smtp->mail_from_address = !empty($validated['mail_from_address']) ? trim($validated['mail_from_address']) : null;
        $smtp->mail_from_name = !empty($validated['mail_from_name']) ? trim($validated['mail_from_name']) : null;

        // Sekuriti: Hanya kemas kini kata laluan jika pengguna memasukkan kata laluan baharu
        if (!empty($validated['mail_password'])) {
            $smtp->mail_password = $validated['mail_password'];
        }

        $smtp->save();

        $successMsg = $isMalay
            ? 'Konfigurasi pelayan SMTP syarikat berjaya disimpan dengan penyulitan selamat (AES-256).'
            : 'Company SMTP gateway configuration saved securely (AES-256 encrypted).';

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $successMsg,
                'is_active' => $smtp->is_active,
                'has_password' => $smtp->hasPassword(),
            ]);
        }

        return redirect()->route('settings.index')->with('success', $successMsg);
    }

    public function testSmtp(Request $request)
    {
        $isMalay = App::getLocale() === 'ms';
        $org = OrganizationContext::get();
        if (!$org && $request->user()) {
            $org = $request->user()->orgs()->first();
            if ($org) {
                OrganizationContext::set($org);
            }
        }

        if (!$org) {
            return response()->json([
                'success' => false,
                'message' => $isMalay ? 'Tiada organisasi aktif ditemui.' : 'No active organization found.',
            ], 404);
        }

        $messages = $isMalay ? [
            'test_email.required' => 'Sila masukkan alamat emel penerima untuk ujian.',
            'test_email.email' => 'Alamat emel penerima mestilah sah.',
        ] : [
            'test_email.required' => 'Please enter recipient email address for testing.',
            'test_email.email' => 'Recipient email address must be valid.',
        ];

        $validated = $request->validate([
            'test_email' => ['required', 'email'],
            'mail_host' => ['nullable', 'string', 'max:255'],
            'mail_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'mail_username' => ['nullable', 'string', 'max:255'],
            'mail_password' => ['nullable', 'string', 'max:500'],
            'mail_encryption' => ['nullable', 'string', 'in:tls,ssl,none'],
            'mail_from_address' => ['nullable', 'email', 'max:255'],
            'mail_from_name' => ['nullable', 'string', 'max:255'],
        ], $messages);

        $smtp = $org->smtpSetting;
        if (!$smtp) {
            $smtp = new OrgSmtpSetting(['org_id' => $org->id]);
        }

        // Benarkan ujian menggunakan nilai daripada form semasa sebelum disimpan
        if (!empty($validated['mail_host'])) {
            $smtp->mail_host = trim($validated['mail_host']);
            $smtp->mail_port = !empty($validated['mail_port']) ? (int) $validated['mail_port'] : 587;
            $smtp->mail_username = !empty($validated['mail_username']) ? trim($validated['mail_username']) : $smtp->mail_username;
            $smtp->mail_encryption = !empty($validated['mail_encryption']) ? $validated['mail_encryption'] : $smtp->mail_encryption;
            $smtp->mail_from_address = !empty($validated['mail_from_address']) ? trim($validated['mail_from_address']) : $smtp->mail_from_address;
            $smtp->mail_from_name = !empty($validated['mail_from_name']) ? trim($validated['mail_from_name']) : $smtp->mail_from_name;
        }

        if (!empty($validated['mail_password'])) {
            $smtp->mail_password = $validated['mail_password'];
        }

        if (empty($smtp->mail_host)) {
            return response()->json([
                'success' => false,
                'message' => $isMalay ? 'Sila masukkan hos pelayan SMTP (contoh: smtp.gmail.com) terlebih dahulu.' : 'Please enter the SMTP host first.',
            ], 422);
        }

        $result = TenantMailer::sendTestEmail($smtp, $validated['test_email'], $org->name, $isMalay);

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    public function updatePassword(Request $request)
    {
        $isMalay = App::getLocale() === 'ms';

        $messages = $isMalay ? [
            'current_password.required' => 'Sila masukkan kata laluan asal anda.',
            'current_password.current_password' => 'Kata laluan asal yang dimasukkan tidak tepat.',
            'password.required' => 'Sila masukkan kata laluan baharu.',
            'password.min' => 'Kata laluan baharu mestilah sekurang-kurangnya :min aksara.',
            'password.confirmed' => 'Pengesahan kata laluan baharu tidak sepadan.',
        ] : [
            'current_password.required' => 'Please enter your current password.',
            'current_password.current_password' => 'The provided current password does not match.',
            'password.required' => 'Please enter a new password.',
            'password.min' => 'The new password must be at least :min characters.',
            'password.confirmed' => 'The new password confirmation does not match.',
        ];

        $validated = $request->validate([
            'current_password' => ['required', 'string', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], $messages);

        $request->user()->update([
            'password' => $validated['password'],
        ]);

        $message = $isMalay
            ? 'Kata laluan anda telah berjaya dikemas kini.'
            : 'Your password has been changed successfully.';

        return redirect()->route('settings.index')->with('success', $message);
    }
}
