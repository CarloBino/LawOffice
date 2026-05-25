<x-app-layout>
    @php($isLawyer = Auth::user()?->isLawyer())

    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-bold uppercase text-[#9f7957]">{{ $isLawyer ? 'Lawyer workspace' : 'Office command center' }}</p>
                <h2 class="mt-2 text-3xl font-extrabold text-[#030203]">{{ $isLawyer ? 'My Dashboard' : __('Dashboard') }}</h2>
            </div>
            @unless($isLawyer)
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('cases.create') }}" class="inline-flex items-center justify-center bg-[#030203] px-4 py-2 text-sm font-bold text-white transition hover:bg-[#554b45]">New case</a>
                    <a href="{{ route('clients.create') }}" class="inline-flex items-center justify-center border border-[#c1c1bd] bg-white px-4 py-2 text-sm font-bold text-[#030203] transition hover:border-[#9f7957]">New client</a>
                </div>
            @endunless
        </div>
    </x-slot>

    <div class="bg-[#eef0ec] py-8">
        <div class="mx-auto max-w-6xl space-y-6 px-4 sm:px-6 lg:px-8">
            <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <a href="{{ route('clients.index') }}" class="bg-white p-5 shadow-sm transition hover:bg-[#f8f8f6]">
                    <p class="text-xs font-bold uppercase text-[#9f7957]">{{ $isLawyer ? 'My clients' : 'Clients' }}</p>
                    <p class="mt-2 text-3xl font-extrabold text-[#030203]">{{ number_format($stats['clients']) }}</p>
                </a>
                <a href="{{ route('cases.index') }}" class="bg-white p-5 shadow-sm transition hover:bg-[#f8f8f6]">
                    <p class="text-xs font-bold uppercase text-[#9f7957]">{{ $isLawyer ? 'My open cases' : 'Open cases' }}</p>
                    <p class="mt-2 text-3xl font-extrabold text-[#030203]">{{ number_format($stats['openCases']) }}</p>
                </a>
                <a href="{{ route('hearings.index') }}" class="bg-white p-5 shadow-sm transition hover:bg-[#f8f8f6]">
                    <p class="text-xs font-bold uppercase text-[#9f7957]">{{ $isLawyer ? 'My upcoming hearings' : 'Upcoming hearings' }}</p>
                    <p class="mt-2 text-3xl font-extrabold text-[#030203]">{{ number_format($stats['upcomingHearings']) }}</p>
                </a>
                @unless($isLawyer)
                    <a href="{{ route('billings.index') }}" class="bg-white p-5 shadow-sm transition hover:bg-[#f8f8f6]">
                        <p class="text-xs font-bold uppercase text-[#9f7957]">Client unpaid</p>
                        <p class="mt-2 text-3xl font-extrabold {{ $stats['unpaidBalance'] > 0 ? 'text-red-700' : 'text-emerald-700' }}">{{ number_format($stats['unpaidBalance'], 2) }}</p>
                    </a>
                @endunless
                @unless($isLawyer)
                    <a href="{{ route('office-expenses.index') }}" class="bg-white p-5 shadow-sm transition hover:bg-[#f8f8f6]">
                        <p class="text-xs font-bold uppercase text-[#9f7957]">Office unpaid</p>
                        <p class="mt-2 text-3xl font-extrabold {{ $stats['officeExpensesUnpaid'] > 0 ? 'text-red-700' : 'text-emerald-700' }}">{{ number_format($stats['officeExpensesUnpaid'], 2) }}</p>
                    </a>
                @endunless
                <a href="{{ route('documents.index') }}" class="bg-white p-5 shadow-sm transition hover:bg-[#f8f8f6]">
                    <p class="text-xs font-bold uppercase text-[#9f7957]">{{ $isLawyer ? 'My documents' : 'Documents' }}</p>
                    <p class="mt-2 text-3xl font-extrabold text-[#030203]">{{ number_format($stats['documents']) }}</p>
                </a>
            </section>

            <section class="grid gap-6 lg:grid-cols-[1.15fr_.85fr]">
                <div class="bg-white shadow-sm">
                    <div class="flex items-center justify-between border-b border-[#e3e3df] px-5 py-4">
                        <div>
                            <h3 class="font-bold text-[#030203]">{{ $isLawyer ? 'My priority cases' : 'Priority matters' }}</h3>
                            <p class="text-sm text-[#554b45]">{{ $isLawyer ? 'Assigned open cases ordered by urgency' : 'Open cases ordered by urgency' }}</p>
                        </div>
                        <a href="{{ route('cases.index') }}" class="text-sm font-bold text-[#9f7957] hover:text-[#030203]">View all</a>
                    </div>
                    <div class="divide-y divide-[#e3e3df]">
                        @forelse ($priorityCases as $case)
                            <a href="{{ route('cases.show', $case) }}" class="grid gap-3 px-5 py-4 transition hover:bg-[#f8f8f6] sm:grid-cols-[1fr_auto]">
                                <div>
                                    <p class="font-bold text-[#030203]">{{ $case->case_title }}</p>
                                    <p class="mt-1 text-sm text-[#554b45]">{{ $case->case_number }} / {{ $case->client?->full_name ?? 'No client assigned' }}</p>
                                </div>
                                <div class="flex items-center gap-2 sm:justify-end">
                                    <span class="px-2.5 py-1 text-xs font-bold {{ $case->priority_level === 'High' ? 'bg-red-100 text-red-800' : ($case->priority_level === 'Medium' ? 'bg-[#f4eadf] text-[#7a5737]' : 'bg-[#eef0ec] text-[#554b45]') }}">{{ $case->priority_level }}</span>
                                    <span class="text-sm text-[#554b45]">{{ $case->case_status }}</span>
                                </div>
                            </a>
                        @empty
                            <div class="px-5 py-10 text-center text-sm text-[#554b45]">No open cases yet.</div>
                        @endforelse
                    </div>
                </div>

                <div class="bg-white shadow-sm">
                    <div class="flex items-center justify-between border-b border-[#e3e3df] px-5 py-4">
                        <div>
                            <h3 class="font-bold text-[#030203]">{{ $isLawyer ? 'My next hearings' : 'Next hearings' }}</h3>
                            <p class="text-sm text-[#554b45]">{{ $isLawyer ? 'Court dates for assigned cases' : 'Court dates coming up' }}</p>
                        </div>
                        <a href="{{ route('hearings.create') }}" class="text-sm font-bold text-[#9f7957] hover:text-[#030203]">Add</a>
                    </div>
                    <div class="divide-y divide-[#e3e3df]">
                        @forelse ($upcomingHearings as $hearing)
                            <a href="{{ route('hearings.show', $hearing) }}" class="block px-5 py-4 transition hover:bg-[#f8f8f6]">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="font-bold text-[#030203]">{{ $hearing->case?->case_title ?? 'Unassigned hearing' }}</p>
                                        <p class="mt-1 text-sm text-[#554b45]">{{ $hearing->court_venue ?? 'Venue pending' }}{{ $hearing->court_branch ? ', '.$hearing->court_branch : '' }}</p>
                                    </div>
                                    <div class="shrink-0 text-right text-sm">
                                        <p class="font-bold text-[#030203]">{{ $hearing->hearing_date ? \Illuminate\Support\Carbon::parse($hearing->hearing_date)->format('M j, Y') : 'TBA' }}</p>
                                        <p class="text-[#554b45]">{{ $hearing->hearing_time ? \Illuminate\Support\Carbon::parse($hearing->hearing_time)->format('g:i A') : 'TBA' }}</p>
                                    </div>
                                </div>
                            </a>
                        @empty
                            <div class="px-5 py-10 text-center text-sm text-[#554b45]">No upcoming hearings scheduled.</div>
                        @endforelse
                    </div>
                </div>
            </section>

            @unless($isLawyer)
            <section class="grid gap-6 lg:grid-cols-2">
                <div class="bg-white shadow-sm">
                    <div class="flex items-center justify-between border-b border-[#e3e3df] px-5 py-4">
                        <div>
                            <h3 class="font-bold text-[#030203]">Recent payments</h3>
                            <p class="text-sm text-[#554b45]">Latest client collections</p>
                        </div>
                        <a href="{{ route('billings.index') }}" class="text-sm font-bold text-[#9f7957] hover:text-[#030203]">Billings</a>
                    </div>
                    <div class="divide-y divide-[#e3e3df]">
                        @forelse ($recentPayments as $payment)
                            <a href="{{ route('billings.show', $payment->billing_id) }}" class="flex items-start justify-between gap-4 px-5 py-4 transition hover:bg-[#f8f8f6]">
                                <div>
                                    <p class="font-bold text-[#030203]">{{ optional(optional(optional($payment->billing)->case)->client)->full_name ?? 'Client payment' }}</p>
                                    <p class="mt-1 text-sm text-[#554b45]">{{ optional(optional($payment->billing)->case)->case_number ?? 'No case linked' }} / {{ $payment->official_receipt_number ?: 'No receipt' }}</p>
                                </div>
                                <div class="shrink-0 text-right">
                                    <p class="font-bold text-emerald-700">{{ number_format($payment->amount, 2) }}</p>
                                    <p class="mt-1 text-sm text-[#554b45]">{{ \Illuminate\Support\Carbon::parse($payment->date_received)->format('M j, Y') }}</p>
                                </div>
                            </a>
                        @empty
                            <div class="px-5 py-10 text-center text-sm text-[#554b45]">No payments recorded yet.</div>
                        @endforelse
                    </div>
                </div>

                    <div class="bg-white shadow-sm">
                        <div class="flex items-center justify-between border-b border-[#e3e3df] px-5 py-4">
                            <div>
                                <h3 class="font-bold text-[#030203]">Office expenses</h3>
                                <p class="text-sm text-[#554b45]">Unpaid operating costs</p>
                            </div>
                            <a href="{{ route('office-expenses.create') }}" class="text-sm font-bold text-[#9f7957] hover:text-[#030203]">Add</a>
                        </div>
                        <div class="divide-y divide-[#e3e3df]">
                            @forelse ($unpaidOfficeExpenses as $expense)
                                <a href="{{ route('office-expenses.show', $expense) }}" class="flex items-start justify-between gap-4 px-5 py-4 transition hover:bg-[#f8f8f6]">
                                    <div>
                                        <p class="font-bold text-[#030203]">{{ $expense->expense_type }}</p>
                                        <p class="mt-1 text-sm text-[#554b45]">{{ $expense->description ?: 'No description' }}</p>
                                    </div>
                                    <div class="shrink-0 text-right">
                                        <p class="font-bold text-red-700">{{ number_format($expense->amount, 2) }}</p>
                                        <p class="mt-1 text-sm text-[#554b45]">{{ $expense->due_date ? \Illuminate\Support\Carbon::parse($expense->due_date)->format('M j, Y') : 'No due date' }}</p>
                                    </div>
                                </a>
                            @empty
                                <div class="px-5 py-10 text-center text-sm text-[#554b45]">No unpaid office expenses.</div>
                            @endforelse
                        </div>
                    </div>
            </section>
            @endunless

        </div>
    </div>
</x-app-layout>
