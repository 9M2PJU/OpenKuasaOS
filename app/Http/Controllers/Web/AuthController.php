<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Org;
use App\Models\OrgMember;
use App\Models\Profile;
use App\Models\User;
use App\Services\Tenancy\OrganizationContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Emel diperlukan.',
            'email.email' => 'Format emel tidak sah.',
            'password.required' => 'Kata laluan diperlukan.',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();

            if ($user->status === 'suspended') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => 'Akaun anda telah digantung oleh pentadbir sistem.',
                ])->onlyInput('email');
            }

            $request->session()->regenerate();

            $firstOrg = $user->orgs()->first();
            if ($firstOrg) {
                $request->session()->put('current_org_id', $firstOrg->id);
                OrganizationContext::setId($firstOrg->id);
            }

            return redirect()->intended(route('dashboard'))
                ->with('success', 'Selamat kembali ke KerjaAI!');
        }

        return back()->withErrors([
            'email' => 'Maklumat log masuk tidak sepadan dengan rekod kami.',
        ])->onlyInput('email');
    }

    public function showRegisterForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        $registrationEnabled = \App\Models\SystemSetting::get('registration_enabled', true);

        return view('auth.register', compact('registrationEnabled'));
    }

    public function register(Request $request)
    {
        if (! \App\Models\SystemSetting::get('registration_enabled', true)) {
            return back()->withErrors([
                'email' => 'Pendaftaran akaun baharu ditutup oleh pentadbir sistem.',
            ])->onlyInput('name', 'company_name', 'email');
        }
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'company_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'name.required' => 'Nama penuh diperlukan.',
            'company_name.required' => 'Nama syarikat / perniagaan diperlukan.',
            'email.required' => 'Emel diperlukan.',
            'email.unique' => 'Emel ini sudah berdaftar dalam sistem.',
            'password.required' => 'Kata laluan diperlukan.',
            'password.min' => 'Kata laluan mestilah sekurang-kurangnya 8 aksara.',
            'password.confirmed' => 'Pengesahan kata laluan tidak sepadan.',
        ]);

        $user = DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            Profile::create([
                'user_id' => $user->id,
                'email' => $validated['email'],
                'full_name' => $validated['name'],
            ]);

            $org = Org::create([
                'name' => $validated['company_name'],
                'slug' => Str::slug($validated['company_name']) . '-' . Str::random(4),
            ]);

            OrgMember::create([
                'org_id' => $org->id,
                'user_id' => $user->id,
                'role' => 'owner',
            ]);

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        $firstOrg = $user->orgs()->first();
        if ($firstOrg) {
            $request->session()->put('current_org_id', $firstOrg->id);
            OrganizationContext::setId($firstOrg->id);
        }

        return redirect()->route('dashboard')
            ->with('success', 'Akaun dan organisasi anda berjaya dicipta!');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('info', 'Anda telah berjaya log keluar.');
    }

    public function switchOrg(Request $request, $orgId)
    {
        $user = Auth::user();
        $hasAccess = OrgMember::where('org_id', $orgId)
            ->where('user_id', $user->id)
            ->exists();

        if ($hasAccess) {
            $request->session()->put('current_org_id', $orgId);
            OrganizationContext::setId($orgId);
            return back()->with('success', 'Organisasi berjaya ditukar.');
        }

        return back()->with('error', 'Anda tiada akses kepada organisasi tersebut.');
    }
}
