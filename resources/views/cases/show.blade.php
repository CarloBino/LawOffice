<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-bold uppercase text-[#9f7957]">Matter record</p>
                <h2 class="mt-2 text-3xl font-extrabold text-[#030203]">{{ $case->case_number }}</h2>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('cases.print', $case->id) }}" target="_blank" class="inline-flex items-center justify-center border border-[#c1c1bd] bg-white px-5 py-3 text-sm font-bold text-[#030203] transition hover:border-[#9f7957]">Print summary</a>
                <a href="{{ route('cases.edit', $case->id) }}" class="inline-flex items-center justify-center bg-[#030203] px-5 py-3 text-sm font-bold text-white transition hover:bg-[#554b45]">Edit case</a>
            </div>
        </div>
    </x-slot>

    <div class="bg-[#eef0ec] py-8">
        <div class="mx-auto max-w-6xl space-y-6 px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm">
                <div class="border-b border-[#e3e3df] px-6 py-5">
                    <p class="text-sm font-bold uppercase text-[#9f7957]">{{ $case->case_status }} | {{ $case->priority_level }}</p>
                    <p class="mt-2 text-2xl font-extrabold text-[#030203]">{{ $case->case_title }}</p>
                </div>
                <div class="grid gap-0 sm:grid-cols-2">
                    <div class="border-b border-[#e3e3df] p-5 sm:border-r"><p class="text-xs font-bold uppercase text-[#9f7957]">Client</p><p class="mt-2 font-semibold text-[#030203]">{{ optional($case->client)->full_name ?: 'Unassigned' }}</p></div>
                    <div class="border-b border-[#e3e3df] p-5"><p class="text-xs font-bold uppercase text-[#9f7957]">Assigned Lawyer</p><p class="mt-2 font-semibold text-[#030203]">{{ optional($case->assignedLawyer)->full_name ?: 'Unassigned' }}</p></div>
                    <div class="border-b border-[#e3e3df] p-5 sm:border-r"><p class="text-xs font-bold uppercase text-[#9f7957]">Date Filed</p><p class="mt-2 font-semibold text-[#030203]">{{ $case->date_filed ? \Illuminate\Support\Carbon::parse($case->date_filed)->format('M d, Y') : 'Not recorded' }}</p></div>
                    <div class="border-b border-[#e3e3df] p-5"><p class="text-xs font-bold uppercase text-[#9f7957]">Case Type</p><p class="mt-2 font-semibold text-[#030203]">{{ $case->case_type ?: 'Not recorded' }}</p></div>
                    <div class="border-b border-[#e3e3df] p-5 sm:col-span-2"><p class="text-xs font-bold uppercase text-[#9f7957]">Description</p><p class="mt-2 whitespace-pre-line text-[#030203]">{{ $case->description ?: 'No description recorded.' }}</p></div>
                </div>
                <div class="flex items-center justify-between px-6 py-5">
                    <a href="{{ route('cases.index') }}" class="text-sm font-bold text-[#554b45] hover:text-[#030203]">Back to cases</a>
                    @if($case->client)<a href="{{ route('clients.show', $case->client_id) }}" class="text-sm font-bold text-[#9f7957] hover:text-[#030203]">Open client</a>@endif
                </div>
            </div>

            <section class="bg-white shadow-sm">
                <div class="border-b border-[#e3e3df] px-5 py-4">
                    <div>
                        <h3 class="text-sm font-bold uppercase text-[#554b45]">Billings for this case</h3>
                        <p class="mt-1 text-sm text-[#7a716b]">These amounts belong only to {{ $case->case_number }}. Add new billing from the client profile or Billings page.</p>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-[#e3e3df]">
                        <thead><tr class="text-left text-xs font-bold uppercase text-[#7a716b]"><th class="px-5 py-3">Status</th><th class="px-5 py-3">Billed</th><th class="px-5 py-3">Paid</th><th class="px-5 py-3">Balance</th><th class="px-5 py-3">Payments</th><th class="px-5 py-3 text-right">Action</th></tr></thead>
                        <tbody class="divide-y divide-[#e3e3df]">
                            @forelse($case->billings as $billing)
                                <tr>
                                    <td class="px-5 py-4 text-sm font-semibold text-[#030203]">{{ $billing->payment_status ?: 'Unpaid' }}</td>
                                    <td class="px-5 py-4 text-sm font-semibold text-[#030203]">{{ number_format($billing->total_amount, 2) }}</td>
                                    <td class="px-5 py-4 text-sm font-semibold text-[#030203]">{{ number_format($billing->amount_paid, 2) }}</td>
                                    <td class="px-5 py-4 text-sm font-semibold {{ $billing->balance > 0 ? 'text-red-700' : 'text-emerald-700' }}">{{ number_format($billing->balance, 2) }}</td>
                                    <td class="px-5 py-4 text-sm text-[#554b45]">{{ $billing->payments->count() }}</td>
                                    <td class="px-5 py-4 text-right"><a href="{{ route('billings.show', $billing->id) }}" class="text-sm font-bold text-[#9f7957] hover:text-[#030203]">Manage</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-5 py-8 text-center text-sm text-[#554b45]">No billing recorded for this case yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
