<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-bold uppercase text-[#9f7957]">Court calendar</p>
                <h2 class="mt-2 text-3xl font-extrabold text-[#030203]">Hearings</h2>
            </div>
            @unless(Auth::user()?->isLawyer())
                <a href="{{ route('hearings.create') }}" class="inline-flex items-center justify-center bg-[#030203] px-4 py-2 text-sm font-bold text-white transition hover:bg-[#554b45]">New hearing</a>
            @endunless
        </div>
    </x-slot>

    <div class="bg-[#eef0ec] py-8">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            @php
                $filterCount = collect(['status', 'branch', 'lawyer_id', 'client_id', 'date_from', 'date_to'])
                    ->filter(fn ($key) => request()->filled($key))
                    ->count();
            @endphp
            <form method="GET" x-data="{ filtersOpen: {{ $filterCount ? 'true' : 'false' }} }" class="mb-4 bg-white p-4 shadow-sm">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <label class="text-sm font-bold uppercase text-[#554b45]">Sort by
                        <select name="sort" onchange="this.form.submit()" class="mt-2 w-full min-w-56 border-[#c1c1bd] bg-white text-sm font-semibold normal-case text-[#030203] focus:border-[#9f7957] focus:ring-[#9f7957]">
                        <option value="nearest" @selected($sort === 'nearest')>Nearest hearing</option>
                        <option value="latest" @selected($sort === 'latest')>Latest hearing</option>
                        <option value="status" @selected($sort === 'status')>Status</option>
                        <option value="branch" @selected($sort === 'branch')>Branch</option>
                        <option value="lawyer" @selected($sort === 'lawyer')>Lawyer</option>
                        <option value="client" @selected($sort === 'client')>Client</option>
                        </select>
                    </label>
                    <div class="flex items-center gap-4">
                        <button type="button" @click="filtersOpen = ! filtersOpen" class="text-sm font-bold text-[#9f7957] hover:text-[#030203]">Filters{{ $filterCount ? ' ('.$filterCount.')' : '' }}</button>
                        @if($filterCount)
                            <a href="{{ route('hearings.index', ['sort' => $sort]) }}" class="text-sm font-bold text-[#554b45] hover:text-[#030203]">Clear</a>
                        @endif
                    </div>
                </div>
                <div x-show="filtersOpen" x-transition class="mt-4 grid gap-3 border-t border-[#e3e3df] pt-4 lg:grid-cols-3">
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
                        Branch
                        <select name="branch" onchange="this.form.submit()" class="mt-2 w-full border-[#c1c1bd] bg-white text-sm font-semibold normal-case text-[#030203]">
                            <option value="">All</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch }}" @selected(request('branch') === $branch)>{{ $branch }}</option>
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
                        From
                        <input type="date" name="date_from" value="{{ request('date_from') }}" onchange="this.form.submit()" class="mt-2 w-full border-[#c1c1bd] bg-white text-sm font-semibold normal-case text-[#030203]">
                    </label>
                    <label class="text-sm font-bold uppercase text-[#554b45]">
                        To
                        <input type="date" name="date_to" value="{{ request('date_to') }}" onchange="this.form.submit()" class="mt-2 w-full border-[#c1c1bd] bg-white text-sm font-semibold normal-case text-[#030203]">
                    </label>
                </div>
            </form>
            <section class="bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-[#e3e3df]">
                        <thead>
                            <tr class="text-left text-xs font-bold uppercase text-[#7a716b]">
                                <th class="px-5 py-4">Schedule</th>
                                <th class="px-5 py-4">Case</th>
                                <th class="px-5 py-4">Client</th>
                                <th class="px-5 py-4">Lawyer</th>
                                <th class="px-5 py-4">Venue</th>
                                <th class="px-5 py-4">Status</th>
                                <th class="px-5 py-4 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#e3e3df]">
                            @forelse($hearings as $h)
                                <tr class="transition hover:bg-[#f8f8f6]">
                                    <td class="px-5 py-4">
                                        <p class="font-bold text-[#030203]">{{ $h->hearing_date ? \Illuminate\Support\Carbon::parse($h->hearing_date)->format('M d, Y') : 'TBA' }}</p>
                                        <p class="mt-1 text-sm text-[#554b45]">{{ $h->hearing_time ? \Illuminate\Support\Carbon::parse($h->hearing_time)->format('g:i A') : 'Time pending' }}</p>
                                    </td>
                                    <td class="px-5 py-4">
                                        <a href="{{ route('hearings.show', $h->id) }}" class="font-bold text-[#030203] hover:text-[#9f7957]">{{ optional($h->case)->case_title ?? 'Unassigned hearing' }}</a>
                                        <p class="mt-1 text-sm text-[#554b45]">{{ optional($h->case)->case_number ?? 'No case number' }}</p>
                                    </td>
                                    <td class="px-5 py-4 text-sm text-[#554b45]">{{ optional(optional($h->case)->client)->full_name ?? 'No client' }}</td>
                                    <td class="px-5 py-4 text-sm text-[#554b45]">{{ optional($h->case?->assignedLawyer)->display_name ?? 'No lawyer assigned' }}</td>
                                    <td class="px-5 py-4 text-sm text-[#554b45]">{{ $h->court_venue ?: 'Venue pending' }}{{ $h->court_branch ? ', '.$h->court_branch : '' }}</td>
                                    <td class="px-5 py-4"><span class="bg-[#eef0ec] px-3 py-1 text-xs font-bold text-[#554b45]">{{ $h->hearing_status ?: 'Scheduled' }}</span></td>
                                    <td class="px-5 py-4 text-right"><a href="{{ route('hearings.show', $h->id) }}" class="text-sm font-bold text-[#9f7957] hover:text-[#030203]">Open</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-5 py-12 text-center text-sm text-[#554b45]">No hearings scheduled.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
            <div class="mt-6">{{ $hearings->links() }}</div>
        </div>
    </div>
</x-app-layout>
