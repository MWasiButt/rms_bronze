<?php

namespace App\Http\Controllers;

use App\Actions\Auth\EnsureUserHasTenantContext;
use App\Actions\Auth\RegisterTenantOwner;
use App\Models\User;
use App\Support\Audit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth-signin');
    }

    public function login(Request $request, EnsureUserHasTenantContext $ensureUserHasTenantContext): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            return back()
                ->withErrors(['email' => 'The provided credentials do not match our records.'])
                ->onlyInput('email');
        }

        /** @var User $user */
        $user = Auth::user();

        if (! $user->is_active) {
            Auth::logout();

            return back()
                ->withErrors(['email' => 'Your account is inactive. Please contact the account owner.'])
                ->onlyInput('email');
        }

        $user = $ensureUserHasTenantContext->handle($user);

        if (! $user->tenant_id || ! $user->outlet_id) {
            Auth::logout();

            return back()
                ->withErrors(['email' => 'Your account is not linked to a business yet.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();
        $this->storeTenantContext($request, $user);

        $user->forceFill(['last_active_at' => now()])->save();

        Audit::record('auth.login', $user, [], [
            'email' => $user->email,
            'role' => $user->role?->value,
        ], $request);

        return redirect()->intended('/');
    }

    public function showRegister(): View
    {
        return view('auth-signup');
    }

    public function showForgotPassword(): View
    {
        return view('auth-reset-password');
    }

    public function sendPasswordResetLink(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withErrors(['email' => __($status)])->onlyInput('email');
    }

    public function showResetPassword(Request $request, string $token): View
    {
        return view('auth-create-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)->letters()->numbers()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => __($status)])->onlyInput('email');
    }

    public function register(Request $request, RegisterTenantOwner $registerTenantOwner): RedirectResponse
    {
        $validated = $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'outlet_name' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:1000'],
            'password' => ['required', 'string', 'confirmed', 'min:8'],
        ]);

        $user = $registerTenantOwner->handle($validated);

        Auth::login($user);
        $request->session()->regenerate();
        $this->storeTenantContext($request, $user);

        Audit::record('auth.login', $user, [], [
            'email' => $user->email,
            'role' => $user->role?->value,
            'registered' => true,
        ], $request);

        return redirect('/');
    }

    public function logout(Request $request): RedirectResponse
    {
        if ($request->user()) {
            Audit::record('auth.logout', $request->user(), [], [
                'email' => $request->user()->email,
                'role' => $request->user()->role?->value,
            ], $request);
        }

        Auth::logout();

        $request->session()->forget([
            'tenant_id',
            'outlet_id',
            'user_role',
        ]);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    private function storeTenantContext(Request $request, User $user): void
    {
        $request->session()->put([
            'tenant_id' => $user->tenant_id,
            'outlet_id' => $user->outlet_id,
            'user_role' => $user->role?->value,
        ]);
    }
}
