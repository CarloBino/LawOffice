<?php

namespace App\Http\Controllers;

use App\Models\OfficeExpense;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OfficeExpenseController extends Controller
{
    public function index(Request $request)
    {
        $this->requireRole('admin', 'staff', 'secretary');

        $expenses = OfficeExpense::orderByRaw("CASE payment_status WHEN 'Unpaid' THEN 1 WHEN 'Partial' THEN 2 ELSE 3 END")
            ->when($request->filled('expense_type'), fn ($query) => $query->where('expense_type', $request->query('expense_type')))
            ->when($request->filled('payment_status'), fn ($query) => $query->where('payment_status', $request->query('payment_status')))
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('due_date', '>=', $request->query('date_from')))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('due_date', '<=', $request->query('date_to')))
            ->orderByRaw('due_date IS NULL')
            ->orderBy('due_date')
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $summary = [
            'total' => OfficeExpense::sum('amount'),
            'unpaid' => OfficeExpense::where('payment_status', 'Unpaid')->sum('amount'),
            'paid' => OfficeExpense::where('payment_status', 'Paid')->sum('amount'),
        ];

        $types = OfficeExpense::query()->whereNotNull('expense_type')->distinct()->orderBy('expense_type')->pluck('expense_type');
        $statuses = OfficeExpense::query()->whereNotNull('payment_status')->distinct()->orderBy('payment_status')->pluck('payment_status');

        return view('office_expenses.index', compact('expenses', 'summary', 'types', 'statuses'));
    }

    public function export(Request $request): StreamedResponse
    {
        $this->requireRole('admin', 'staff', 'secretary');

        $expenses = OfficeExpense::query()
            ->when($request->filled('expense_type'), fn ($query) => $query->where('expense_type', $request->query('expense_type')))
            ->when($request->filled('payment_status'), fn ($query) => $query->where('payment_status', $request->query('payment_status')))
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('due_date', '>=', $request->query('date_from')))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('due_date', '<=', $request->query('date_to')))
            ->orderBy('due_date')
            ->get();

        return response()->streamDownload(function () use ($expenses) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Type', 'Description', 'Amount', 'Due Date', 'Payment Date', 'Status', 'Receipt Number', 'Notes']);
            foreach ($expenses as $expense) {
                fputcsv($handle, [
                    $expense->expense_type,
                    $expense->description,
                    $expense->amount,
                    $expense->due_date,
                    $expense->payment_date,
                    $expense->payment_status,
                    $expense->receipt_number,
                    $expense->notes,
                ]);
            }
            fclose($handle);
        }, 'office-expenses.csv');
    }

    public function create()
    {
        $this->requireRole('admin', 'staff', 'secretary');

        return view('office_expenses.create');
    }

    public function store(Request $request)
    {
        $this->requireRole('admin', 'staff', 'secretary');

        $data = $this->validatedData($request);
        $data = $this->normalizePaymentData($data);

        $expense = OfficeExpense::create($data);
        $this->logActivity('Office expense created', "Created {$expense->expense_type} expense for ".number_format($expense->amount, 2).'.', $expense);

        return redirect()->route('office-expenses.show', $expense)->with('success', 'Office expense recorded.');
    }

    public function show(OfficeExpense $officeExpense)
    {
        $this->requireRole('admin', 'staff', 'secretary');

        return view('office_expenses.show', ['expense' => $officeExpense]);
    }

    public function edit(OfficeExpense $officeExpense)
    {
        $this->requireRole('admin', 'staff', 'secretary');

        return view('office_expenses.edit', ['expense' => $officeExpense]);
    }

    public function update(Request $request, OfficeExpense $officeExpense)
    {
        $this->requireRole('admin', 'staff', 'secretary');

        $data = $this->validatedData($request);
        $data = $this->normalizePaymentData($data);

        $officeExpense->update($data);
        $this->logActivity('Office expense updated', "Updated {$officeExpense->expense_type} expense.", $officeExpense);

        return redirect()->route('office-expenses.show', $officeExpense)->with('success', 'Office expense updated.');
    }

    public function togglePaid(OfficeExpense $officeExpense)
    {
        $this->requireRole('admin', 'staff', 'secretary');
        if ($officeExpense->payment_status === 'Paid') {
            $officeExpense->update([
                'payment_status' => 'Unpaid',
                'payment_date' => null,
            ]);
        } else {
            $officeExpense->update([
                'payment_status' => 'Paid',
                'payment_date' => now()->toDateString(),
            ]);
        }
        $this->logActivity('Office expense status updated', "Marked {$officeExpense->expense_type} as {$officeExpense->payment_status}.", $officeExpense);

        return back()->with('success', 'Office expense payment status updated.');
    }

    public function destroy(OfficeExpense $officeExpense)
    {
        $this->requireRole('admin');
        $this->logActivity('Office expense deleted', "Deleted {$officeExpense->expense_type} expense.", $officeExpense);
        $officeExpense->delete();

        return redirect()->route('office-expenses.index')->with('success', 'Office expense deleted.');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'expense_type' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0',
            'due_date' => 'nullable|date',
            'payment_date' => 'nullable|date',
            'payment_status' => 'nullable|string|max:50',
            'receipt_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);
    }

    private function normalizePaymentData(array $data): array
    {
        $data['payment_status'] = $data['payment_status'] ?? 'Unpaid';

        if ($data['payment_status'] === 'Paid' && empty($data['payment_date'])) {
            $data['payment_date'] = now()->toDateString();
        }

        if ($data['payment_status'] !== 'Paid') {
            $data['payment_date'] = null;
        }

        return $data;
    }
}
