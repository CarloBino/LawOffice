<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-bold uppercase text-[#9f7957]">Counsel directory</p>
                <h2 class="mt-2 text-3xl font-extrabold text-[#030203]">Lawyers</h2>
            </div>
            @unless(Auth::user()?->isLawyer())
                <a href="{{ route('lawyers.create') }}" class="inline-flex items-center justify-center bg-[#030203] px-4 py-2 text-sm font-bold text-white transition hover:bg-[#554b45]">New lawyer</a>
            @endunless
        </div>
    </x-slot>

    <div class="bg-[#eef0ec] py-8">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <section class="bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-[#e3e3df]">
                        <thead>
                            <tr class="text-left text-xs font-bold uppercase text-[#7a716b]">
                                <th class="px-5 py-4">Lawyer</th>
                                <th class="px-5 py-4">Contact</th>
                                <th class="px-5 py-4">Specialization</th>
                                <th class="px-5 py-4">Workload</th>
                                <th class="px-5 py-4">Status</th>
                                <th class="px-5 py-4 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#e3e3df]">
                            @forelse($lawyers as $lawyer)
                                <tr class="transition hover:bg-[#f8f8f6]">
                                    <td class="px-5 py-4">
                                        <a href="{{ route('lawyers.show', $lawyer->id) }}" class="font-bold text-[#030203] hover:text-[#9f7957]">{{ $lawyer->full_name }}</a>
                                        <p class="mt-1 text-sm text-[#554b45]">{{ $lawyer->email ?: 'No email recorded' }}</p>
                                    </td>
                                    <td class="px-5 py-4 text-sm text-[#554b45]">{{ $lawyer->contact_number ?: 'No contact number' }}</td>
                                    <td class="px-5 py-4 text-sm text-[#554b45]">{{ $lawyer->specialization ?: 'General practice' }}</td>
                                    <td class="px-5 py-4 text-sm text-[#030203]">
                                        <span class="font-bold">{{ $lawyer->cases->count() }}</span>
                                        <span class="text-[#7a716b]">assigned</span>
                                        <p class="mt-1 text-[#7a716b]">{{ $lawyer->cases->whereNotIn('case_status', ['Closed', 'Archived'])->count() }} open</p>
                                    </td>
                                    <td class="px-5 py-4"><span class="bg-[#eef0ec] px-3 py-1 text-xs font-bold text-[#554b45]">{{ $lawyer->status ?: 'Active' }}</span></td>
                                    <td class="px-5 py-4 text-right"><a href="{{ route('lawyers.show', $lawyer->id) }}" class="text-sm font-bold text-[#9f7957] hover:text-[#030203]">Manage</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-5 py-12 text-center text-sm text-[#554b45]">No lawyers yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
            <div class="mt-6">{{ $lawyers->links() }}</div>
        </div>
    </div>
</x-app-layout>
