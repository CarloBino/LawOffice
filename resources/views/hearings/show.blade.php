<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-bold uppercase text-[#9f7957]">Court calendar</p>
                <h2 class="mt-2 text-3xl font-extrabold text-[#030203]">Hearing</h2>
            </div>
            <a href="{{ route('hearings.edit', $hearing->id) }}" class="inline-flex items-center justify-center bg-[#030203] px-4 py-2 text-sm font-bold text-white transition hover:bg-[#554b45]">Edit hearing</a>
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
                    <div><p class="text-xs font-bold uppercase text-[#9f7957]">Assigned Lawyer</p><p class="mt-2 font-semibold text-[#030203]">{{ optional(optional($hearing->case)->assignedLawyer)->full_name ?: 'No lawyer assigned' }}</p></div>
                    <div><p class="text-xs font-bold uppercase text-[#9f7957]">Venue</p><p class="mt-2 font-semibold text-[#030203]">{{ $hearing->court_venue ?: 'Not recorded' }}</p></div>
                    <div><p class="text-xs font-bold uppercase text-[#9f7957]">Branch</p><p class="mt-2 font-semibold text-[#030203]">{{ $hearing->court_branch ?: 'Not recorded' }}</p></div>
                    <div><p class="text-xs font-bold uppercase text-[#9f7957]">Judge</p><p class="mt-2 font-semibold text-[#030203]">{{ $hearing->judge_name ?: 'Not recorded' }}</p></div>
                    <div><p class="text-xs font-bold uppercase text-[#9f7957]">Jurisdiction</p><p class="mt-2 font-semibold text-[#030203]">{{ $hearing->court_jurisdiction ?: 'Not recorded' }}</p></div>
                    <div class="sm:col-span-2"><p class="text-xs font-bold uppercase text-[#9f7957]">Purpose</p><p class="mt-2 whitespace-pre-line text-[#030203]">{{ $hearing->hearing_purpose ?: 'No purpose recorded.' }}</p></div>
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
