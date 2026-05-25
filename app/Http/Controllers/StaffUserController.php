<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class StaffUserController extends Controller
{
    public function index(): View
    {
        $this->requireRole('admin');

        $users = User::query()
            ->orderByRaw("CASE role WHEN 'admin' THEN 1 WHEN 'lawyer' THEN 2 WHEN 'secretary' THEN 3 ELSE 4 END")
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
            'role' => ['required', 'in:admin,lawyer,secretary,staff'],
            'status' => ['required', 'in:active,inactive'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create($validated);

        $this->logActivity('Staff account created', "Created {$user->role} account for {$user->name}.", $user);

        return redirect()
            ->route('staff-users.index')
            ->with('status', 'Staff account created.');
    }
}
