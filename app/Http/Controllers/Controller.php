<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Lawyer;
use App\Models\LegalCase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

abstract class Controller
{
    protected function logActivity(string $action, string $description, ?Model $subject = null, array $metadata = []): void
    {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'description' => $description,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'metadata' => $metadata ?: null,
        ]);
    }

    protected function requireRole(string ...$roles): void
    {
        $role = Auth::user()?->role ?: 'staff';

        abort_unless(Auth::user() && in_array($role, $roles, true), 403);
    }

    protected function userIsLawyer(): bool
    {
        return (Auth::user()?->role ?: 'staff') === 'lawyer';
    }

    protected function currentLawyerId(): ?int
    {
        if (! $this->userIsLawyer()) {
            return null;
        }

        return Lawyer::where('user_id', Auth::id())->value('id');
    }

    protected function restrictCasesToCurrentLawyer($query)
    {
        if (! $this->userIsLawyer()) {
            return $query;
        }

        $lawyerId = $this->currentLawyerId();

        return $lawyerId
            ? $query->where('assigned_lawyer_id', $lawyerId)
            : $query->whereRaw('1 = 0');
    }

    protected function authorizeCaseAccess(?LegalCase $case): void
    {
        if (! $this->userIsLawyer()) {
            return;
        }

        abort_unless($case && $case->assigned_lawyer_id === $this->currentLawyerId(), 403);
    }
}
