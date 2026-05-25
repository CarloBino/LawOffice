<?php

namespace App\Http\Controllers;

use App\Models\BillingPayment;
use App\Models\Client;
use App\Models\Hearing;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $sort = $request->query('sort', 'name');
        $query = Client::with('cases.billings')
            ->withCount('cases')
            ->select('clients.*')
            ->selectSub(function (Builder $query) {
                $query->from('billings')
                    ->join('cases', 'cases.id', '=', 'billings.case_id')
                    ->whereColumn('cases.client_id', 'clients.id')
                    ->selectRaw('COALESCE(SUM(billings.balance), 0)');
            }, 'billing_balance');
        $query->when($this->userIsLawyer(), fn ($query) => $query->whereHas('cases', fn ($case) => $this->restrictCasesToCurrentLawyer($case)));

        $query->when($request->filled('client_type'), fn ($query) => $query->where('client_type', $request->query('client_type')));
        $query->when($request->query('balance') === 'with_balance', fn ($query) => $query->whereHas('cases.billings', fn ($billing) => $billing->where('balance', '>', 0)));
        $query->when($request->query('case_status') === 'no_active_case', fn ($query) => $query->whereDoesntHave('cases', fn ($case) => $case->whereNotIn('case_status', ['Closed', 'Archived'])));

        match ($sort) {
            'newest' => $query->latest('clients.created_at'),
            'most_cases' => $query->orderByDesc('cases_count')->orderBy('full_name'),
            'highest_balance' => $query->orderByDesc('billing_balance')->orderBy('full_name'),
            default => $query->orderBy('full_name'),
        };

        $clients = $query->paginate(20)->withQueryString();
        $clientTypes = Client::query()->whereNotNull('client_type')->distinct()->orderBy('client_type')->pluck('client_type');
        return view('clients.index', compact('clients', 'sort', 'clientTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->requireRole('admin', 'staff', 'secretary');
        return view('clients.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->requireRole('admin', 'staff', 'secretary');

        $data = $request->validate([
            'full_name' => 'required|string|max:255',
            'contact_number' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'client_type' => 'nullable|string',
        ]);

        $client = Client::create($data);
        $this->logActivity('Client created', "Created client {$client->full_name}.", $client);

        return redirect()->route('clients.show', $client->id)->with('success','Client created.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Client $client)
    {
        $this->authorizeClientAccess($client);

        $client->load([
            'cases' => fn ($query) => $this->restrictCasesToCurrentLawyer($query),
            'cases.assignedLawyer',
            'cases.billings.case',
            'cases.billings.payments',
        ]);

        $caseIds = $client->cases->pluck('id');
        $billings = $client->cases->flatMap->billings;
        $upcomingHearings = Hearing::with('case')
            ->whereIn('case_id', $caseIds)
            ->whereDate('hearing_date', '>=', now()->toDateString())
            ->orderBy('hearing_date')
            ->orderBy('hearing_time')
            ->take(5)
            ->get();
        $recentPayments = BillingPayment::with('billing.case')
            ->whereHas('billing.case', fn ($query) => $query->where('client_id', $client->id))
            ->latest('date_received')
            ->latest('id')
            ->take(5)
            ->get();
        $stats = [
            'cases' => $client->cases->count(),
            'open_cases' => $client->cases->whereNotIn('case_status', ['Closed', 'Archived'])->count(),
            'total_billed' => $billings->sum('total_amount'),
            'amount_paid' => $billings->sum('amount_paid'),
            'balance' => $billings->sum('balance'),
        ];

        return view('clients.show', compact('client', 'stats', 'billings', 'upcomingHearings', 'recentPayments'));
    }

    public function printStatement(Client $client)
    {
        $this->authorizeClientAccess($client);

        $client->load([
            'cases' => fn ($query) => $this->restrictCasesToCurrentLawyer($query),
            'cases.assignedLawyer',
            'cases.billings.payments',
        ]);

        $billings = $client->cases->flatMap->billings;
        $stats = [
            'total_billed' => $billings->sum('total_amount'),
            'amount_paid' => $billings->sum('amount_paid'),
            'balance' => $billings->sum('balance'),
        ];

        return view('clients.statement', compact('client', 'stats'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Client $client)
    {
        $this->requireRole('admin', 'staff', 'secretary');
        return view('clients.edit', compact('client'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Client $client)
    {
        $this->requireRole('admin', 'staff', 'secretary');

        $data = $request->validate([
            'full_name' => 'required|string|max:255',
            'contact_number' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'client_type' => 'nullable|string',
        ]);

        $client->update($data);
        $this->logActivity('Client updated', "Updated client {$client->full_name}.", $client);

        return redirect()->route('clients.show', $client->id)->with('success','Client updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Client $client)
    {
        $this->requireRole('admin');
        $this->logActivity('Client deleted', "Deleted client {$client->full_name}.", $client);
        $client->delete();
        return redirect()->route('clients.index')->with('success','Client deleted.');
    }

    private function authorizeClientAccess(Client $client): void
    {
        if (! $this->userIsLawyer()) {
            return;
        }

        abort_unless(
            $client->cases()->where('assigned_lawyer_id', $this->currentLawyerId())->exists(),
            403
        );
    }
}
