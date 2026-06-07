<?php

namespace App\Http\Controllers;

use App\Models\Lawyer;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StaffUserController extends Controller
{
    public function index(): View
    {
        $this->requireRole('admin');

        $users = User::query()
            ->orderByRaw("CASE role WHEN 'admin' THEN 1 WHEN 'lawyer' THEN 2 ELSE 3 END")
            ->orderBy('name')
            ->paginate(20);

        return view('staff_users.index', compact('users'));
    }

    public function create(): View
    {
        $this->requireRole('admin');

        return view('staff_users.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->requireRole('admin');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'in:admin,lawyer,staff'],
            'status' => ['required', 'in:active,inactive'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create($validated);
        $this->syncLawyerProfile($user);

        $this->logActivity('Staff account created', "Created {$user->role} account for {$user->name}.", $user);

        return redirect()
            ->route('staff-users.index')
            ->with('status', 'Staff account created.');
    }

    public function edit(User $staffUser): View
    {
        $this->requireRole('admin');

        return view('staff_users.edit', compact('staffUser'));
    }

    public function update(Request $request, User $staffUser): RedirectResponse
    {
        $this->requireRole('admin');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($staffUser->id),
            ],
            'role' => ['required', 'in:admin,lawyer,staff'],
            'status' => ['required', 'in:active,inactive'],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ]);

        $this->protectLastAdministrator($staffUser, $validated['role'], $validated['status']);

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $previousRole = $staffUser->role;
        $staffUser->update($validated);
        $this->syncLawyerProfile($staffUser, $previousRole);

        if ($staffUser->status === 'inactive') {
            DB::table('sessions')->where('user_id', $staffUser->id)->delete();
        }

        $this->logActivity('Staff account updated', "Updated {$staffUser->role} account for {$staffUser->name}.", $staffUser);

        return redirect()
            ->route('staff-users.index')
            ->with('status', 'Staff account updated.');
    }

    public function destroy(User $staffUser): RedirectResponse
    {
        $this->requireRole('admin');

        if ($staffUser->is(auth()->user())) {
            throw ValidationException::withMessages([
                'account' => 'You cannot delete the account you are currently using.',
            ]);
        }

        $this->protectLastAdministrator($staffUser, null, null);

        DB::transaction(function () use ($staffUser) {
            Lawyer::where('user_id', $staffUser->id)->update(['user_id' => null]);
            DB::table('sessions')->where('user_id', $staffUser->id)->delete();
            $this->logActivity('Staff account deleted', "Deleted {$staffUser->role} account for {$staffUser->name}.", $staffUser);
            $staffUser->delete();
        });

        return redirect()
            ->route('staff-users.index')
            ->with('status', 'Staff account deleted.');
    }

    private function syncLawyerProfile(User $user, ?string $previousRole = null): void
    {
        if ($user->role !== 'lawyer') {
            if ($previousRole === 'lawyer') {
                Lawyer::where('user_id', $user->id)->update(['user_id' => null]);
            }

            return;
        }

        $lawyer = Lawyer::query()
            ->where('user_id', $user->id)
            ->orWhere('email', $user->email)
            ->orWhere('full_name', $user->name)
            ->first();

        if ($lawyer) {
            $lawyer->update([
                'user_id' => $user->id,
                'full_name' => $user->name,
                'email' => $user->email,
                'status' => $user->status === 'active' ? 'Active' : 'Inactive',
            ]);

            return;
        }

        Lawyer::create([
            'user_id' => $user->id,
            'full_name' => $user->name,
            'email' => $user->email,
            'status' => $user->status === 'active' ? 'Active' : 'Inactive',
        ]);
    }

    private function protectLastAdministrator(User $user, ?string $newRole, ?string $newStatus): void
    {
        if ($user->role !== 'admin' || $user->status !== 'active') {
            return;
        }

        $removesActiveAdmin = $newRole === null
            || $newStatus === null
            || $newRole !== 'admin'
            || $newStatus !== 'active';

        if ($removesActiveAdmin && User::where('role', 'admin')->where('status', 'active')->count() <= 1) {
            throw ValidationException::withMessages([
                'account' => 'At least one active administrator account must remain.',
            ]);
        }
    }
}
