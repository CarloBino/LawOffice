<?php

namespace App\Http\Controllers;

use App\Models\Hearing;
use App\Models\Client;
use App\Models\Lawyer;
use App\Models\LegalCase;
use Illuminate\Http\Request;

class HearingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $sort = $request->query('sort', 'nearest');
        $query = Hearing::with(['case.client', 'case.assignedLawyer'])->select('hearings.*');
        $query->when($this->userIsLawyer(), fn ($query) => $query->whereHas('case', fn ($case) => $this->restrictCasesToCurrentLawyer($case)));

        $query->when($request->filled('status'), fn ($query) => $query->where('hearing_status', $request->query('status')));
        $query->when($request->filled('branch'), fn ($query) => $query->where('court_branch', $request->query('branch')));
        $query->when($request->filled('date_from'), fn ($query) => $query->whereDate('hearing_date', '>=', $request->query('date_from')));
        $query->when($request->filled('date_to'), fn ($query) => $query->whereDate('hearing_date', '<=', $request->query('date_to')));
        $query->when($request->filled('client_id'), fn ($query) => $query->whereHas('case', fn ($case) => $case->where('client_id', $request->query('client_id'))));
        $query->when($request->filled('lawyer_id'), fn ($query) => $query->whereHas('case', fn ($case) => $case->where('assigned_lawyer_id', $request->query('lawyer_id'))));

        match ($sort) {
            'latest' => $query->orderByDesc('hearing_date')->orderByDesc('hearing_time'),
            'status' => $query->orderBy('hearing_status')->orderBy('hearing_date')->orderBy('hearing_time'),
            'branch' => $query->orderBy('court_branch')->orderBy('hearing_date')->orderBy('hearing_time'),
            'lawyer' => $query->leftJoin('cases', 'cases.id', '=', 'hearings.case_id')
                ->leftJoin('lawyers', 'lawyers.id', '=', 'cases.assigned_lawyer_id')
                ->orderBy('lawyers.full_name')
                ->orderBy('hearing_date')
                ->orderBy('hearing_time'),
            'client' => $query->leftJoin('cases', 'cases.id', '=', 'hearings.case_id')
                ->leftJoin('clients', 'clients.id', '=', 'cases.client_id')
                ->orderBy('clients.full_name')
                ->orderBy('hearing_date')
                ->orderBy('hearing_time'),
            default => $query->orderByRaw('hearing_date IS NULL')->orderBy('hearing_date')->orderBy('hearing_time'),
        };

        $hearings = $query->paginate(25)->withQueryString();
        $clients = Client::query()
            ->when($this->userIsLawyer(), fn ($query) => $query->whereHas('cases', fn ($case) => $this->restrictCasesToCurrentLawyer($case)))
            ->orderBy('full_name')
            ->get();
        $lawyers = Lawyer::query()
            ->when($this->userIsLawyer(), fn ($query) => $query->where('id', $this->currentLawyerId()))
            ->orderBy('full_name')
            ->get();
        $statuses = Hearing::query()->whereNotNull('hearing_status')->distinct()->orderBy('hearing_status')->pluck('hearing_status');
        $branches = Hearing::query()->whereNotNull('court_branch')->distinct()->orderBy('court_branch')->pluck('court_branch');

        return view('hearings.index', compact('hearings', 'sort', 'clients', 'lawyers', 'statuses', 'branches'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->requireRole('admin', 'staff');
        $cases = $this->restrictCasesToCurrentLawyer(LegalCase::with(['client', 'assignedLawyer']))
            ->orderBy('case_number')
            ->get();
        return view('hearings.create', compact('cases'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->requireRole('admin', 'staff');
        $data = $this->validatedData($request);
        $this->authorizeCaseAccess(LegalCase::find($data['case_id']));

        $hearing = Hearing::create($data);
        $hearing->load('case');
        $this->logActivity('Hearing created', 'Created hearing for '.(optional($hearing->case)->case_number ?: 'case').'.', $hearing);
        return redirect()->route('hearings.show', $hearing->id)->with('success','Hearing created.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Hearing $hearing)
    {
        $hearing->load(['case.client', 'case.assignedLawyer', 'billings.payments']);
        $this->authorizeCaseAccess($hearing->case);
        $billingSummary = [
            'total_billed' => $hearing->billings->sum('total_amount'),
            'total_paid' => $hearing->billings->sum('amount_paid'),
            'balance' => $hearing->billings->sum('balance'),
            'count' => $hearing->billings->count(),
        ];

        return view('hearings.show', compact('hearing', 'billingSummary'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Hearing $hearing)
    {
        $this->requireRole('admin', 'staff');
        $hearing->load('case');
        $this->authorizeCaseAccess($hearing->case);
        $cases = $this->restrictCasesToCurrentLawyer(LegalCase::with(['client', 'assignedLawyer']))
            ->orderBy('case_number')
            ->get();
        return view('hearings.edit', compact('hearing','cases'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Hearing $hearing)
    {
        $this->requireRole('admin', 'staff');
        $data = $this->validatedData($request);
        $hearing->load('case');
        $this->authorizeCaseAccess($hearing->case);
        $this->authorizeCaseAccess(LegalCase::find($data['case_id']));

        $hearing->update($data);
        $hearing->load('case');
        $this->logActivity('Hearing updated', 'Updated hearing for '.(optional($hearing->case)->case_number ?: 'case').'.', $hearing);
        return redirect()->route('hearings.show', $hearing->id)->with('success','Hearing updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Hearing $hearing)
    {
        $this->requireRole('admin');
        $hearing->load('case');
        $this->logActivity('Hearing deleted', 'Deleted hearing for '.(optional($hearing->case)->case_number ?: 'case').'.', $hearing);
        $hearing->delete();
        return redirect()->route('hearings.index')->with('success','Hearing deleted.');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'case_id' => ['required', 'exists:cases,id'],
            'hearing_date' => ['nullable', 'date_format:Y-m-d', 'before_or_equal:9999-12-31'],
            'hearing_time' => ['nullable', 'date_format:H:i'],
            'court_venue' => ['nullable', 'string'],
            'court_branch' => ['nullable', 'string'],
            'court_jurisdiction' => ['nullable', 'string'],
            'judge_name' => ['nullable', 'string'],
            'hearing_purpose' => ['nullable', 'string'],
            'hearing_status' => ['nullable', 'string'],
        ]);
    }
}
