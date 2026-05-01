<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\User;
use App\Support\PlanALimits;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StaffController extends Controller
{
    public function index(Request $request): View
    {
        $staff = User::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->orderByRaw("CASE role WHEN 'OWNER' THEN 1 WHEN 'CASHIER' THEN 2 WHEN 'KITCHEN' THEN 3 ELSE 4 END")
            ->orderBy('name')
            ->get();

        return view('staff.index', [
            'staff' => $staff,
            'maxActiveUsers' => $request->user()->tenant?->max_active_users ?? PlanALimits::MAX_ACTIVE_USERS,
        ]);
    }

    public function create(Request $request): View
    {
        return view('staff.form', [
            'staffMember' => new User([
                'role' => UserRole::CASHIER,
                'is_active' => true,
            ]),
            'roles' => UserRole::cases(),
            'maxActiveUsers' => $request->user()->tenant?->max_active_users ?? PlanALimits::MAX_ACTIVE_USERS,
        ]);
    }

    public function store(Request $request, PlanALimits $planLimits): RedirectResponse
    {
        $validated = $this->validatedStaff($request);
        $role = UserRole::from($validated['role']);

        if ($request->boolean('is_active')) {
            $planLimits->assertCanAddUser($request->user()->tenant);
        }

        User::create([
            'tenant_id' => $request->user()->tenant_id,
            'outlet_id' => $request->user()->outlet_id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => $role,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('staff.index')
            ->with('status', 'Staff account created.');
    }

    public function edit(Request $request, User $staff): View
    {
        $this->authorizeTenantStaff($request, $staff);

        return view('staff.form', [
            'staffMember' => $staff,
            'roles' => UserRole::cases(),
            'maxActiveUsers' => $request->user()->tenant?->max_active_users ?? PlanALimits::MAX_ACTIVE_USERS,
        ]);
    }

    public function update(Request $request, User $staff, PlanALimits $planLimits): RedirectResponse
    {
        $this->authorizeTenantStaff($request, $staff);

        $wasInactive = ! $staff->is_active;
        $validated = $this->validatedStaff($request, $staff);
        $role = UserRole::from($validated['role']);

        if ($staff->is($request->user()) && $role !== $staff->role) {
            abort(422, 'You cannot change your own role.');
        }

        if ($wasInactive && $request->boolean('is_active')) {
            $planLimits->assertCanAddUser($request->user()->tenant);
        }

        $staff->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $role,
            'is_active' => $request->boolean('is_active'),
        ]);

        if (! empty($validated['password'])) {
            $staff->password = $validated['password'];
        }

        $staff->save();

        return redirect()
            ->route('staff.index')
            ->with('status', 'Staff account updated.');
    }

    public function status(Request $request, User $staff, PlanALimits $planLimits): RedirectResponse
    {
        $this->authorizeTenantStaff($request, $staff);

        abort_if($staff->is($request->user()), 422, 'You cannot deactivate your own account.');

        if (! $staff->is_active) {
            $planLimits->assertCanAddUser($request->user()->tenant);
        }

        $staff->forceFill(['is_active' => ! $staff->is_active])->save();

        return back()->with('status', $staff->is_active ? 'Staff account activated.' : 'Staff account deactivated.');
    }

    public function sendPasswordReset(Request $request, User $staff): RedirectResponse
    {
        $this->authorizeTenantStaff($request, $staff);

        $status = Password::sendResetLink(['email' => $staff->email]);

        return back()->with(
            $status === Password::RESET_LINK_SENT ? 'status' : 'error',
            __($status)
        );
    }

    /**
     * @return array{name: string, email: string, role: string, password?: string|null}
     */
    private function validatedStaff(Request $request, ?User $staff = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($staff?->id),
            ],
            'role' => ['required', Rule::enum(UserRole::class)],
            'password' => [$staff ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    private function authorizeTenantStaff(Request $request, User $staff): void
    {
        abort_if($staff->tenant_id !== $request->user()->tenant_id, 404);
        abort_if($request->user()->role !== UserRole::OWNER, 403, 'Only owners can manage staff accounts.');
    }
}
