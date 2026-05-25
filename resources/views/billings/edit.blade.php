<x-app-layout>
    <x-slot name="header"><div><p class="text-sm font-bold uppercase text-[#9f7957]">Accounts and fees</p><h2 class="mt-2 text-3xl font-extrabold text-[#030203]">Manage Billing</h2></div></x-slot>
    <div class="bg-[#eef0ec] py-8"><div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        @if ($errors->any())<div class="mb-6 border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ $errors->first() }}</div>@endif
        <div class="bg-white p-6 shadow-sm">
            <div class="mb-5 border border-[#e3e3df] bg-[#f8f8f6] px-4 py-3 text-sm font-semibold text-[#554b45]">Billing charges are locked after creation.</div>
            <div class="grid gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2"><p class="mb-2 text-sm font-bold uppercase text-[#554b45]">Case / Client</p><div class="border border-[#c1c1bd] bg-white px-4 py-3 text-[#030203]">{{ optional($billing->case)->case_number }} - {{ optional($billing->case)->case_title }} @if(optional($billing->case)->client) / {{ $billing->case->client->full_name }} @endif</div></div>
                <div><p class="mb-2 text-sm font-bold uppercase text-[#554b45]">Acceptance Fee</p><div class="border border-[#c1c1bd] bg-white px-4 py-3 text-[#030203]">{{ number_format($billing->acceptance_fee, 2) }}</div></div>
                <div><p class="mb-2 text-sm font-bold uppercase text-[#554b45]">Appearance Fee</p><div class="border border-[#c1c1bd] bg-white px-4 py-3 text-[#030203]">{{ number_format($billing->appearance_fee, 2) }}</div></div>
                <div><p class="mb-2 text-sm font-bold uppercase text-[#554b45]">Pleading Fee</p><div class="border border-[#c1c1bd] bg-white px-4 py-3 text-[#030203]">{{ number_format($billing->pleading_fee, 2) }}</div></div>
                <div><p class="mb-2 text-sm font-bold uppercase text-[#554b45]">Notarial Fee</p><div class="border border-[#c1c1bd] bg-white px-4 py-3 text-[#030203]">{{ number_format($billing->notarial_fee, 2) }}</div></div>
                <div><p class="mb-2 text-sm font-bold uppercase text-[#554b45]">Success Fee</p><div class="border border-[#c1c1bd] bg-white px-4 py-3 text-[#030203]">{{ number_format($billing->success_fee, 2) }}</div></div>
                <div><p class="mb-2 text-sm font-bold uppercase text-[#554b45]">Retainer Fee</p><div class="border border-[#c1c1bd] bg-white px-4 py-3 text-[#030203]">{{ number_format($billing->retainer_fee, 2) }}</div></div>
                <div><p class="mb-2 text-sm font-bold uppercase text-[#554b45]">Others</p><div class="border border-[#c1c1bd] bg-white px-4 py-3 text-[#030203]">{{ number_format($billing->other_fees, 2) }}</div></div>
                <div><p class="mb-2 text-sm font-bold uppercase text-[#554b45]">Total Charges</p><div class="border border-[#c1c1bd] bg-white px-4 py-3 font-bold text-[#030203]">{{ number_format($billing->total_amount, 2) }}</div></div>
            </div>
            <div class="mt-6 flex items-center justify-between border-t border-[#e3e3df] pt-5"><a href="{{ route('billings.index') }}" class="text-sm font-bold text-[#554b45] hover:text-[#030203]">Back to billings</a></div>
        </div>

        <div class="mt-6 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-sm font-bold uppercase text-[#9f7957]">Payments received</p>
                    <p class="mt-1 text-sm text-[#554b45]">Paid: {{ number_format($billing->amount_paid, 2) }} / Balance: {{ number_format($billing->balance, 2) }}</p>
                </div>
                <a href="{{ route('billings.show', $billing->id) }}" class="text-sm font-bold text-[#9f7957] hover:text-[#030203]">View billing details</a>
            </div>

            <form method="POST" action="{{ route('billings.payments.store', $billing->id) }}" class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @csrf
                <div><label class="mb-2 block text-sm font-bold uppercase text-[#554b45]">Amount Received</label><input type="number" step="0.01" min="0.01" name="amount" required class="w-full border-[#c1c1bd] bg-white text-[#030203] focus:border-[#9f7957] focus:ring-[#9f7957]"></div>
                <div><label class="mb-2 block text-sm font-bold uppercase text-[#554b45]">Date Received</label><input type="date" name="date_received" value="{{ now()->toDateString() }}" required class="w-full border-[#c1c1bd] bg-white text-[#030203] focus:border-[#9f7957] focus:ring-[#9f7957]"></div>
                <div><label class="mb-2 block text-sm font-bold uppercase text-[#554b45]">Receipt Number</label><input type="text" name="official_receipt_number" class="w-full border-[#c1c1bd] bg-white text-[#030203] focus:border-[#9f7957] focus:ring-[#9f7957]"></div>
                <div><label class="mb-2 block text-sm font-bold uppercase text-[#554b45]">Notes</label><input type="text" name="notes" class="w-full border-[#c1c1bd] bg-white text-[#030203] focus:border-[#9f7957] focus:ring-[#9f7957]"></div>
                <div class="sm:col-span-2 lg:col-span-4"><button class="bg-[#030203] px-5 py-3 text-sm font-bold text-white transition hover:bg-[#554b45]">Add payment</button></div>
            </form>

            <div class="mt-6 overflow-x-auto">
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
                                    <form method="POST" action="{{ route('billings.payments.destroy', [$billing->id, $payment->id]) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-sm font-bold text-red-700 hover:text-[#030203]" onclick="return confirm('Remove this payment record?')">Remove</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-6 text-center text-sm text-[#554b45]">No payments recorded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div></div>
</x-app-layout>
