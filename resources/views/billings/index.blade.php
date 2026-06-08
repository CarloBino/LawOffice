<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-bold uppercase text-[#9f7957]">Accounts and fees</p>
                <h2 class="mt-2 text-3xl font-extrabold text-[#030203]">Billings</h2>
            </div>
            @unless(Auth::user()?->isLawyer())
                <a href="{{ route('billings.create') }}" class="inline-flex items-center justify-center bg-[#030203] px-5 py-3 text-sm font-bold text-white transition hover:bg-[#554b45]">New billing</a>
            @endunless
        </div>
    </x-slot>

    <div class="bg-[#eef0ec] py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @php
                $filterCount = collect(['payment_status', 'client_id', 'case_id', 'hearing_id', 'balance'])
                    ->filter(fn ($key) => request()->filled($key))
                    ->count();
            @endphp
            <form method="GET" x-data="{ filtersOpen: {{ $filterCount ? 'true' : 'false' }} }" class="mb-4 bg-white p-4 shadow-sm">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <label class="text-sm font-bold uppercase text-[#554b45]">Sort by
                        <select name="sort" onchange="this.form.submit()" class="mt-2 w-full min-w-56 border-[#c1c1bd] bg-white text-sm font-semibold normal-case text-[#030203] focus:border-[#9f7957] focus:ring-[#9f7957]">
                        <option value="newest" @selected($sort === 'newest')>Newest</option>
                        <option value="highest_balance" @selected($sort === 'highest_balance')>Highest balance</option>
                        <option value="unpaid_first" @selected($sort === 'unpaid_first')>Unpaid first</option>
                        <option value="latest_payment" @selected($sort === 'latest_payment')>Latest payment</option>
                        <option value="client" @selected($sort === 'client')>Client name</option>
                        <option value="case" @selected($sort === 'case')>Case number</option>
                        </select>
                    </label>
                    <div class="flex items-center gap-4">
                        <button type="button" @click="filtersOpen = ! filtersOpen" class="text-sm font-bold text-[#9f7957] hover:text-[#030203]">Filters{{ $filterCount ? ' ('.$filterCount.')' : '' }}</button>
                        @if($filterCount)
                            <a href="{{ route('billings.index', ['sort' => $sort]) }}" class="text-sm font-bold text-[#554b45] hover:text-[#030203]">Clear</a>
                        @endif
                        <a href="{{ route('billings.export', request()->query()) }}" class="text-sm font-bold text-[#9f7957] hover:text-[#030203]">Export CSV</a>
                    </div>
                </div>
                <div x-show="filtersOpen" x-transition class="mt-4 grid gap-3 border-t border-[#e3e3df] pt-4 lg:grid-cols-5">
                    <label class="text-sm font-bold uppercase text-[#554b45]">
                        Status
                        <select name="payment_status" onchange="this.form.submit()" class="mt-2 w-full border-[#c1c1bd] bg-white text-sm font-semibold normal-case text-[#030203]">
                            <option value="">All</option>
                            @foreach($statuses as $status)
                                <option value="{{ $status }}" @selected(request('payment_status') === $status)>{{ $status }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="text-sm font-bold uppercase text-[#554b45]">
                        Client
                        <select name="client_id" onchange="this.form.submit()" class="mt-2 w-full border-[#c1c1bd] bg-white text-sm font-semibold normal-case text-[#030203]">
                            <option value="">All</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}" @selected((string) request('client_id') === (string) $client->id)>{{ $client->full_name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="text-sm font-bold uppercase text-[#554b45]">
                        Case
                        <select name="case_id" onchange="this.form.submit()" class="mt-2 w-full border-[#c1c1bd] bg-white text-sm font-semibold normal-case text-[#030203]">
                            <option value="">All</option>
                            @foreach($cases as $case)
                                <option value="{{ $case->id }}" @selected((string) request('case_id') === (string) $case->id)>{{ $case->case_number }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="text-sm font-bold uppercase text-[#554b45]">
                        Hearing
                        <select name="hearing_id" onchange="this.form.submit()" class="mt-2 w-full border-[#c1c1bd] bg-white text-sm font-semibold normal-case text-[#030203]">
                            <option value="">All</option>
                            @foreach($hearings as $hearing)
                                <option value="{{ $hearing->id }}" @selected((string) request('hearing_id') === (string) $hearing->id)>{{ optional($hearing->case)->case_number }} / {{ $hearing->hearing_date ? \Illuminate\Support\Carbon::parse($hearing->hearing_date)->format('M d, Y') : 'TBA' }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="text-sm font-bold uppercase text-[#554b45]">
                        Balance
                        <select name="balance" onchange="this.form.submit()" class="mt-2 w-full border-[#c1c1bd] bg-white text-sm font-semibold normal-case text-[#030203]">
                            <option value="">All</option>
                            <option value="with_balance" @selected(request('balance') === 'with_balance')>With balance</option>
                        </select>
                    </label>
                </div>
            </form>
            <div class="overflow-hidden bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-[#d1d2cd]">
                        <thead>
                            <tr class="text-left text-xs font-bold uppercase text-[#7a716b]">
                                <th class="px-5 py-4">Case</th>
                                <th class="px-5 py-4">Client</th>
                                <th class="px-5 py-4">Hearing</th>
                                <th class="px-5 py-4">Latest Date Received</th>
                                <th class="px-5 py-4">Total</th>
                                <th class="px-5 py-4">Paid</th>
                                <th class="px-5 py-4">Balance</th>
                                <th class="px-5 py-4">Status</th>
                                <th class="px-5 py-4 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#d1d2cd]">
                            @forelse($billings as $b)
                                <tr class="transition hover:bg-[#f8f8f6]">
                                    <td class="px-5 py-4">
                                        <a href="{{ route('billings.show', $b->id) }}" class="font-bold text-[#030203] hover:text-[#9f7957]">{{ optional($b->case)->case_number ?? 'No case linked' }}</a>
                                        <p class="mt-1 text-sm text-[#554b45]">{{ optional($b->case)->case_title }}</p>
                                    </td>
                                    <td class="px-5 py-4 text-sm font-semibold text-[#030203]">{{ optional(optional($b->case)->client)->full_name ?? 'No client linked' }}</td>
                                    <td class="px-5 py-4 text-sm text-[#554b45]">
                                        @if($b->hearing)
                                            <a href="{{ route('hearings.show', $b->hearing_id) }}" class="font-semibold text-[#030203] hover:text-[#9f7957]">{{ $b->hearing->hearing_date ? \Illuminate\Support\Carbon::parse($b->hearing->hearing_date)->format('M d, Y') : 'TBA' }}</a>
                                            <p class="mt-1">{{ $b->hearing->hearing_purpose ?: 'Appearance fee' }}</p>
                                        @else
                                            Case billing
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-sm text-[#554b45]">{{ $b->payment_date ? \Illuminate\Support\Carbon::parse($b->payment_date)->format('M d, Y') : 'No payment received' }}</td>
                                    <td class="px-5 py-4 text-sm font-bold text-[#030203]">{{ number_format($b->total_amount, 2) }}</td>
                                    <td class="px-5 py-4 text-sm text-[#554b45]">{{ number_format($b->amount_paid, 2) }}</td>
                                    <td class="px-5 py-4 text-sm font-bold {{ $b->balance > 0 ? 'text-red-700' : 'text-emerald-700' }}">{{ number_format($b->balance, 2) }}</td>
                                    <td class="px-5 py-4">
                                        @unless(Auth::user()?->isAdmin())
                                            <span class="px-3 py-1 text-xs font-bold {{ $b->balance > 0 ? 'bg-red-100 text-red-800' : 'bg-emerald-100 text-emerald-800' }}">
                                                {{ $b->payment_status ?: ($b->balance > 0 ? 'Unpaid' : 'Paid') }}
                                            </span>
                                        @else
                                            <form method="POST" action="{{ route('billings.toggle-paid', $b->id) }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button
                                                    type="submit"
                                                    class="px-3 py-1 text-xs font-bold transition hover:scale-105 {{ $b->balance > 0 ? 'bg-red-100 text-red-800 hover:bg-red-200' : 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' }}"
                                                    title="{{ $b->balance > 0 ? 'Mark this billing as paid' : 'Mark this billing as unpaid' }}"
                                                    onclick="return confirm('{{ $b->balance > 0 ? 'Mark this billing as paid?' : 'Mark this billing as unpaid?' }}')"
                                                >
                                                    {{ $b->payment_status ?: ($b->balance > 0 ? 'Unpaid' : 'Paid') }}
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        <a href="{{ route('billings.show', $b->id) }}" class="text-sm font-bold text-[#9f7957] hover:text-[#030203]">{{ Auth::user()?->isLawyer() ? 'View' : 'Manage' }}</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-5 py-14 text-center">
                                        <p class="text-lg font-bold text-[#030203]">No billing records yet</p>
                                        <p class="mt-2 text-sm text-[#554b45]">Create a billing entry to track fees and balances.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="mt-6">{{ $billings->links() }}</div>
        </div>
    </div>
</x-app-layout>
