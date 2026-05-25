<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-bold uppercase text-[#9f7957]">File room</p>
                <h2 class="mt-2 text-3xl font-extrabold text-[#030203]">Documents</h2>
            </div>
            <a href="{{ route('documents.create') }}" class="inline-flex items-center justify-center bg-[#030203] px-4 py-2 text-sm font-bold text-white transition hover:bg-[#554b45]">New document</a>
        </div>
    </x-slot>

    <div class="bg-[#eef0ec] py-8">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            @php
                $filterCount = collect(['document_type', 'client_id', 'case_id'])
                    ->filter(fn ($key) => request()->filled($key))
                    ->count();
            @endphp
            <form method="GET" x-data="{ filtersOpen: {{ $filterCount ? 'true' : 'false' }} }" class="mb-4 bg-white p-4 shadow-sm">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <label class="text-sm font-bold uppercase text-[#554b45]">Sort by
                        <select name="sort" onchange="this.form.submit()" class="mt-2 w-full min-w-56 border-[#c1c1bd] bg-white text-sm font-semibold normal-case text-[#030203] focus:border-[#9f7957] focus:ring-[#9f7957]">
                        <option value="latest" @selected($sort === 'latest')>Latest uploaded</option>
                        <option value="type" @selected($sort === 'type')>Document type</option>
                        <option value="case" @selected($sort === 'case')>Case number</option>
                        <option value="client" @selected($sort === 'client')>Client name</option>
                        </select>
                    </label>
                    <div class="flex items-center gap-4">
                        <button type="button" @click="filtersOpen = ! filtersOpen" class="text-sm font-bold text-[#9f7957] hover:text-[#030203]">Filters{{ $filterCount ? ' ('.$filterCount.')' : '' }}</button>
                        @if($filterCount)
                            <a href="{{ route('documents.index', ['sort' => $sort]) }}" class="text-sm font-bold text-[#554b45] hover:text-[#030203]">Clear</a>
                        @endif
                    </div>
                </div>
                <div x-show="filtersOpen" x-transition class="mt-4 grid gap-3 border-t border-[#e3e3df] pt-4 lg:grid-cols-3">
                    <label class="text-sm font-bold uppercase text-[#554b45]">
                        Type
                        <select name="document_type" onchange="this.form.submit()" class="mt-2 w-full border-[#c1c1bd] bg-white text-sm font-semibold normal-case text-[#030203]">
                            <option value="">All</option>
                            @foreach($types as $type)
                                <option value="{{ $type }}" @selected(request('document_type') === $type)>{{ $type }}</option>
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
                </div>
            </form>
            <section class="bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-[#e3e3df]">
                        <thead><tr class="text-left text-xs font-bold uppercase text-[#7a716b]"><th class="px-5 py-4">Document</th><th class="px-5 py-4">Case</th><th class="px-5 py-4">Client</th><th class="px-5 py-4">Uploaded</th><th class="px-5 py-4 text-right">Action</th></tr></thead>
                        <tbody class="divide-y divide-[#e3e3df]">
                            @forelse($documents as $d)
                                <tr class="transition hover:bg-[#f8f8f6]">
                                    <td class="px-5 py-4"><a href="{{ route('documents.show', $d->id) }}" class="font-bold text-[#030203] hover:text-[#9f7957]">{{ $d->document_name }}</a><p class="mt-1 text-sm text-[#554b45]">{{ $d->document_type ?: 'File' }}</p></td>
                                    <td class="px-5 py-4 text-sm text-[#554b45]">{{ optional($d->case)->case_number ?? 'No case linked' }}</td>
                                    <td class="px-5 py-4 text-sm text-[#554b45]">{{ optional(optional($d->case)->client)->full_name ?? 'No client linked' }}</td>
                                    <td class="px-5 py-4 text-sm text-[#554b45]">{{ $d->created_at ? $d->created_at->format('M d, Y') : 'Not recorded' }}</td>
                                    <td class="px-5 py-4 text-right">
                                        <div class="flex justify-end gap-4">
                                            @if($d->file_path)<a href="{{ asset('storage/'.$d->file_path) }}" target="_blank" class="text-sm font-bold text-[#554b45] hover:text-[#030203]">Download</a>@endif
                                            <a href="{{ route('documents.show', $d->id) }}" class="text-sm font-bold text-[#9f7957] hover:text-[#030203]">Open</a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-5 py-12 text-center text-sm text-[#554b45]">No documents uploaded yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
            <div class="mt-6">{{ $documents->links() }}</div>
        </div>
    </div>
</x-app-layout>
