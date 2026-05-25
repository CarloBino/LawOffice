<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-bold uppercase text-[#9f7957]">Global search</p>
            <h2 class="mt-2 text-3xl font-extrabold text-[#030203]">Search Results</h2>
        </div>
    </x-slot>

    <div class="bg-[#eef0ec] py-8">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <form method="GET" action="{{ route('search.index') }}" class="mb-6 bg-white p-5 shadow-sm">
                <label class="mb-2 block text-sm font-bold uppercase text-[#554b45]">Search anything</label>
                <div class="flex flex-col gap-3 sm:flex-row">
                    <input type="search" name="q" value="{{ $query }}" placeholder="Client, case number, receipt, branch, document..." class="min-w-0 flex-1 border-[#c1c1bd] bg-white text-[#030203] focus:border-[#9f7957] focus:ring-[#9f7957]">
                    <button class="bg-[#030203] px-6 py-3 text-sm font-bold text-white transition hover:bg-[#554b45]">Search</button>
                </div>
            </form>

            <section class="bg-white shadow-sm">
                <div class="border-b border-[#e3e3df] px-5 py-4">
                    <h3 class="text-sm font-bold uppercase text-[#554b45]">
                        @if($query)
                            {{ $results->count() }} result{{ $results->count() === 1 ? '' : 's' }} for "{{ $query }}"
                        @else
                            Enter a search term
                        @endif
                    </h3>
                </div>

                <div class="divide-y divide-[#e3e3df]">
                    @if(! $query)
                        <div class="px-5 py-12 text-center text-sm text-[#554b45]">Search by client name, case number, receipt number, branch, lawyer, document, or expense.</div>
                    @else
                        @forelse($results as $result)
                            <a href="{{ $result['url'] }}" class="block px-5 py-4 transition hover:bg-[#f8f8f6]">
                                <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                    <div>
                                        <p class="text-xs font-bold uppercase text-[#9f7957]">{{ $result['type'] }}</p>
                                        <p class="mt-1 font-bold text-[#030203]">{{ $result['title'] }}</p>
                                        <p class="mt-1 text-sm text-[#554b45]">{{ $result['details'] }}</p>
                                    </div>
                                    <span class="text-sm font-bold text-[#9f7957]">Open</span>
                                </div>
                            </a>
                        @empty
                            <div class="px-5 py-12 text-center">
                                <p class="font-bold text-[#030203]">No results found</p>
                                <p class="mt-2 text-sm text-[#554b45]">Try a client name, case number, receipt number, court branch, or document title.</p>
                            </div>
                        @endforelse
                    @endif
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
