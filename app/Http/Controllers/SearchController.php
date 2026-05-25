<?php

namespace App\Http\Controllers;

use App\Models\Billing;
use App\Models\BillingPayment;
use App\Models\Client;
use App\Models\Document;
use App\Models\Hearing;
use App\Models\Lawyer;
use App\Models\LegalCase;
use App\Models\OfficeExpense;
use App\Models\OpposingParty;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = trim((string) $request->query('q'));
        $results = collect();

        if ($query !== '') {
            $like = '%'.$query.'%';

            $results = $results
                ->merge($this->clients($like))
                ->merge($this->cases($like))
                ->merge($this->hearings($like))
                ->merge($this->billings($like))
                ->merge($this->payments($like))
                ->merge($this->documents($like))
                ->merge($this->lawyers($like))
                ->merge($this->officeExpenses($like))
                ->merge($this->opposingParties($like));
        }

        return view('search.index', compact('query', 'results'));
    }

    private function clients(string $like)
    {
        return Client::query()
            ->when($this->userIsLawyer(), fn ($query) => $query->whereHas('cases', fn ($case) => $this->restrictCasesToCurrentLawyer($case)))
            ->where(function ($query) use ($like) {
                $query->where('full_name', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhere('contact_number', 'like', $like)
                    ->orWhere('address', 'like', $like);
            })
            ->limit(8)
            ->get()
            ->map(fn (Client $client) => [
                'type' => 'Client',
                'title' => $client->full_name,
                'details' => trim(($client->email ?: 'No email').' / '.($client->contact_number ?: 'No contact')),
                'url' => route('clients.show', $client),
            ]);
    }

    private function cases(string $like)
    {
        return LegalCase::with(['client', 'assignedLawyer'])
            ->when($this->userIsLawyer(), fn ($query) => $this->restrictCasesToCurrentLawyer($query))
            ->where(function ($query) use ($like) {
                $query->where('case_number', 'like', $like)
                    ->orWhere('case_title', 'like', $like)
                    ->orWhere('case_type', 'like', $like)
                    ->orWhere('case_status', 'like', $like);
            })
            ->limit(8)
            ->get()
            ->map(fn (LegalCase $case) => [
                'type' => 'Case',
                'title' => $case->case_number.' - '.$case->case_title,
                'details' => (optional($case->client)->full_name ?: 'No client').' / '.(optional($case->assignedLawyer)->full_name ?: 'No lawyer'),
                'url' => route('cases.show', $case),
            ]);
    }

    private function hearings(string $like)
    {
        return Hearing::with('case.client')
            ->when($this->userIsLawyer(), fn ($query) => $query->whereHas('case', fn ($case) => $this->restrictCasesToCurrentLawyer($case)))
            ->where(function ($query) use ($like) {
                $query->where('court_venue', 'like', $like)
                    ->orWhere('court_branch', 'like', $like)
                    ->orWhere('court_jurisdiction', 'like', $like)
                    ->orWhere('judge_name', 'like', $like)
                    ->orWhere('hearing_purpose', 'like', $like)
                    ->orWhere('hearing_status', 'like', $like)
                    ->orWhere('hearing_date', 'like', $like);
            })
            ->limit(8)
            ->get()
            ->map(fn (Hearing $hearing) => [
                'type' => 'Hearing',
                'title' => optional($hearing->case)->case_title ?: 'Hearing',
                'details' => trim(($hearing->hearing_date ?: 'No date').' / '.($hearing->court_branch ?: $hearing->court_venue ?: 'No venue')),
                'url' => route('hearings.show', $hearing),
            ]);
    }

    private function billings(string $like)
    {
        return Billing::with('case.client')
            ->when($this->userIsLawyer(), fn ($query) => $query->whereHas('case', fn ($case) => $this->restrictCasesToCurrentLawyer($case)))
            ->where(function ($query) use ($like) {
                $query->where('payment_status', 'like', $like)
                    ->orWhere('official_receipt_number', 'like', $like);
            })
            ->limit(8)
            ->get()
            ->map(fn (Billing $billing) => [
                'type' => 'Billing',
                'title' => optional($billing->case)->case_number ?: 'Billing #'.$billing->id,
                'details' => 'Balance '.number_format((float) $billing->balance, 2).' / '.$billing->payment_status,
                'url' => route('billings.show', $billing),
            ]);
    }

    private function payments(string $like)
    {
        return BillingPayment::with('billing.case.client')
            ->when($this->userIsLawyer(), fn ($query) => $query->whereHas('billing.case', fn ($case) => $this->restrictCasesToCurrentLawyer($case)))
            ->where(function ($query) use ($like) {
                $query->where('official_receipt_number', 'like', $like)
                    ->orWhere('notes', 'like', $like)
                    ->orWhere('date_received', 'like', $like);
            })
            ->limit(8)
            ->get()
            ->map(fn (BillingPayment $payment) => [
                'type' => 'Payment',
                'title' => 'Receipt '.($payment->official_receipt_number ?: '#'.$payment->id),
                'details' => number_format((float) $payment->amount, 2).' / '.($payment->date_received ?: 'No date'),
                'url' => route('billings.show', $payment->billing_id),
            ]);
    }

    private function documents(string $like)
    {
        return Document::with('case.client')
            ->when($this->userIsLawyer(), fn ($query) => $query->whereHas('case', fn ($case) => $this->restrictCasesToCurrentLawyer($case)))
            ->where(function ($query) use ($like) {
                $query->where('document_name', 'like', $like)
                    ->orWhere('document_type', 'like', $like);
            })
            ->limit(8)
            ->get()
            ->map(fn (Document $document) => [
                'type' => 'Document',
                'title' => $document->document_name,
                'details' => ($document->document_type ?: 'File').' / '.(optional($document->case)->case_number ?: 'No case'),
                'url' => route('documents.show', $document),
            ]);
    }

    private function lawyers(string $like)
    {
        return Lawyer::query()
            ->when($this->userIsLawyer(), fn ($query) => $query->where('id', $this->currentLawyerId()))
            ->where(function ($query) use ($like) {
                $query->where('full_name', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhere('contact_number', 'like', $like)
                    ->orWhere('specialization', 'like', $like);
            })
            ->limit(8)
            ->get()
            ->map(fn (Lawyer $lawyer) => [
                'type' => 'Lawyer',
                'title' => $lawyer->full_name,
                'details' => ($lawyer->specialization ?: 'No specialization').' / '.($lawyer->status ?: 'No status'),
                'url' => route('lawyers.show', $lawyer),
            ]);
    }

    private function officeExpenses(string $like)
    {
        return OfficeExpense::query()
            ->when($this->userIsLawyer(), fn ($query) => $query->whereRaw('1 = 0'))
            ->where(function ($query) use ($like) {
                $query->where('expense_type', 'like', $like)
                    ->orWhere('description', 'like', $like)
                    ->orWhere('payment_status', 'like', $like)
                    ->orWhere('receipt_number', 'like', $like)
                    ->orWhere('notes', 'like', $like);
            })
            ->limit(8)
            ->get()
            ->map(fn (OfficeExpense $expense) => [
                'type' => 'Expense',
                'title' => $expense->expense_type,
                'details' => number_format((float) $expense->amount, 2).' / '.$expense->payment_status,
                'url' => route('office-expenses.show', $expense),
            ]);
    }

    private function opposingParties(string $like)
    {
        return OpposingParty::with('case.client')
            ->when($this->userIsLawyer(), fn ($query) => $query->whereHas('case', fn ($case) => $this->restrictCasesToCurrentLawyer($case)))
            ->where(function ($query) use ($like) {
                $query->where('opposing_party_name', 'like', $like)
                    ->orWhere('opposing_counsel_name', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhere('contact_number', 'like', $like)
                    ->orWhere('address', 'like', $like);
            })
            ->limit(8)
            ->get()
            ->map(fn (OpposingParty $party) => [
                'type' => 'Opposing Party',
                'title' => $party->opposing_party_name,
                'details' => (optional($party->case)->case_number ?: 'No case').' / '.($party->opposing_counsel_name ?: 'No counsel'),
                'url' => route('opposing-parties.show', $party),
            ]);
    }
}
