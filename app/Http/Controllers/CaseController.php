<?php

namespace App\Http\Controllers;

use App\Models\LegalCase;
use App\Models\Client;
use App\Models\Lawyer;
use Illuminate\Http\Request;

class CaseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $sort = $request->query('sort', 'newest');
        $query = LegalCase::with(['client','assignedLawyer'])->select('cases.*');
        $this->restrictCasesToCurrentLawyer($query);

        $query->when($request->filled('status'), fn ($query) => $query->where('case_status', $request->query('status')));
        $query->when($request->filled('priority'), fn ($query) => $query->where('priority_level', $request->query('priority')));
        $query->when($request->filled('client_id'), fn ($query) => $query->where('client_id', $request->query('client_id')));
        $query->when($request->filled('lawyer_id'), fn ($query) => $query->where('assigned_lawyer_id', $request->query('lawyer_id')));

        match ($sort) {
            'priority' => $query->orderByRaw("CASE priority_level WHEN 'High' THEN 1 WHEN 'Medium' THEN 2 WHEN 'Low' THEN 3 ELSE 4 END")->latest('cases.created_at'),
            'status' => $query->orderBy('case_status')->latest('cases.created_at'),
            'client' => $query->leftJoin('clients', 'clients.id', '=', 'cases.client_id')->orderBy('clients.full_name')->latest('cases.created_at'),
            'lawyer' => $query->leftJoin('lawyers', 'lawyers.id', '=', 'cases.assigned_lawyer_id')->orderBy('lawyers.full_name')->latest('cases.created_at'),
            'date_filed' => $query->orderByRaw('date_filed IS NULL')->orderByDesc('date_filed')->latest('cases.created_at'),
            default => $query->latest('cases.created_at'),
        };

        $cases = $query->paginate(15)->withQueryString();

        if ($request->wantsJson()) {
            return response()->json($cases);
        }

        $clients = Client::orderBy('full_name')->get();
        $lawyers = Lawyer::orderBy('full_name')->get();
        $statuses = LegalCase::query()->whereNotNull('case_status')->distinct()->orderBy('case_status')->pluck('case_status');

        return view('cases.index', compact('cases', 'sort', 'clients', 'lawyers', 'statuses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $this->requireRole('admin', 'staff');

        $clients = Client::orderBy('full_name')->get();
        $lawyers = Lawyer::orderBy('full_name')->get();
        $selectedClientId = $request->query('client_id');
        $caseTypes = LegalCase::CASE_TYPES;

        return view('cases.create', compact('clients','lawyers', 'selectedClientId', 'caseTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->requireRole('admin', 'staff');

        $data = $request->validate([
            'case_number' => 'required|unique:cases,case_number',
            'case_title' => 'required|string|max:255',
            'case_type' => 'nullable|in:'.implode(',', LegalCase::CASE_TYPES),
            'case_status' => 'nullable|string',
            'client_id' => 'nullable|exists:clients,id',
            'assigned_lawyer_id' => 'nullable|exists:lawyers,id',
            'date_filed' => 'nullable|date',
            'description' => 'nullable|string',
            'priority_level' => 'nullable|string',
        ]);

        $case = LegalCase::create($data);
        $this->logActivity('Case created', "Created case {$case->case_number} - {$case->case_title}.", $case);

        return redirect()->route('cases.show', $case->id)->with('success','Case created.');
    }

    /**
     * Display the specified resource.
     */
    public function show(LegalCase $case, Request $request)
    {
        $this->authorizeCaseAccess($case);
        $case->load(['client','assignedLawyer','hearings','actions','documents','billings.hearing','billings.payments','opposingParties']);
        $billingSummary = [
            'total_billed' => $case->billings->sum('total_amount'),
            'total_paid' => $case->billings->sum('amount_paid'),
            'balance' => $case->billings->sum('balance'),
            'count' => $case->billings->count(),
        ];

        if ($request->wantsJson()) {
            return response()->json($case);
        }

        return view('cases.show', compact('case', 'billingSummary'));
    }

    public function printSummary(LegalCase $case)
    {
        $this->authorizeCaseAccess($case);
        $case->load(['client','assignedLawyer','hearings','actions','documents','billings.payments','opposingParties']);

        return view('cases.print', compact('case'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LegalCase $case)
    {
        $this->requireRole('admin', 'staff');
        $this->authorizeCaseAccess($case);
        $clients = Client::orderBy('full_name')->get();
        $lawyers = Lawyer::orderBy('full_name')->get();
        $caseTypes = LegalCase::CASE_TYPES;
        return view('cases.edit', compact('case','clients','lawyers', 'caseTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LegalCase $case)
    {
        $this->requireRole('admin', 'staff');
        $this->authorizeCaseAccess($case);

        $data = $request->validate([
            'case_number' => 'required|unique:cases,case_number,'.$case->id,
            'case_title' => 'required|string|max:255',
            'case_type' => 'nullable|in:'.implode(',', LegalCase::CASE_TYPES),
            'case_status' => 'nullable|string',
            'client_id' => 'nullable|exists:clients,id',
            'assigned_lawyer_id' => 'nullable|exists:lawyers,id',
            'date_filed' => 'nullable|date',
            'description' => 'nullable|string',
            'priority_level' => 'nullable|string',
        ]);

        $case->update($data);
        $this->logActivity('Case updated', "Updated case {$case->case_number} - {$case->case_title}.", $case);

        return redirect()->route('cases.show', $case->id)->with('success','Case updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LegalCase $case)
    {
        $this->requireRole('admin');
        $this->logActivity('Case deleted', "Deleted case {$case->case_number} - {$case->case_title}.", $case);
        $case->delete();
        return redirect()->route('cases.index')->with('success','Case deleted.');
    }
}
