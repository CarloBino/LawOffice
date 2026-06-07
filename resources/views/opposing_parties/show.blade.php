<x-app-layout>
    <x-slot name="header"><div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between"><div><p class="text-sm font-bold uppercase text-[#9f7957]">Case party</p><h2 class="mt-2 text-3xl font-extrabold text-[#030203]">{{ $party->opposing_party_name }}</h2></div>@unless(Auth::user()?->isLawyer())<a href="{{ route('opposing-parties.edit', $party->id) }}" class="inline-flex items-center justify-center bg-[#030203] px-4 py-2 text-sm font-bold text-white transition hover:bg-[#554b45]">Edit party</a>@endunless</div></x-slot>
    <div class="bg-[#eef0ec] py-8"><div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <section class="bg-white shadow-sm">
            <div class="border-b border-[#e3e3df] px-6 py-5">
                <p class="text-xs font-bold uppercase text-[#9f7957]">Opposing Party</p>
                <h3 class="mt-1 text-2xl font-extrabold text-[#030203]">{{ $party->opposing_party_name }}</h3>
            </div>
            <div class="grid gap-0 sm:grid-cols-2">
                <div class="border-b border-[#e3e3df] p-5 sm:border-r"><p class="text-xs font-bold uppercase text-[#9f7957]">Case</p><p class="mt-2 font-semibold text-[#030203]">{{ optional($party->case)->case_number ?: 'No case linked' }}</p><p class="mt-1 text-sm text-[#7a716b]">{{ optional($party->case)->case_title }}</p></div>
                <div class="border-b border-[#e3e3df] p-5"><p class="text-xs font-bold uppercase text-[#9f7957]">Client</p><p class="mt-2 font-semibold text-[#030203]">{{ optional(optional($party->case)->client)->full_name ?: 'No client linked' }}</p></div>
                <div class="border-b border-[#e3e3df] p-5 sm:border-r"><p class="text-xs font-bold uppercase text-[#9f7957]">Opposing Counsel</p><p class="mt-2 font-semibold text-[#030203]">{{ $party->opposing_counsel_name ?: 'Not recorded' }}</p></div>
                <div class="border-b border-[#e3e3df] p-5"><p class="text-xs font-bold uppercase text-[#9f7957]">Contact Number</p><p class="mt-2 font-semibold text-[#030203]">{{ $party->contact_number ?: 'Not recorded' }}</p></div>
                <div class="border-b border-[#e3e3df] p-5 sm:border-r"><p class="text-xs font-bold uppercase text-[#9f7957]">Email</p><p class="mt-2 font-semibold text-[#030203]">{{ $party->email ?: 'Not recorded' }}</p></div>
                <div class="border-b border-[#e3e3df] p-5"><p class="text-xs font-bold uppercase text-[#9f7957]">Address</p><p class="mt-2 text-[#030203]">{{ $party->address ?: 'Not recorded' }}</p></div>
            </div>
            <div class="flex items-center justify-between px-6 py-5"><a href="{{ route('opposing-parties.index') }}" class="text-sm font-bold text-[#554b45] hover:text-[#030203]">Back to opposing parties</a>@if($party->case)<a href="{{ route('cases.show', $party->case_id) }}" class="text-sm font-bold text-[#9f7957] hover:text-[#030203]">Open case</a>@endif</div>
        </section>
    </div></div>
</x-app-layout>
