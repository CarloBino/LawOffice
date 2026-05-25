<?php

namespace App\Http\Controllers;

use App\Models\Billing;
use App\Models\BillingPayment;
use App\Models\Client;
use App\Models\LegalCase;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BillingController extends Controller
{
    private const FEE_FIELDS = [
        'acceptance_fee',
        'appearance_fee',
        'pleading_fee',
        'notarial_fee',
        'success_fee',
        'retainer_fee',
        'other_fees',
    ];

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $sort = $request->query('sort', 'newest');
        $query = Billing::with(['case.client', 'payments'])->select('billings.*');
        $query->when($this->userIsLawyer(), fn ($query) => $query->whereHas('case', fn ($case) => $this->restrictCasesToCurrentLawyer($case)));

        $query->when($request->filled('payment_status'), fn ($query) => $query->where('payment_status', $request->query('payment_status')));
        $query->when($request->filled('client_id'), fn ($query) => $query->whereHas('case', fn ($case) => $case->where('client_id', $request->query('client_id'))));
        $query->when($request->filled('case_id'), fn ($query) => $query->where('case_id', $request->query('case_id')));
        $query->when($request->query('balance') === 'with_balance', fn ($query) => $query->where('balance', '>', 0));

        match ($sort) {
            'highest_balance' => $query->orderByDesc('balance')->latest('billings.created_at'),
            'unpaid_first' => $query->orderByRaw("CASE payment_status WHEN 'Unpaid' THEN 1 WHEN 'Partial' THEN 2 WHEN 'Paid' THEN 3 ELSE 4 END")->orderByDesc('balance'),
            'latest_payment' => $query->orderByRaw('payment_date IS NULL')->orderByDesc('payment_date')->latest('billings.created_at'),
            'client' => $query->leftJoin('cases', 'cases.id', '=', 'billings.case_id')
                ->leftJoin('clients', 'clients.id', '=', 'cases.client_id')
                ->orderBy('clients.full_name')
                ->latest('billings.created_at'),
            'case' => $query->leftJoin('cases', 'cases.id', '=', 'billings.case_id')
                ->orderBy('cases.case_number')
                ->latest('billings.created_at'),
            default => $query->latest('billings.created_at'),
        };

        $billings = $query->paginate(25)->withQueryString();
        $clients = Client::query()
            ->when($this->userIsLawyer(), fn ($query) => $query->whereHas('cases', fn ($case) => $this->restrictCasesToCurrentLawyer($case)))
            ->orderBy('full_name')
            ->get();
        $cases = $this->restrictCasesToCurrentLawyer(LegalCase::query())
            ->orderBy('case_number')
            ->get();
        $statuses = Billing::query()->whereNotNull('payment_status')->distinct()->orderBy('payment_status')->pluck('payment_status');

        return view('billings.index', compact('billings', 'sort', 'clients', 'cases', 'statuses'));
    }

    public function export(Request $request): StreamedResponse
    {
        $this->requireRole('admin', 'staff', 'secretary');

        $billings = Billing::with('case.client')
            ->when($request->filled('payment_status'), fn ($query) => $query->where('payment_status', $request->query('payment_status')))
            ->when($request->filled('client_id'), fn ($query) => $query->whereHas('case', fn ($case) => $case->where('client_id', $request->query('client_id'))))
            ->when($request->filled('case_id'), fn ($query) => $query->where('case_id', $request->query('case_id')))
            ->latest()
            ->get();

        return response()->streamDownload(function () use ($billings) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Case Number', 'Case Title', 'Client', 'Total', 'Paid', 'Balance', 'Status', 'Last Payment', 'Last Receipt']);
            foreach ($billings as $billing) {
                fputcsv($handle, [
                    optional($billing->case)->case_number,
                    optional($billing->case)->case_title,
                    optional(optional($billing->case)->client)->full_name,
                    $billing->total_amount,
                    $billing->amount_paid,
                    $billing->balance,
                    $billing->payment_status,
                    $billing->payment_date,
                    $billing->official_receipt_number,
                ]);
            }
            fclose($handle);
        }, 'billings.csv');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $this->requireRole('admin', 'staff', 'secretary');

        $cases = LegalCase::with('client')
            ->when($request->query('client_id'), fn ($query, $clientId) => $query->where('client_id', $clientId))
            ->when($request->query('case_id'), fn ($query, $caseId) => $query->where('id', $caseId))
            ->orderBy('case_number')
            ->get();

        $selectedCaseId = $request->query('case_id');

        return view('billings.create', compact('cases', 'selectedCaseId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->requireRole('admin', 'staff', 'secretary');

        $data = $request->validate([
            'case_id' => 'required|exists:cases,id',
            'acceptance_fee' => 'nullable|numeric|min:0',
            'appearance_fee' => 'nullable|numeric|min:0',
            'pleading_fee' => 'nullable|numeric|min:0',
            'notarial_fee' => 'nullable|numeric|min:0',
            'success_fee' => 'nullable|numeric|min:0',
            'retainer_fee' => 'nullable|numeric|min:0',
            'other_fees' => 'nullable|numeric|min:0',
            'payment_amount' => 'nullable|numeric|min:0.01',
            'payment_date_received' => 'nullable|date|required_with:payment_amount',
            'payment_official_receipt_number' => 'nullable|string',
            'payment_notes' => 'nullable|string',
        ]);

        $paymentData = $data;
        $data = $this->prepareBillingData($data);

        $billing = Billing::create($data);
        $this->storeInitialPayment($billing, $paymentData);
        $billing->load('case.client');
        $this->logActivity(
            'Billing created',
            'Created billing for '.(optional($billing->case)->case_number ?: 'case').' with total '.number_format($billing->total_amount, 2).'.',
            $billing
        );

        return redirect()->route('billings.show', $billing->id)->with('success','Billing created.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Billing $billing)
    {
        $billing->load(['case.client', 'payments' => fn ($query) => $query->latest('date_received')->latest('id')]);
        $this->authorizeCaseAccess($billing->case);
        return view('billings.show', compact('billing'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Billing $billing)
    {
        return redirect()->route('billings.show', $billing->id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Billing $billing)
    {
        return redirect()
            ->route('billings.edit', $billing->id)
            ->with('success', 'Billing charges are locked after creation. Record payments below instead.');
    }

    public function storePayment(Request $request, Billing $billing)
    {
        $this->requireRole('admin', 'staff', 'secretary');

        $data = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'date_received' => 'required|date',
            'official_receipt_number' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $payment = $billing->payments()->create($data);
        $billing->recalculatePaymentTotals();
        $this->logActivity(
            'Payment received',
            'Recorded payment of '.number_format($payment->amount, 2).' for billing #'.$billing->id.'.',
            $payment,
            ['billing_id' => $billing->id]
        );

        return redirect()->route('billings.show', $billing)->with('success', 'Payment received recorded.');
    }

    public function destroyPayment(Billing $billing, BillingPayment $payment)
    {
        $this->requireRole('admin', 'staff', 'secretary');
        abort_unless($payment->billing_id === $billing->id, 404);

        $amount = $payment->amount;
        $payment->delete();
        $billing->recalculatePaymentTotals();
        $this->logActivity(
            'Payment removed',
            'Removed payment of '.number_format($amount, 2).' from billing #'.$billing->id.'.',
            $billing
        );

        return redirect()->route('billings.show', $billing)->with('success', 'Payment record removed.');
    }

    public function togglePaid(Billing $billing)
    {
        $this->requireRole('admin', 'staff', 'secretary');
        $billing->load('payments');
        $isPaid = (float) $billing->balance <= 0 || $billing->payment_status === 'Paid';

        if ($isPaid) {
            $billing->payments()->delete();
        } else {
            $remainingBalance = max((float) $billing->total_amount - (float) $billing->payments()->sum('amount'), 0);

            $billing->payments()->create([
                'amount' => $remainingBalance,
                'date_received' => now()->toDateString(),
                'notes' => 'Marked paid from billing shortcut.',
            ]);
        }

        $billing->recalculatePaymentTotals();
        $this->logActivity(
            'Billing status updated',
            'Updated billing #'.$billing->id.' status to '.$billing->payment_status.'.',
            $billing
        );

        return back()->with('success', 'Billing payment status updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Billing $billing)
    {
        $this->requireRole('admin');
        $this->logActivity('Billing deleted', 'Deleted billing #'.$billing->id.'.', $billing);
        $billing->delete();
        return redirect()->route('billings.index')->with('success','Billing deleted.');
    }

    private function prepareBillingData(array $data): array
    {
        foreach (self::FEE_FIELDS as $field) {
            $data[$field] = $data[$field] ?? 0;
        }

        $data['amount_paid'] = $data['amount_paid'] ?? 0;
        $data['total_amount'] = collect(self::FEE_FIELDS)->sum(fn ($field) => (float) $data[$field]);
        $data['balance'] = $data['total_amount'] - $data['amount_paid'];
        $data['payment_status'] = 'Unpaid';

        unset(
            $data['payment_amount'],
            $data['payment_date_received'],
            $data['payment_official_receipt_number'],
            $data['payment_notes']
        );

        return $data;
    }

    private function storeInitialPayment(Billing $billing, array $requestData): void
    {
        if (empty($requestData['payment_amount'])) {
            return;
        }

        $billing->payments()->create([
            'amount' => $requestData['payment_amount'],
            'date_received' => $requestData['payment_date_received'],
            'official_receipt_number' => $requestData['payment_official_receipt_number'] ?? null,
            'notes' => $requestData['payment_notes'] ?? null,
        ]);

        $billing->recalculatePaymentTotals();
    }
}
