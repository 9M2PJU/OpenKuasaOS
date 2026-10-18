<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\Tenancy\OrganizationContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Hash;

class SettingsController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $org = OrganizationContext::get();
        $currentLocale = App::getLocale();

        return view('settings.index', compact('user', 'org', 'currentLocale'));
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
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if (!empty($validated['org_name'])) {
            $org = OrganizationContext::get();
            if ($org) {
                $org->update(['name' => $validated['org_name']]);
            }
        }

        $isMalay = App::getLocale() === 'ms';
        $message = $isMalay ? 'Profil pengguna dan organisasi berjaya dikemas kini.' : 'User and organization profile updated successfully.';

        return redirect()->route('settings.index')->with('success', $message);
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
