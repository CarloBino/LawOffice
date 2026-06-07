<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-bold uppercase text-[#9f7957]">Matter registry</p>
                <h2 class="mt-2 text-3xl font-extrabold text-[#030203]">Cases</h2>
            </div>
            @unless(Auth::user()?->isLawyer())
                <a href="{{ route('cases.create') }}" class="inline-flex items-center justify-center bg-[#030203] px-5 py-3 text-sm font-bold text-white transition hover:bg-[#554b45]">New case</a>
            @endunless
        </div>
    </x-slot>

    <div class="bg-[#eef0ec] py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @php
                $filterCount = collect(['status', 'priority', 'client_id', 'lawyer_id'])
                    ->filter(fn ($key) => request()->filled($key))
                    ->count();
            @endphp
            <form method="GET" x-data="{ filtersOpen: {{ $filterCount ? 'true' : 'false' }} }" class="mb-4 bg-white p-4 shadow-sm">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <label class="text-sm font-bold uppercase text-[#554b45]">Sort by
                        <select name="sort" onchange="this.form.submit()" class="mt-2 w-full min-w-56 border-[#c1c1bd] bg-white text-sm font-semibold normal-case text-[#030203] focus:border-[#9f7957] focus:ring-[#9f7957]">
                        <option value="newest" @selected($sort === 'newest')>Newest</option>
                        <option value="priority" @selected($sort === 'priority')>Priority</option>
                        <option value="status" @selected($sort === 'status')>Status</option>
                        <option value="client" @selected($sort === 'client')>Client name</option>
                        <option value="lawyer" @selected($sort === 'lawyer')>Assigned lawyer</option>
                        <option value="date_filed" @selected($sort === 'date_filed')>Date filed</option>
                        </select>
                    </label>
                    <div class="flex items-center gap-4">
                        <button type="button" @click="filtersOpen = ! filtersOpen" class="text-sm font-bold text-[#9f7957] hover:text-[#030203]">Filters{{ $filterCount ? ' ('.$filterCount.')' : '' }}</button>
                        @if($filterCount)
                            <a href="{{ route('cases.index', ['sort' => $sort]) }}" class="text-sm font-bold text-[#554b45] hover:text-[#030203]">Clear</a>
                        @endif
                    </div>
                </div>
                <div x-show="filtersOpen" x-transition class="mt-4 grid gap-3 border-t border-[#e3e3df] pt-4 lg:grid-cols-4">
                    <label class="text-sm font-bold uppercase text-[#554b45]">
                        Status
                        <select name="status" onchange="this.form.submit()" class="mt-2 w-full border-[#c1c1bd] bg-white text-sm font-semibold normal-case text-[#030203]">
                            <option value="">All</option>
                            @foreach($statuses as $status)
                                <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="text-sm font-bold uppercase text-[#554b45]">
                        Priority
                        <select name="priority" onchange="this.form.submit()" class="mt-2 w-full border-[#c1c1bd] bg-white text-sm font-semibold normal-case text-[#030203]">
                            <option value="">All</option>
                            @foreach(['High','Medium','Low'] as $priority)
                                <option value="{{ $priority }}" @selected(request('priority') === $priority)>{{ $priority }}</option>
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
                        Lawyer
                        <select name="lawyer_id" onchange="this.form.submit()" class="mt-2 w-full border-[#c1c1bd] bg-white text-sm font-semibold normal-case text-[#030203]">
                            <option value="">All</option>
                            @foreach($lawyers as $lawyer)
                                <option value="{{ $lawyer->id }}" @selected((string) request('lawyer_id') === (string) $lawyer->id)>{{ $lawyer->display_name }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>
            </form>
            <div class="overflow-hidden bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-[#d1d2cd]">
                        <thead class="bg-white">
                            <tr class="text-left text-xs font-bold uppercase text-[#554b45]">
                                <th class="px-5 py-4">Case</th>
                                <th class="px-5 py-4">Client</th>
                                <th class="px-5 py-4">Lawyer</th>
                                <th class="px-5 py-4">Priority</th>
                                <th class="px-5 py-4 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#d1d2cd]">
                            @forelse($cases as $c)
                                <tr class="transition hover:bg-[#f8f8f6]">
                                    <td class="px-5 py-4">
                                        <a href="{{ route('cases.show', $c->id) }}" class="font-bold text-[#030203] hover:text-[#9f7957]">{{ $c->case_title }}</a>
                                        <p class="mt-1 text-sm text-[#554b45]">{{ $c->case_number }} | {{ $c->case_status }}</p>
                                    </td>
                                    <td class="px-5 py-4 text-sm text-[#554b45]">{{ optional($c->client)->full_name ?? 'Unassigned' }}</td>
                                    <td class="px-5 py-4 text-sm text-[#554b45]">{{ $c->assignedLawyer?->display_name ?? 'Unassigned' }}</td>
                                    <td class="px-5 py-4">
                                        <span class="px-3 py-1 text-xs font-bold {{ $c->priority_level === 'High' ? 'bg-red-100 text-red-800' : ($c->priority_level === 'Medium' ? 'bg-[#c7a47b] text-[#030203]' : 'bg-[#d1d2cd] text-[#554b45]') }}">{{ $c->priority_level }}</span>
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        <a href="{{ route('cases.show', $c->id) }}" class="text-sm font-bold text-[#9f7957] hover:text-[#030203]">Open</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-14 text-center">
                                        <p class="text-lg font-bold text-[#030203]">No cases yet</p>
                                        <p class="mt-2 text-sm text-[#554b45]">Create the first matter to begin tracking work.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="mt-6">{{ $cases->links() }}</div>
        </div>
    </div>
</x-app-layout>
