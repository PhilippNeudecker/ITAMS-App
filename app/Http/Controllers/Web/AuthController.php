<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Services\LdapAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    public function __construct(private LdapAuthService $ldap) {}

    public function showLogin(): Response
    {
        return Inertia::render('Auth/Login');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $username = $request->input('username');
        $password = $request->input('password');

        // 1️⃣ LDAP
        $ldapUser = $this->ldap->authenticate($username, $password);

        if ($ldapUser) {
            $employee = $this->ldap->syncEmployee($ldapUser);
            $user     = \App\Models\User::where('employee_id', $employee->employee_id)->firstOrFail();
        } else {
            // 2️⃣ Lokaler Fallback
            $user = \App\Models\User::where('username', $username)->first();

            if (! $user || ! Hash::check($password, $user->password)) {
                throw ValidationException::withMessages([
                    'username' => ['Die Zugangsdaten sind ungültig.'],
                ]);
            }
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(route('assets.index'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
