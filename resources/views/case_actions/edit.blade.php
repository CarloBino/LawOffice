<x-app-layout>
    <x-slot name="header"><div><p class="text-sm font-bold uppercase text-[#9f7957]">Matter tasks</p><h2 class="mt-2 text-3xl font-extrabold text-[#030203]">Edit Case Action</h2></div></x-slot>
    <div class="bg-[#eef0ec] py-8"><div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        @if ($errors->any())<div class="mb-6 border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ $errors->first() }}</div>@endif
        <form method="POST" action="{{ route('case-actions.update', $caseAction->id) }}" class="bg-white p-6 shadow-sm">
            @csrf @method('PUT')
            <div class="grid gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2"><label class="mb-2 block text-sm font-bold uppercase text-[#554b45]">Case / Client</label><select name="case_id" required class="w-full border-[#c1c1bd] bg-white text-[#030203] focus:border-[#9f7957] focus:ring-[#9f7957]">@foreach($cases as $c)<option value="{{ $c->id }}" @selected(old('case_id', $caseAction->case_id) == $c->id)>{{ $c->case_number }} - {{ $c->case_title }} / {{ optional($c->client)->full_name ?: 'No client' }}</option>@endforeach</select></div>
                <div><label class="mb-2 block text-sm font-bold uppercase text-[#554b45]">Action Type</label><input type="text" name="action_type" value="{{ old('action_type', $caseAction->action_type) }}" required class="w-full border-[#c1c1bd] bg-white text-[#030203] focus:border-[#9f7957] focus:ring-[#9f7957]"></div>
                <div><label class="mb-2 block text-sm font-bold uppercase text-[#554b45]">Responsible Person</label><input type="text" name="responsible_person" value="{{ old('responsible_person', $caseAction->responsible_person) }}" class="w-full border-[#c1c1bd] bg-white text-[#030203] focus:border-[#9f7957] focus:ring-[#9f7957]"></div>
                <div><label class="mb-2 block text-sm font-bold uppercase text-[#554b45]">Due Date</label><input type="date" name="due_date" value="{{ old('due_date', $caseAction->due_date) }}" class="w-full border-[#c1c1bd] bg-white text-[#030203] focus:border-[#9f7957] focus:ring-[#9f7957]"></div>
                <div><label class="mb-2 block text-sm font-bold uppercase text-[#554b45]">Date Completed</label><input type="date" name="date_completed" value="{{ old('date_completed', $caseAction->date_completed) }}" class="w-full border-[#c1c1bd] bg-white text-[#030203] focus:border-[#9f7957] focus:ring-[#9f7957]"></div>
                <div><label class="mb-2 block text-sm font-bold uppercase text-[#554b45]">Status</label><select name="action_status" class="w-full border-[#c1c1bd] bg-white text-[#030203] focus:border-[#9f7957] focus:ring-[#9f7957]">@foreach(['Pending','In Progress','Completed'] as $status)<option value="{{ $status }}" @selected(old('action_status', $caseAction->action_status) === $status)>{{ $status }}</option>@endforeach</select></div>
                <div class="sm:col-span-2"><label class="mb-2 block text-sm font-bold uppercase text-[#554b45]">Description</label><textarea name="action_description" rows="4" class="w-full border-[#c1c1bd] bg-white text-[#030203] focus:border-[#9f7957] focus:ring-[#9f7957]">{{ old('action_description', $caseAction->action_description) }}</textarea></div>
            </div>
            <div class="mt-6 flex items-center justify-between border-t border-[#e3e3df] pt-5"><a href="{{ route('case-actions.index') }}" class="text-sm font-bold text-[#554b45] hover:text-[#030203]">Cancel</a><button class="bg-[#030203] px-6 py-3 text-sm font-bold text-white transition hover:bg-[#554b45]">Save action</button></div>
        </form>
    </div></div>
</x-app-layout>
