<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-bold uppercase text-[#9f7957]">People and organizations</p>
                <h2 class="mt-2 text-3xl font-extrabold text-[#030203]">Clients</h2>
            </div>
            @unless(Auth::user()?->isLawyer())
                <a href="{{ route('clients.create') }}" class="inline-flex items-center justify-center bg-[#030203] px-4 py-2 text-sm font-bold text-white transition hover:bg-[#554b45]">New client</a>
            @endunless
        </div>
    </x-slot>

    <div class="bg-[#eef0ec] py-8">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            @php
                $filterCount = collect(['client_type', 'balance', 'case_status'])
                    ->filter(fn ($key) => request()->filled($key))
                    ->count();
            @endphp
            <form method="GET" x-data="{ filtersOpen: {{ $filterCount ? 'true' : 'false' }} }" class="mb-4 bg-white p-4 shadow-sm">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <label class="text-sm font-bold uppercase text-[#554b45]">Sort by
                        <select name="sort" onchange="this.form.submit()" class="mt-2 w-full min-w-56 border-[#c1c1bd] bg-white text-sm font-semibold normal-case text-[#030203] focus:border-[#9f7957] focus:ring-[#9f7957]">
                        <option value="name" @selected($sort === 'name')>Name</option>
                        <option value="newest" @selected($sort === 'newest')>Newest</option>
                        <option value="most_cases" @selected($sort === 'most_cases')>Most cases</option>
                        <option value="highest_balance" @selected($sort === 'highest_balance')>Highest balance</option>
                        </select>
                    </label>
                    <div class="flex items-center gap-4">
                        <button type="button" @click="filtersOpen = ! filtersOpen" class="text-sm font-bold text-[#9f7957] hover:text-[#030203]">Filters{{ $filterCount ? ' ('.$filterCount.')' : '' }}</button>
                        @if($filterCount)
                            <a href="{{ route('clients.index', ['sort' => $sort]) }}" class="text-sm font-bold text-[#554b45] hover:text-[#030203]">Clear</a>
                        @endif
                    </div>
                </div>
                <div x-show="filtersOpen" x-transition class="mt-4 grid gap-3 border-t border-[#e3e3df] pt-4 lg:grid-cols-3">
                    <label class="text-sm font-bold uppercase text-[#554b45]">
                        Client Type
                        <select name="client_type" onchange="this.form.submit()" class="mt-2 w-full border-[#c1c1bd] bg-white text-sm font-semibold normal-case text-[#030203]">
                            <option value="">All</option>
                            @foreach($clientTypes as $type)
                                <option value="{{ $type }}" @selected(request('client_type') === $type)>{{ $type }}</option>
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
                    <label class="text-sm font-bold uppercase text-[#554b45]">
                        Cases
                        <select name="case_status" onchange="this.form.submit()" class="mt-2 w-full border-[#c1c1bd] bg-white text-sm font-semibold normal-case text-[#030203]">
                            <option value="">All</option>
                            <option value="no_active_case" @selected(request('case_status') === 'no_active_case')>No active case</option>
                        </select>
                    </label>
                </div>
            </form>
            <div class="bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-[#e3e3df]">
                        <thead>
                            <tr class="text-left text-xs font-bold uppercase text-[#7a716b]">
                                <th class="px-5 py-4">Client</th>
                                <th class="px-5 py-4">Contact</th>
                                <th class="px-5 py-4">Cases</th>
                                <th class="px-5 py-4">Billed</th>
                                <th class="px-5 py-4">Balance</th>
                                <th class="px-5 py-4 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#e3e3df]">
                            @forelse($clients as $client)
                                @php
                                    $billings = $client->cases->flatMap->billings;
                                    $balance = $billings->sum('balance');
                                    $totalBilled = $billings->sum('total_amount');
                                @endphp
                                <tr class="transition hover:bg-[#f8f8f6]">
                                    <td class="px-5 py-4">
                                        <a href="{{ route('clients.show', $client->id) }}" class="font-bold text-[#030203] hover:text-[#9f7957]">{{ $client->full_name }}</a>
                                        <p class="mt-1 text-xs font-bold uppercase text-[#9f7957]">{{ $client->client_type ?? 'Client' }}</p>
                                    </td>
                                    <td class="px-5 py-4 text-sm text-[#554b45]">
                                        <p>{{ $client->email ?: 'No email recorded' }}</p>
                                        <p class="mt-1">{{ $client->contact_number ?: 'No contact number' }}</p>
                                    </td>
                                    <td class="px-5 py-4 text-sm text-[#030203]">
                                        <span class="font-bold">{{ $client->cases->count() }}</span>
                                        <span class="text-[#7a716b]">total</span>
                                        <p class="mt-1 text-[#7a716b]">{{ $client->cases->whereNotIn('case_status', ['Closed', 'Archived'])->count() }} open</p>
                                    </td>
                                    <td class="px-5 py-4 text-sm font-semibold text-[#030203]">{{ number_format($totalBilled, 2) }}</td>
                                    <td class="px-5 py-4 text-sm font-semibold {{ $balance > 0 ? 'text-red-700' : 'text-emerald-700' }}">{{ number_format($balance, 2) }}</td>
                                    <td class="px-5 py-4 text-right">
                                        <div class="flex justify-end gap-4">
                                            <a href="{{ route('clients.show', $client->id) }}" class="text-sm font-bold text-[#9f7957] hover:text-[#030203]">Manage</a>
                                            @unless(Auth::user()?->isLawyer())
                                                <a href="{{ route('cases.create', ['client_id' => $client->id]) }}" class="text-sm font-bold text-[#554b45] hover:text-[#030203]">New case</a>
                                            @endunless
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-12 text-center">
                                        <p class="font-bold text-[#030203]">No clients yet</p>
                                        <p class="mt-2 text-sm text-[#554b45]">Add a client profile before opening a case file.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="mt-6">{{ $clients->links() }}</div>
        </div>
    </div>
</x-app-layout>
