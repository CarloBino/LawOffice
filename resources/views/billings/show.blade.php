<x-app-layout>
    <x-slot name="header"><div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between"><div><p class="text-sm font-bold uppercase text-[#9f7957]">Accounts and fees</p><h2 class="mt-2 text-3xl font-extrabold text-[#030203]">Manage Billing</h2></div>@unless(Auth::user()?->isLawyer())<a href="#record-payment" class="inline-flex items-center justify-center bg-[#030203] px-5 py-3 text-sm font-bold text-white transition hover:bg-[#554b45]">Record payment</a>@endunless</div></x-slot>
    <div class="bg-[#eef0ec] py-8"><div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8"><div class="bg-white shadow-sm">
        <div class="border-b border-[#e3e3df] px-6 py-5"><p class="text-sm font-bold uppercase text-[#9f7957]">{{ $billing->payment_status ?: 'Billing record' }}</p><p class="mt-2 text-2xl font-extrabold text-[#030203]">{{ optional($billing->case)->case_number ?: 'No case linked' }}</p><p class="mt-1 text-sm text-[#554b45]">{{ optional(optional($billing->case)->client)->full_name ?: 'No client linked' }}</p></div>
        <div class="grid gap-0 sm:grid-cols-2">
            <div class="border-b border-[#e3e3df] p-5 sm:border-r"><p class="text-xs font-bold uppercase text-[#9f7957]">Total</p><p class="mt-2 font-semibold text-[#030203]">{{ number_format($billing->total_amount, 2) }}</p></div>
            <div class="border-b border-[#e3e3df] p-5"><p class="text-xs font-bold uppercase text-[#9f7957]">Paid</p><p class="mt-2 font-semibold text-[#030203]">{{ number_format($billing->amount_paid, 2) }}</p></div>
            <div class="border-b border-[#e3e3df] p-5 sm:border-r"><p class="text-xs font-bold uppercase text-[#9f7957]">Balance</p><p class="mt-2 font-semibold {{ $billing->balance > 0 ? 'text-red-700' : 'text-emerald-700' }}">{{ number_format($billing->balance, 2) }}</p></div>
            <div class="border-b border-[#e3e3df] p-5"><p class="text-xs font-bold uppercase text-[#9f7957]">Last Receipt</p><p class="mt-2 font-semibold text-[#030203]">{{ $billing->official_receipt_number ?: 'Not recorded' }}</p></div>
            <div class="border-b border-[#e3e3df] p-5 sm:border-r"><p class="text-xs font-bold uppercase text-[#9f7957]">Last Payment Date</p><p class="mt-2 font-semibold text-[#030203]">{{ $billing->payment_date ? \Illuminate\Support\Carbon::parse($billing->payment_date)->format('M d, Y') : 'No payment yet' }}</p></div>
            <div class="border-b border-[#e3e3df] p-5"><p class="text-xs font-bold uppercase text-[#9f7957]">Payment Entries</p><p class="mt-2 font-semibold text-[#030203]">{{ $billing->payments->count() }}</p></div>
        </div>
        <div class="grid gap-0 border-b border-[#e3e3df] sm:grid-cols-2 lg:grid-cols-3">
            <div class="border-b border-[#e3e3df] p-5 sm:border-r lg:border-r"><p class="text-xs font-bold uppercase text-[#9f7957]">Acceptance Fee</p><p class="mt-2 font-semibold text-[#030203]">{{ number_format($billing->acceptance_fee, 2) }}</p></div>
            <div class="border-b border-[#e3e3df] p-5 lg:border-r"><p class="text-xs font-bold uppercase text-[#9f7957]">Appearance Fee</p><p class="mt-2 font-semibold text-[#030203]">{{ number_format($billing->appearance_fee, 2) }}</p></div>
            <div class="border-b border-[#e3e3df] p-5 sm:border-r lg:border-r-0"><p class="text-xs font-bold uppercase text-[#9f7957]">Pleading Fee</p><p class="mt-2 font-semibold text-[#030203]">{{ number_format($billing->pleading_fee, 2) }}</p></div>
            <div class="border-b border-[#e3e3df] p-5 lg:border-r"><p class="text-xs font-bold uppercase text-[#9f7957]">Notarial Fee</p><p class="mt-2 font-semibold text-[#030203]">{{ number_format($billing->notarial_fee, 2) }}</p></div>
            <div class="border-b border-[#e3e3df] p-5 sm:border-r lg:border-r"><p class="text-xs font-bold uppercase text-[#9f7957]">Success Fee</p><p class="mt-2 font-semibold text-[#030203]">{{ number_format($billing->success_fee, 2) }}</p></div>
            <div class="border-b border-[#e3e3df] p-5"><p class="text-xs font-bold uppercase text-[#9f7957]">Retainer Fee</p><p class="mt-2 font-semibold text-[#030203]">{{ number_format($billing->retainer_fee, 2) }}</p></div>
            <div class="p-5"><p class="text-xs font-bold uppercase text-[#9f7957]">Others</p><p class="mt-2 font-semibold text-[#030203]">{{ number_format($billing->other_fees, 2) }}</p></div>
        </div>
        @unless(Auth::user()?->isLawyer())
            <div id="record-payment" class="border-b border-[#e3e3df] px-6 py-5">
                <p class="text-sm font-bold uppercase text-[#9f7957]">Record payment received</p>
                <form method="POST" action="{{ route('billings.payments.store', $billing->id) }}" class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @csrf
                    <div><label class="mb-2 block text-sm font-bold uppercase text-[#554b45]">Amount Received</label><input type="number" step="0.01" min="0.01" name="amount" required class="w-full border-[#c1c1bd] bg-white text-[#030203] focus:border-[#9f7957] focus:ring-[#9f7957]"></div>
                    <div><label class="mb-2 block text-sm font-bold uppercase text-[#554b45]">Date Received</label><input type="date" name="date_received" value="{{ now()->toDateString() }}" required class="w-full border-[#c1c1bd] bg-white text-[#030203] focus:border-[#9f7957] focus:ring-[#9f7957]"></div>
                    <div><label class="mb-2 block text-sm font-bold uppercase text-[#554b45]">Receipt Number</label><input type="text" name="official_receipt_number" class="w-full border-[#c1c1bd] bg-white text-[#030203] focus:border-[#9f7957] focus:ring-[#9f7957]"></div>
                    <div><label class="mb-2 block text-sm font-bold uppercase text-[#554b45]">Notes</label><input type="text" name="notes" class="w-full border-[#c1c1bd] bg-white text-[#030203] focus:border-[#9f7957] focus:ring-[#9f7957]"></div>
                    <div class="sm:col-span-2 lg:col-span-4"><button class="bg-[#030203] px-5 py-3 text-sm font-bold text-white transition hover:bg-[#554b45]">Add payment</button></div>
                </form>
            </div>
        @endunless
        <div class="border-b border-[#e3e3df] px-6 py-5">
            <p class="text-sm font-bold uppercase text-[#9f7957]">Payment history</p>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-[#e3e3df]">
                    <thead><tr class="text-left text-xs font-bold uppercase text-[#554b45]"><th class="py-3 pr-4">Date Received</th><th class="py-3 pr-4">Amount</th><th class="py-3 pr-4">Receipt</th><th class="py-3 pr-4">Notes</th><th class="py-3 text-right">Action</th></tr></thead>
                    <tbody class="divide-y divide-[#e3e3df]">
                        @forelse($billing->payments as $payment)
                            <tr>
                                <td class="py-3 pr-4 text-sm text-[#030203]">{{ \Illuminate\Support\Carbon::parse($payment->date_received)->format('M d, Y') }}</td>
                                <td class="py-3 pr-4 text-sm font-bold text-[#030203]">{{ number_format($payment->amount, 2) }}</td>
                                <td class="py-3 pr-4 text-sm text-[#554b45]">{{ $payment->official_receipt_number ?: 'Not recorded' }}</td>
                                <td class="py-3 pr-4 text-sm text-[#554b45]">{{ $payment->notes ?: 'None' }}</td>
                                <td class="py-3 text-right">
                                    @unless(Auth::user()?->isLawyer())
                                        <form method="POST" action="{{ route('billings.payments.destroy', [$billing->id, $payment->id]) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button class="text-sm font-bold text-red-700 hover:text-[#030203]" onclick="return confirm('Remove this payment record?')">Remove</button>
                                        </form>
                                    @else
                                        <span class="text-sm text-[#7a716b]">View only</span>
                                    @endunless
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-6 text-center text-sm text-[#554b45]">No payments recorded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="flex items-center justify-between px-6 py-5">
            <a href="{{ route('billings.index') }}" class="text-sm font-bold text-[#554b45] hover:text-[#030203]">Back to billings</a>
            @unless(Auth::user()?->isLawyer())
                <div class="flex items-center gap-4">
                    <form method="POST" action="{{ route('billings.toggle-paid', $billing->id) }}">
                        @csrf
                        @method('PATCH')
                        <button class="bg-[#c7a47b] px-4 py-2 text-sm font-bold text-[#030203] hover:bg-[#030203] hover:text-white" onclick="return confirm('{{ $billing->balance > 0 ? 'Mark this billing as paid?' : 'Mark this billing as unpaid?' }}')">
                            {{ $billing->balance > 0 ? 'Mark paid' : 'Mark unpaid' }}
                        </button>
                    </form>
                </div>
            @endunless
        </div>
    </div></div></div>
</x-app-layout>
