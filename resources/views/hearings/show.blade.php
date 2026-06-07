<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-bold uppercase text-[#9f7957]">Court calendar</p>
                <h2 class="mt-2 text-3xl font-extrabold text-[#030203]">Hearing</h2>
            </div>
            @unless(Auth::user()?->isLawyer())
                <a href="{{ route('hearings.edit', $hearing->id) }}" class="inline-flex items-center justify-center bg-[#030203] px-4 py-2 text-sm font-bold text-white transition hover:bg-[#554b45]">Edit hearing</a>
            @endunless
        </div>
    </x-slot>

    <div class="bg-[#eef0ec] py-8">
        <div class="mx-auto max-w-5xl space-y-6 px-4 sm:px-6 lg:px-8">
            <section class="bg-white px-6 py-5 shadow-sm">
                <div class="grid gap-5 md:grid-cols-[1.2fr_.8fr]">
                    <div>
                        <p class="text-xs font-bold uppercase text-[#9f7957]">{{ $hearing->hearing_status ?: 'Scheduled' }}</p>
                        <h3 class="mt-1 text-2xl font-extrabold text-[#030203]">{{ optional($hearing->case)->case_title ?: 'Unassigned hearing' }}</h3>
                        <p class="mt-2 text-sm text-[#554b45]">{{ optional($hearing->case)->case_number ?: 'No case number' }}</p>
                    </div>
                    <div class="border-t border-[#d1d2cd] pt-5 md:border-l md:border-t-0 md:pl-6 md:pt-0">
                        <p class="text-xs font-bold uppercase text-[#9f7957]">Schedule</p>
                        <p class="mt-2 text-xl font-extrabold text-[#030203]">{{ $hearing->hearing_date ? \Illuminate\Support\Carbon::parse($hearing->hearing_date)->format('M d, Y') : 'TBA' }}</p>
                        <p class="mt-1 text-sm text-[#554b45]">{{ $hearing->hearing_time ? \Illuminate\Support\Carbon::parse($hearing->hearing_time)->format('g:i A') : 'Time pending' }}</p>
                    </div>
                </div>
            </section>

            <section class="bg-white p-6 shadow-sm">
                <div class="grid gap-5 sm:grid-cols-2">
                    <div><p class="text-xs font-bold uppercase text-[#9f7957]">Client</p><p class="mt-2 font-semibold text-[#030203]">{{ optional(optional($hearing->case)->client)->full_name ?: 'No client linked' }}</p></div>
                    <div><p class="text-xs font-bold uppercase text-[#9f7957]">Assigned Lawyer</p><p class="mt-2 font-semibold text-[#030203]">{{ $hearing->case?->assignedLawyer?->display_name ?: 'No lawyer assigned' }}</p></div>
                    <div><p class="text-xs font-bold uppercase text-[#9f7957]">Venue</p><p class="mt-2 font-semibold text-[#030203]">{{ $hearing->court_venue ?: 'Not recorded' }}</p></div>
                    <div><p class="text-xs font-bold uppercase text-[#9f7957]">Branch</p><p class="mt-2 font-semibold text-[#030203]">{{ $hearing->court_branch ?: 'Not recorded' }}</p></div>
                    <div><p class="text-xs font-bold uppercase text-[#9f7957]">Judge</p><p class="mt-2 font-semibold text-[#030203]">{{ $hearing->judge_name ?: 'Not recorded' }}</p></div>
                    <div><p class="text-xs font-bold uppercase text-[#9f7957]">Jurisdiction</p><p class="mt-2 font-semibold text-[#030203]">{{ $hearing->court_jurisdiction ?: 'Not recorded' }}</p></div>
                    <div class="sm:col-span-2"><p class="text-xs font-bold uppercase text-[#9f7957]">Purpose</p><p class="mt-2 whitespace-pre-line text-[#030203]">{{ $hearing->hearing_purpose ?: 'No purpose recorded.' }}</p></div>
                </div>
            </section>

            <section class="bg-white shadow-sm">
                <div class="border-b border-[#e3e3df] px-5 py-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <h3 class="text-sm font-bold uppercase text-[#554b45]">Hearing billing</h3>
                            <p class="mt-1 text-sm text-[#7a716b]">Amounts listed here are billing records linked directly to this hearing.</p>
                        </div>
                        @unless(Auth::user()?->isLawyer())
                            <a href="{{ route('billings.create', ['hearing_id' => $hearing->id]) }}" class="text-sm font-bold text-[#9f7957] hover:text-[#030203]">New billing</a>
                        @endunless
                    </div>
                </div>
                <div class="grid gap-0 border-b border-[#e3e3df] sm:grid-cols-2 lg:grid-cols-4">
                    <div class="border-b border-[#e3e3df] p-5 sm:border-r lg:border-b-0"><p class="text-xs font-bold uppercase text-[#9f7957]">Billing Records</p><p class="mt-2 text-xl font-extrabold text-[#030203]">{{ number_format($billingSummary['count']) }}</p></div>
                    <div class="border-b border-[#e3e3df] p-5 lg:border-b-0 lg:border-r"><p class="text-xs font-bold uppercase text-[#9f7957]">Total Billed</p><p class="mt-2 text-xl font-extrabold text-[#030203]">{{ number_format($billingSummary['total_billed'], 2) }}</p></div>
                    <div class="border-b border-[#e3e3df] p-5 sm:border-r sm:border-b-0"><p class="text-xs font-bold uppercase text-[#9f7957]">Total Paid</p><p class="mt-2 text-xl font-extrabold text-emerald-700">{{ number_format($billingSummary['total_paid'], 2) }}</p></div>
                    <div class="p-5"><p class="text-xs font-bold uppercase text-[#9f7957]">Outstanding Balance</p><p class="mt-2 text-xl font-extrabold {{ $billingSummary['balance'] > 0 ? 'text-red-700' : 'text-emerald-700' }}">{{ number_format($billingSummary['balance'], 2) }}</p></div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-[#e3e3df]">
                        <thead><tr class="text-left text-xs font-bold uppercase text-[#7a716b]"><th class="px-5 py-3">Status</th><th class="px-5 py-3">Billed</th><th class="px-5 py-3">Paid</th><th class="px-5 py-3">Balance</th><th class="px-5 py-3 text-right">Action</th></tr></thead>
                        <tbody class="divide-y divide-[#e3e3df]">
                            @forelse($hearing->billings as $billing)
                                <tr>
                                    <td class="px-5 py-4 text-sm font-semibold text-[#030203]">{{ $billing->payment_status ?: 'Unpaid' }}</td>
                                    <td class="px-5 py-4 text-sm font-semibold text-[#030203]">{{ number_format($billing->total_amount, 2) }}</td>
                                    <td class="px-5 py-4 text-sm font-semibold text-[#030203]">{{ number_format($billing->amount_paid, 2) }}</td>
                                    <td class="px-5 py-4 text-sm font-semibold {{ $billing->balance > 0 ? 'text-red-700' : 'text-emerald-700' }}">{{ number_format($billing->balance, 2) }}</td>
                                    <td class="px-5 py-4 text-right"><a href="{{ route('billings.show', $billing->id) }}" class="text-sm font-bold text-[#9f7957] hover:text-[#030203]">{{ Auth::user()?->isLawyer() ? 'View' : 'Manage' }}</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-5 py-8 text-center text-sm text-[#554b45]">No billing linked to this hearing yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <div class="flex items-center justify-between">
                <a href="{{ route('hearings.index') }}" class="text-sm font-bold text-[#554b45] hover:text-[#030203]">Back to hearings</a>
                @if($hearing->case)
                    <a href="{{ route('cases.show', $hearing->case_id) }}" class="text-sm font-bold text-[#9f7957] hover:text-[#030203]">Open case</a>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
