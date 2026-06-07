<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        $canDeleteAccount = $user->isAdmin()
            && User::where('role', 'admin')->where('status', 'active')->count() > 1;
        $deleteAccountMessage = $user->isAdmin()
            ? 'This is the last active administrator account. Create another active administrator before deleting it.'
            : 'Only an administrator can delete workplace accounts.';

        return view('profile.edit', [
            'user' => $user,
            'canDeleteAccount' => $canDeleteAccount,
            'deleteAccountMessage' => $deleteAccountMessage,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        if ($user->isAdmin()
            && $user->status === 'active'
            && User::where('role', 'admin')->where('status', 'active')->count() <= 1) {
            return Redirect::route('profile.edit')
                ->withErrors([
                    'password' => 'The last active administrator account cannot be deleted.',
                ], 'userDeletion');
        }

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
