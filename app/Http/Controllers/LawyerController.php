<?php

namespace App\Http\Controllers;

use App\Models\Hearing;
use App\Models\Lawyer;
use Illuminate\Http\Request;

class LawyerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $lawyers = Lawyer::with('cases')
            ->when($this->userIsLawyer(), fn ($query) => $query->where('id', $this->currentLawyerId()))
            ->orderBy('full_name')
            ->paginate(20);
        return view('lawyers.index', compact('lawyers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->requireRole('admin', 'staff');
        return view('lawyers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->requireRole('admin', 'staff');

        $data = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'full_name' => 'required|string|max:255',
            'contact_number' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'specialization' => 'nullable|string',
            'status' => 'nullable|string',
        ]);

        $lawyer = Lawyer::create($data);
        $this->logActivity('Lawyer created', "Created lawyer {$lawyer->full_name}.", $lawyer);

        return redirect()->route('lawyers.show', $lawyer->id)->with('success','Lawyer created.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Lawyer $lawyer)
    {
        if ($this->userIsLawyer()) {
            abort_unless($lawyer->id === $this->currentLawyerId(), 403);
        }

        $lawyer->load(['cases.client']);
        $caseIds = $lawyer->cases->pluck('id');
        $upcomingHearings = Hearing::with('case.client')
            ->whereIn('case_id', $caseIds)
            ->whereDate('hearing_date', '>=', now()->toDateString())
            ->orderBy('hearing_date')
            ->orderBy('hearing_time')
            ->take(5)
            ->get();
        $stats = [
            'cases' => $lawyer->cases->count(),
            'open_cases' => $lawyer->cases->whereNotIn('case_status', ['Closed', 'Archived'])->count(),
            'high_priority' => $lawyer->cases->where('priority_level', 'High')->count(),
            'upcoming_hearings' => $upcomingHearings->count(),
        ];

        return view('lawyers.show', compact('lawyer', 'stats', 'upcomingHearings'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Lawyer $lawyer)
    {
        $this->requireRole('admin', 'staff');
        return view('lawyers.edit', compact('lawyer'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Lawyer $lawyer)
    {
        $this->requireRole('admin', 'staff');

        $data = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'full_name' => 'required|string|max:255',
            'contact_number' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'specialization' => 'nullable|string',
            'status' => 'nullable|string',
        ]);

        $lawyer->update($data);
        $this->logActivity('Lawyer updated', "Updated lawyer {$lawyer->full_name}.", $lawyer);

        return redirect()->route('lawyers.show', $lawyer->id)->with('success','Lawyer updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Lawyer $lawyer)
    {
        $this->requireRole('admin');
        $this->logActivity('Lawyer deleted', "Deleted lawyer {$lawyer->full_name}.", $lawyer);
        $lawyer->delete();
        return redirect()->route('lawyers.index')->with('success','Lawyer deleted.');
    }
}
