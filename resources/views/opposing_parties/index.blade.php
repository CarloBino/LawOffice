<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-bold uppercase text-[#9f7957]">Case parties</p>
                <h2 class="mt-2 text-3xl font-extrabold text-[#030203]">Opposing Parties</h2>
            </div>
            <a href="{{ route('opposing-parties.create') }}" class="inline-flex items-center justify-center bg-[#030203] px-4 py-2 text-sm font-bold text-white transition hover:bg-[#554b45]">New party</a>
        </div>
    </x-slot>
    <div class="bg-[#eef0ec] py-8"><div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <section class="bg-white shadow-sm"><div class="overflow-x-auto"><table class="min-w-full divide-y divide-[#e3e3df]">
            <thead><tr class="text-left text-xs font-bold uppercase text-[#7a716b]"><th class="px-5 py-4">Opposing Party</th><th class="px-5 py-4">Case</th><th class="px-5 py-4">Client</th><th class="px-5 py-4">Counsel</th><th class="px-5 py-4">Contact</th><th class="px-5 py-4 text-right">Action</th></tr></thead>
            <tbody class="divide-y divide-[#e3e3df]">
                @forelse($parties as $p)
                    <tr class="transition hover:bg-[#f8f8f6]">
                        <td class="px-5 py-4">
                            <a href="{{ route('opposing-parties.show', $p->id) }}" class="font-bold text-[#030203] hover:text-[#9f7957]">{{ $p->opposing_party_name }}</a>
                            <p class="mt-1 text-sm text-[#554b45]">{{ $p->address ?: 'No address recorded' }}</p>
                        </td>
                        <td class="px-5 py-4 text-sm text-[#030203]"><span class="font-semibold">{{ optional($p->case)->case_number ?: 'No case linked' }}</span><p class="mt-1 text-[#7a716b]">{{ optional($p->case)->case_title }}</p></td>
                        <td class="px-5 py-4 text-sm text-[#554b45]">{{ optional(optional($p->case)->client)->full_name ?: 'No client linked' }}</td>
                        <td class="px-5 py-4 text-sm text-[#554b45]">{{ $p->opposing_counsel_name ?: 'Not recorded' }}</td>
                        <td class="px-5 py-4 text-sm text-[#554b45]"><p>{{ $p->email ?: 'No email recorded' }}</p><p class="mt-1">{{ $p->contact_number ?: 'No contact number' }}</p></td>
                        <td class="px-5 py-4 text-right"><a href="{{ route('opposing-parties.show', $p->id) }}" class="text-sm font-bold text-[#9f7957] hover:text-[#030203]">Manage</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-12 text-center"><p class="font-bold text-[#030203]">No opposing parties yet</p><p class="mt-2 text-sm text-[#554b45]">Add the opposing party after opening a case file.</p></td></tr>
                @endforelse
            </tbody>
        </table></div></section>
        <div class="mt-6">{{ $parties->links() }}</div>
    </div></div>
</x-app-layout>
