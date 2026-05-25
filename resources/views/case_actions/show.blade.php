<x-app-layout>
    <x-slot name="header"><div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between"><div><p class="text-sm font-bold uppercase text-[#9f7957]">Matter tasks</p><h2 class="mt-2 text-3xl font-extrabold text-[#030203]">Case Action</h2></div><a href="{{ route('case-actions.edit', $caseAction->id) }}" class="inline-flex items-center justify-center bg-[#030203] px-5 py-3 text-sm font-bold text-white transition hover:bg-[#554b45]">Edit action</a></div></x-slot>
    <div class="bg-[#eef0ec] py-8"><div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8"><div class="bg-white shadow-sm">
        <div class="border-b border-[#e3e3df] px-6 py-5"><p class="text-sm font-bold uppercase text-[#9f7957]">{{ $caseAction->action_status ?: 'Pending' }}</p><p class="mt-2 text-2xl font-extrabold text-[#030203]">{{ $caseAction->action_type }}</p></div>
        <div class="grid gap-0 sm:grid-cols-2">
            <div class="border-b border-[#e3e3df] p-5 sm:border-r"><p class="text-xs font-bold uppercase text-[#9f7957]">Case</p><p class="mt-2 font-semibold text-[#030203]">{{ optional($caseAction->case)->case_number ?: 'No case linked' }}</p></div>
            <div class="border-b border-[#e3e3df] p-5"><p class="text-xs font-bold uppercase text-[#9f7957]">Client</p><p class="mt-2 font-semibold text-[#030203]">{{ optional(optional($caseAction->case)->client)->full_name ?: 'No client linked' }}</p></div>
            <div class="border-b border-[#e3e3df] p-5 sm:border-r"><p class="text-xs font-bold uppercase text-[#9f7957]">Responsible Person</p><p class="mt-2 font-semibold text-[#030203]">{{ $caseAction->responsible_person ?: 'Not recorded' }}</p></div>
            <div class="border-b border-[#e3e3df] p-5"><p class="text-xs font-bold uppercase text-[#9f7957]">Due</p><p class="mt-2 font-semibold text-[#030203]">{{ $caseAction->due_date ? \Illuminate\Support\Carbon::parse($caseAction->due_date)->format('M d, Y') : 'Not recorded' }}</p></div>
            <div class="border-b border-[#e3e3df] p-5 sm:col-span-2"><p class="text-xs font-bold uppercase text-[#9f7957]">Description</p><p class="mt-2 text-[#030203]">{{ $caseAction->action_description ?: 'No description recorded.' }}</p></div>
        </div>
        <div class="flex items-center justify-between px-6 py-5"><a href="{{ route('case-actions.index') }}" class="text-sm font-bold text-[#554b45] hover:text-[#030203]">Back to actions</a>@if($caseAction->case)<a href="{{ route('cases.show', $caseAction->case_id) }}" class="text-sm font-bold text-[#9f7957] hover:text-[#030203]">Open case</a>@endif</div>
    </div></div></div>
</x-app-layout>
