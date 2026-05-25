<x-app-layout>
    <x-slot name="header"><div><p class="text-sm font-bold uppercase text-[#9f7957]">Counsel record</p><h2 class="mt-2 text-3xl font-extrabold text-[#030203]">Edit Lawyer</h2></div></x-slot>
    <div class="bg-[#eef0ec] py-8"><div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        @if ($errors->any())<div class="mb-6 border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ $errors->first() }}</div>@endif
        <form method="POST" action="{{ route('lawyers.update', $lawyer->id) }}" class="bg-white p-6 shadow-sm">
            @csrf @method('PUT')
            <div class="grid gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2"><label class="mb-2 block text-sm font-bold uppercase text-[#554b45]">Full Name</label><input type="text" name="full_name" value="{{ old('full_name', $lawyer->full_name) }}" required class="w-full border-[#c1c1bd] bg-white text-[#030203] focus:border-[#9f7957] focus:ring-[#9f7957]"></div>
                <div><label class="mb-2 block text-sm font-bold uppercase text-[#554b45]">Contact</label><input type="text" name="contact_number" value="{{ old('contact_number', $lawyer->contact_number) }}" class="w-full border-[#c1c1bd] bg-white text-[#030203] focus:border-[#9f7957] focus:ring-[#9f7957]"></div>
                <div><label class="mb-2 block text-sm font-bold uppercase text-[#554b45]">Email</label><input type="email" name="email" value="{{ old('email', $lawyer->email) }}" class="w-full border-[#c1c1bd] bg-white text-[#030203] focus:border-[#9f7957] focus:ring-[#9f7957]"></div>
                <div><label class="mb-2 block text-sm font-bold uppercase text-[#554b45]">Specialization</label><input type="text" name="specialization" value="{{ old('specialization', $lawyer->specialization) }}" class="w-full border-[#c1c1bd] bg-white text-[#030203] focus:border-[#9f7957] focus:ring-[#9f7957]"></div>
                <div><label class="mb-2 block text-sm font-bold uppercase text-[#554b45]">Status</label><select name="status" class="w-full border-[#c1c1bd] bg-white text-[#030203] focus:border-[#9f7957] focus:ring-[#9f7957]"><option value="Active" @selected(old('status', $lawyer->status) === 'Active')>Active</option><option value="Inactive" @selected(old('status', $lawyer->status) === 'Inactive')>Inactive</option></select></div>
            </div>
            <div class="mt-6 flex items-center justify-between border-t border-[#e3e3df] pt-5"><a href="{{ route('lawyers.index') }}" class="text-sm font-bold text-[#554b45] hover:text-[#030203]">Cancel</a><button class="bg-[#030203] px-6 py-3 text-sm font-bold text-white transition hover:bg-[#554b45]">Save lawyer</button></div>
        </form>
    </div></div>
</x-app-layout>
