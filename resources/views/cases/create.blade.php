<x-app-layout>
    <x-slot name="header"><div><p class="text-sm font-bold uppercase text-[#9f7957]">Matter intake</p><h2 class="mt-2 text-3xl font-extrabold text-[#030203]">New Case</h2></div></x-slot>
    <div class="bg-[#eef0ec] py-8"><div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        @if ($errors->any())<div class="mb-6 border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ $errors->first() }}</div>@endif
        <form method="POST" action="{{ route('cases.store') }}" class="bg-white p-6 shadow-sm">
            @csrf
            <div class="grid gap-5 sm:grid-cols-2">
                <div><label class="mb-2 block text-sm font-bold uppercase text-[#554b45]">Case Number</label><input type="text" name="case_number" value="{{ old('case_number') }}" required class="w-full border-[#c1c1bd] bg-white text-[#030203] focus:border-[#9f7957] focus:ring-[#9f7957]"></div>
                <div><label class="mb-2 block text-sm font-bold uppercase text-[#554b45]">Date Filed</label><input type="date" name="date_filed" value="{{ old('date_filed') }}" class="w-full border-[#c1c1bd] bg-white text-[#030203] focus:border-[#9f7957] focus:ring-[#9f7957]"></div>
                <div class="sm:col-span-2"><label class="mb-2 block text-sm font-bold uppercase text-[#554b45]">Case Title</label><input type="text" name="case_title" value="{{ old('case_title') }}" required class="w-full border-[#c1c1bd] bg-white text-[#030203] focus:border-[#9f7957] focus:ring-[#9f7957]"></div>
                <div><label class="mb-2 block text-sm font-bold uppercase text-[#554b45]">Client</label><select name="client_id" class="w-full border-[#c1c1bd] bg-white text-[#030203] focus:border-[#9f7957] focus:ring-[#9f7957]"><option value="">Select client</option>@foreach($clients as $client)<option value="{{ $client->id }}" @selected(old('client_id', $selectedClientId ?? null) == $client->id)>{{ $client->full_name }}</option>@endforeach</select></div>
                <div><label class="mb-2 block text-sm font-bold uppercase text-[#554b45]">Assigned Lawyer</label><select name="assigned_lawyer_id" class="w-full border-[#c1c1bd] bg-white text-[#030203] focus:border-[#9f7957] focus:ring-[#9f7957]"><option value="">Select lawyer</option>@foreach($lawyers as $lawyer)<option value="{{ $lawyer->id }}" @selected(old('assigned_lawyer_id') == $lawyer->id)>{{ $lawyer->display_name }}</option>@endforeach</select></div>
                <div><label class="mb-2 block text-sm font-bold uppercase text-[#554b45]">Case Type</label><select name="case_type" class="w-full border-[#c1c1bd] bg-white text-[#030203] focus:border-[#9f7957] focus:ring-[#9f7957]"><option value="">Select type</option>@foreach($caseTypes as $caseType)<option value="{{ $caseType }}" @selected(old('case_type') === $caseType)>{{ $caseType }}</option>@endforeach</select></div>
                <div><label class="mb-2 block text-sm font-bold uppercase text-[#554b45]">Priority</label><select name="priority_level" class="w-full border-[#c1c1bd] bg-white text-[#030203] focus:border-[#9f7957] focus:ring-[#9f7957]"><option value="Low">Low</option><option value="Medium">Medium</option><option value="High">High</option></select></div>
                <div class="sm:col-span-2"><label class="mb-2 block text-sm font-bold uppercase text-[#554b45]">Description</label><textarea name="description" rows="5" class="w-full border-[#c1c1bd] bg-white text-[#030203] focus:border-[#9f7957] focus:ring-[#9f7957]">{{ old('description') }}</textarea></div>
            </div>
            <div class="mt-6 flex items-center justify-between border-t border-[#e3e3df] pt-5"><a href="{{ route('cases.index') }}" class="text-sm font-bold text-[#554b45] hover:text-[#030203]">Cancel</a><button class="bg-[#030203] px-6 py-3 text-sm font-bold text-white transition hover:bg-[#554b45]">Create case</button></div>
        </form>
    </div></div>
</x-app-layout>
