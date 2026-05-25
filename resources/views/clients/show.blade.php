<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-bold uppercase text-[#9f7957]">Client profile</p>
                <h2 class="mt-2 text-3xl font-extrabold text-[#030203]">{{ $client->full_name }}</h2>
            </div>
            <div class="flex flex-wrap gap-3">
                @unless(Auth::user()?->isLawyer())
                    <a href="{{ route('cases.create', ['client_id' => $client->id]) }}" class="inline-flex items-center justify-center bg-[#030203] px-4 py-2 text-sm font-bold text-white transition hover:bg-[#554b45]">New case</a>
                    <a href="{{ route('billings.create', ['client_id' => $client->id]) }}" class="inline-flex items-center justify-center border border-[#c1c1bd] bg-white px-4 py-2 text-sm font-bold text-[#030203] transition hover:border-[#9f7957]">New billing</a>
                @endunless
                <a href="{{ route('clients.statement', $client->id) }}" target="_blank" class="inline-flex items-center justify-center border border-[#c1c1bd] bg-white px-4 py-2 text-sm font-bold text-[#030203] transition hover:border-[#9f7957]">Print statement</a>
                @unless(Auth::user()?->isLawyer())
                    <a href="{{ route('clients.edit', $client->id) }}" class="inline-flex items-center justify-center border border-transparent px-4 py-2 text-sm font-bold text-[#554b45] transition hover:text-[#030203]">Edit</a>
                @endunless
            </div>
        </div>
    </x-slot>

    <div class="bg-[#eef0ec] py-8">
        <div class="mx-auto max-w-6xl space-y-6 px-4 sm:px-6 lg:px-8">
            <section class="bg-white px-6 py-5 shadow-sm">
                <div class="grid gap-5 md:grid-cols-[1.4fr_1fr]">
                    <div>
                        <p class="text-xs font-bold uppercase text-[#9f7957]">{{ $client->client_type ?? 'Client' }}</p>
                        <h3 class="mt-1 text-2xl font-extrabold text-[#030203]">{{ $client->full_name }}</h3>
                        <div class="mt-4 grid gap-3 text-sm text-[#554b45] sm:grid-cols-3">
                            <p><span class="block text-xs font-bold uppercase text-[#9f7957]">Contact</span>{{ $client->contact_number ?: 'Not recorded' }}</p>
                            <p><span class="block text-xs font-bold uppercase text-[#9f7957]">Email</span>{{ $client->email ?: 'Not recorded' }}</p>
                            <p><span class="block text-xs font-bold uppercase text-[#9f7957]">Address</span>{{ $client->address ?: 'Not recorded' }}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-x-6 gap-y-4 border-t border-[#d1d2cd] pt-5 md:border-l md:border-t-0 md:pl-6 md:pt-0">
                        <div><p class="text-xs font-bold uppercase text-[#9f7957]">Cases</p><p class="mt-1 text-xl font-extrabold text-[#030203]">{{ $stats['cases'] }}</p></div>
                        <div><p class="text-xs font-bold uppercase text-[#9f7957]">Open</p><p class="mt-1 text-xl font-extrabold text-[#030203]">{{ $stats['open_cases'] }}</p></div>
                        <div><p class="text-xs font-bold uppercase text-[#9f7957]">Billed</p><p class="mt-1 text-xl font-extrabold text-[#030203]">{{ number_format($stats['total_billed'], 2) }}</p></div>
                        <div><p class="text-xs font-bold uppercase text-[#9f7957]">Balance</p><p class="mt-1 text-xl font-extrabold {{ $stats['balance'] > 0 ? 'text-red-700' : 'text-emerald-700' }}">{{ number_format($stats['balance'], 2) }}</p></div>
                    </div>
                </div>
            </section>

            <div class="grid gap-6 lg:grid-cols-[1.15fr_.85fr]">
                <section class="bg-white shadow-sm">
                    <div class="flex items-center justify-between border-b border-[#d1d2cd] px-5 py-4">
                        <h3 class="text-sm font-bold uppercase text-[#554b45]">Cases</h3>
                        @unless(Auth::user()?->isLawyer())
                            <a href="{{ route('cases.create', ['client_id' => $client->id]) }}" class="text-sm font-bold text-[#9f7957] hover:text-[#030203]">New case</a>
                        @endunless
                    </div>
                    <div class="divide-y divide-[#e3e3df]">
                        @forelse($client->cases as $case)
                            <div class="px-5 py-4">
                                <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                    <div>
                                        <a href="{{ route('cases.show', $case->id) }}" class="font-bold text-[#030203] hover:text-[#9f7957]">{{ $case->case_number }}</a>
                                        <p class="mt-1 text-sm text-[#554b45]">{{ $case->case_title }}</p>
                                    </div>
                                    <p class="text-sm font-semibold text-[#554b45]">{{ $case->case_status ?: 'No status' }}</p>
                                </div>
                                <p class="mt-2 text-sm text-[#7a716b]">{{ $case->case_type ?: 'No type' }} / {{ optional($case->assignedLawyer)->full_name ?: 'No lawyer assigned' }}</p>
                            </div>
                        @empty
                            <div class="px-5 py-8 text-center text-sm text-[#554b45]">No cases linked to this client yet.</div>
                        @endforelse
                    </div>
                </section>

                <section class="bg-white shadow-sm">
                    <div class="border-b border-[#d1d2cd] px-5 py-4">
                        <h3 class="text-sm font-bold uppercase text-[#554b45]">Upcoming hearings</h3>
                    </div>
                    <div class="divide-y divide-[#e3e3df]">
                        @forelse($upcomingHearings as $hearing)
                            <div class="px-5 py-4">
                                <p class="font-bold text-[#030203]">{{ \Illuminate\Support\Carbon::parse($hearing->hearing_date)->format('M d, Y') }} @if($hearing->hearing_time) <span class="font-semibold text-[#554b45]">{{ \Illuminate\Support\Carbon::parse($hearing->hearing_time)->format('g:i A') }}</span> @endif</p>
                                <p class="mt-1 text-sm text-[#554b45]">{{ optional($hearing->case)->case_number }} / {{ $hearing->hearing_purpose ?: 'Hearing' }}</p>
                                <p class="mt-1 text-sm text-[#7a716b]">{{ $hearing->court_venue ?: 'Venue not recorded' }}</p>
                            </div>
                        @empty
                            <div class="px-5 py-8 text-center text-sm text-[#554b45]">No upcoming hearings.</div>
                        @endforelse
                    </div>
                </section>
            </div>

            <div class="grid gap-6 lg:grid-cols-[1.15fr_.85fr]">
                <section class="bg-white shadow-sm">
                    <div class="flex items-center justify-between border-b border-[#d1d2cd] px-5 py-4">
                        <div>
                            <h3 class="text-sm font-bold uppercase text-[#554b45]">Billings</h3>
                            <p class="mt-1 text-sm text-[#7a716b]">This client total comes from the billings recorded under each case.</p>
                        </div>
                        @unless(Auth::user()?->isLawyer())
                            <a href="{{ route('billings.create', ['client_id' => $client->id]) }}" class="text-sm font-bold text-[#9f7957] hover:text-[#030203]">New billing</a>
                        @endunless
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-[#e3e3df]">
                            <thead><tr class="text-left text-xs font-bold uppercase text-[#7a716b]"><th class="px-5 py-3">Case</th><th class="px-5 py-3">Billed</th><th class="px-5 py-3">Paid</th><th class="px-5 py-3">Balance</th><th class="px-5 py-3 text-right">Action</th></tr></thead>
                            <tbody class="divide-y divide-[#e3e3df]">
                                @forelse($billings as $billing)
                                    <tr>
                                        <td class="px-5 py-4 text-sm text-[#030203]">
                                            <p class="font-semibold">{{ optional($billing->case)->case_number }}</p>
                                            <p class="mt-1 text-[#7a716b]">{{ optional($billing->case)->case_title }}</p>
                                        </td>
                                        <td class="px-5 py-4 text-sm font-semibold text-[#030203]">{{ number_format($billing->total_amount, 2) }}</td>
                                        <td class="px-5 py-4 text-sm font-semibold text-[#030203]">{{ number_format($billing->amount_paid, 2) }}</td>
                                        <td class="px-5 py-4 text-sm font-semibold {{ $billing->balance > 0 ? 'text-red-700' : 'text-emerald-700' }}">{{ number_format($billing->balance, 2) }}</td>
                                        <td class="px-5 py-4 text-right"><a href="{{ route('billings.show', $billing->id) }}" class="text-sm font-bold text-[#9f7957] hover:text-[#030203]">Manage</a></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="px-5 py-8 text-center text-sm text-[#554b45]">No billings recorded.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="bg-white shadow-sm">
                    <div class="border-b border-[#d1d2cd] px-5 py-4">
                        <h3 class="text-sm font-bold uppercase text-[#554b45]">Recent payments</h3>
                    </div>
                    <div class="divide-y divide-[#e3e3df]">
                        @forelse($recentPayments as $payment)
                            <div class="flex items-start justify-between gap-4 px-5 py-4">
                                <div>
                                    <p class="font-bold text-[#030203]">{{ number_format($payment->amount, 2) }}</p>
                                    <p class="mt-1 text-sm text-[#554b45]">{{ \Illuminate\Support\Carbon::parse($payment->date_received)->format('M d, Y') }} / {{ $payment->official_receipt_number ?: 'No receipt' }}</p>
                                    <p class="mt-1 text-sm text-[#7a716b]">{{ optional(optional($payment->billing)->case)->case_number }}</p>
                                </div>
                                <a href="{{ route('billings.show', $payment->billing_id) }}" class="text-sm font-bold text-[#9f7957] hover:text-[#030203]">Open</a>
                            </div>
                        @empty
                            <div class="px-5 py-8 text-center text-sm text-[#554b45]">No payments received.</div>
                        @endforelse
                    </div>
                </section>
            </div>

            <div class="flex items-center justify-between">
                <a href="{{ route('clients.index') }}" class="text-sm font-bold text-[#554b45] hover:text-[#030203]">Back to clients</a>
            </div>
        </div>
    </div>
</x-app-layout>
