<x-app-layout>
    <x-slot name="header"><div><p class="text-sm font-bold uppercase text-[#9f7957]">Case party</p><h2 class="mt-2 text-3xl font-extrabold text-[#030203]">New Opposing Party</h2></div></x-slot>
    <div class="bg-[#eef0ec] py-8"><div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        @if ($errors->any())<div class="mb-6 border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ $errors->first() }}</div>@endif
        <form method="POST" action="{{ route('opposing-parties.store') }}" class="bg-white p-6 shadow-sm">
            @csrf
            <div class="grid gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2"><label class="mb-2 block text-sm font-bold uppercase text-[#554b45]">Case / Client</label><select name="case_id" required class="w-full border-[#c1c1bd] bg-white text-[#030203] focus:border-[#9f7957] focus:ring-[#9f7957]">@foreach($cases as $c)<option value="{{ $c->id }}" @selected(old('case_id') == $c->id)>{{ $c->case_number }} - {{ $c->case_title }} / {{ optional($c->client)->full_name ?: 'No client' }}</option>@endforeach</select></div>
                <div class="sm:col-span-2 border-t border-[#e3e3df] pt-5"><p class="text-sm font-bold uppercase text-[#9f7957]">Party details</p></div>
                <div><label class="mb-2 block text-sm font-bold uppercase text-[#554b45]">Opposing Party Name</label><input type="text" name="opposing_party_name" value="{{ old('opposing_party_name') }}" required class="w-full border-[#c1c1bd] bg-white text-[#030203] focus:border-[#9f7957] focus:ring-[#9f7957]"></div>
                <div><label class="mb-2 block text-sm font-bold uppercase text-[#554b45]">Opposing Counsel</label><input type="text" name="opposing_counsel_name" value="{{ old('opposing_counsel_name') }}" class="w-full border-[#c1c1bd] bg-white text-[#030203] focus:border-[#9f7957] focus:ring-[#9f7957]"></div>
                <div><label class="mb-2 block text-sm font-bold uppercase text-[#554b45]">Contact</label><input type="text" name="contact_number" value="{{ old('contact_number') }}" class="w-full border-[#c1c1bd] bg-white text-[#030203] focus:border-[#9f7957] focus:ring-[#9f7957]"></div>
                <div><label class="mb-2 block text-sm font-bold uppercase text-[#554b45]">Email</label><input type="email" name="email" value="{{ old('email') }}" class="w-full border-[#c1c1bd] bg-white text-[#030203] focus:border-[#9f7957] focus:ring-[#9f7957]"></div>
                <div class="sm:col-span-2"><label class="mb-2 block text-sm font-bold uppercase text-[#554b45]">Address</label><textarea name="address" rows="4" class="w-full border-[#c1c1bd] bg-white text-[#030203] focus:border-[#9f7957] focus:ring-[#9f7957]">{{ old('address') }}</textarea></div>
            </div>
            <div class="mt-6 flex items-center justify-between border-t border-[#e3e3df] pt-5"><a href="{{ route('opposing-parties.index') }}" class="text-sm font-bold text-[#554b45] hover:text-[#030203]">Cancel</a><button class="bg-[#030203] px-6 py-3 text-sm font-bold text-white transition hover:bg-[#554b45]">Create party</button></div>
        </form>
    </div></div>
</x-app-layout>
