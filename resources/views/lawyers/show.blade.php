<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-bold uppercase text-[#9f7957]">Lawyer profile</p>
                <h2 class="mt-2 text-3xl font-extrabold text-[#030203]">{{ $lawyer->full_name }}</h2>
            </div>
            @unless(Auth::user()?->isLawyer())
                <a href="{{ route('lawyers.edit', $lawyer->id) }}" class="inline-flex items-center justify-center bg-[#030203] px-4 py-2 text-sm font-bold text-white transition hover:bg-[#554b45]">Edit lawyer</a>
            @endunless
        </div>
    </x-slot>

    <div class="bg-[#eef0ec] py-8">
        <div class="mx-auto max-w-6xl space-y-6 px-4 sm:px-6 lg:px-8">
            <section class="bg-white px-6 py-5 shadow-sm">
                <div class="grid gap-5 md:grid-cols-[1.4fr_1fr]">
                    <div>
                        <p class="text-xs font-bold uppercase text-[#9f7957]">{{ $lawyer->status ?: 'Active' }}</p>
                        <h3 class="mt-1 text-2xl font-extrabold text-[#030203]">{{ $lawyer->full_name }}</h3>
                        <div class="mt-4 grid gap-3 text-sm text-[#554b45] sm:grid-cols-3">
                            <p><span class="block text-xs font-bold uppercase text-[#9f7957]">Contact</span>{{ $lawyer->contact_number ?: 'Not recorded' }}</p>
                            <p><span class="block text-xs font-bold uppercase text-[#9f7957]">Email</span>{{ $lawyer->email ?: 'Not recorded' }}</p>
                            <p><span class="block text-xs font-bold uppercase text-[#9f7957]">Specialization</span>{{ $lawyer->specialization ?: 'General practice' }}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-x-6 gap-y-4 border-t border-[#d1d2cd] pt-5 md:border-l md:border-t-0 md:pl-6 md:pt-0">
                        <div><p class="text-xs font-bold uppercase text-[#9f7957]">Cases</p><p class="mt-1 text-xl font-extrabold text-[#030203]">{{ $stats['cases'] }}</p></div>
                        <div><p class="text-xs font-bold uppercase text-[#9f7957]">Open</p><p class="mt-1 text-xl font-extrabold text-[#030203]">{{ $stats['open_cases'] }}</p></div>
                        <div><p class="text-xs font-bold uppercase text-[#9f7957]">High Priority</p><p class="mt-1 text-xl font-extrabold {{ $stats['high_priority'] > 0 ? 'text-red-700' : 'text-[#030203]' }}">{{ $stats['high_priority'] }}</p></div>
                        <div><p class="text-xs font-bold uppercase text-[#9f7957]">Hearings</p><p class="mt-1 text-xl font-extrabold text-[#030203]">{{ $stats['upcoming_hearings'] }}</p></div>
                    </div>
                </div>
            </section>

            <div class="grid gap-6 lg:grid-cols-[1.15fr_.85fr]">
                <section class="bg-white shadow-sm">
                    <div class="border-b border-[#e3e3df] px-5 py-4">
                        <h3 class="text-sm font-bold uppercase text-[#554b45]">Assigned cases</h3>
                    </div>
                    <div class="divide-y divide-[#e3e3df]">
                        @forelse($lawyer->cases as $case)
                            <a href="{{ route('cases.show', $case->id) }}" class="block px-5 py-4 transition hover:bg-[#f8f8f6]">
                                <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                    <div>
                                        <p class="font-bold text-[#030203]">{{ $case->case_number }}</p>
                                        <p class="mt-1 text-sm text-[#554b45]">{{ $case->case_title }}</p>
                                        <p class="mt-1 text-sm text-[#7a716b]">{{ optional($case->client)->full_name ?: 'No client assigned' }}</p>
                                    </div>
                                    <div class="flex gap-2 sm:justify-end">
                                        <span class="px-2.5 py-1 text-xs font-bold {{ $case->priority_level === 'High' ? 'bg-red-100 text-red-800' : ($case->priority_level === 'Medium' ? 'bg-[#f4eadf] text-[#7a5737]' : 'bg-[#eef0ec] text-[#554b45]') }}">{{ $case->priority_level ?: 'Low' }}</span>
                                        <span class="px-2.5 py-1 text-xs font-bold bg-[#eef0ec] text-[#554b45]">{{ $case->case_status ?: 'New' }}</span>
                                    </div>
                                </div>
                            </a>
                        @empty
                            <div class="px-5 py-8 text-center text-sm text-[#554b45]">No cases assigned to this lawyer yet.</div>
                        @endforelse
                    </div>
                </section>

                <section class="bg-white shadow-sm">
                    <div class="border-b border-[#e3e3df] px-5 py-4">
                        <h3 class="text-sm font-bold uppercase text-[#554b45]">Upcoming hearings</h3>
                    </div>
                    <div class="divide-y divide-[#e3e3df]">
                        @forelse($upcomingHearings as $hearing)
                            <a href="{{ route('hearings.show', $hearing->id) }}" class="block px-5 py-4 transition hover:bg-[#f8f8f6]">
                                <p class="font-bold text-[#030203]">{{ \Illuminate\Support\Carbon::parse($hearing->hearing_date)->format('M d, Y') }} @if($hearing->hearing_time)<span class="font-semibold text-[#554b45]">{{ \Illuminate\Support\Carbon::parse($hearing->hearing_time)->format('g:i A') }}</span>@endif</p>
                                <p class="mt-1 text-sm text-[#554b45]">{{ optional($hearing->case)->case_number }} / {{ $hearing->hearing_purpose ?: 'Hearing' }}</p>
                                <p class="mt-1 text-sm text-[#7a716b]">{{ $hearing->court_venue ?: 'Venue not recorded' }}</p>
                            </a>
                        @empty
                            <div class="px-5 py-8 text-center text-sm text-[#554b45]">No upcoming hearings for assigned cases.</div>
                        @endforelse
                    </div>
                </section>
            </div>

            <div class="flex items-center justify-between">
                <a href="{{ route('lawyers.index') }}" class="text-sm font-bold text-[#554b45] hover:text-[#030203]">Back to lawyers</a>
            </div>
        </div>
    </div>
</x-app-layout>
