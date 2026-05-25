<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-bold uppercase text-[#9f7957]">Matter tasks</p>
                <h2 class="mt-2 text-3xl font-extrabold text-[#030203]">Case Actions</h2>
            </div>
            <a href="{{ route('case-actions.create') }}" class="inline-flex items-center justify-center bg-[#030203] px-5 py-3 text-sm font-bold text-white transition hover:bg-[#554b45]">New action</a>
        </div>
    </x-slot>

    <div class="bg-[#eef0ec] py-8">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <section class="bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-[#e3e3df]">
                        <thead>
                            <tr class="text-left text-xs font-bold uppercase text-[#7a716b]">
                                <th class="px-5 py-4">Action</th>
                                <th class="px-5 py-4">Case</th>
                                <th class="px-5 py-4">Client</th>
                                <th class="px-5 py-4">Due</th>
                                <th class="px-5 py-4">Status</th>
                                <th class="px-5 py-4 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#e3e3df]">
                            @forelse($actions as $action)
                                <tr class="transition hover:bg-[#f8f8f6]">
                                    <td class="px-5 py-4">
                                        <a href="{{ route('case-actions.show', $action->id) }}" class="font-bold text-[#030203] hover:text-[#9f7957]">{{ $action->action_type }}</a>
                                        <p class="mt-1 text-sm text-[#554b45]">{{ $action->responsible_person ?: 'No person assigned' }}</p>
                                    </td>
                                    <td class="px-5 py-4 text-sm text-[#554b45]">{{ optional($action->case)->case_number ?: 'No case linked' }}</td>
                                    <td class="px-5 py-4 text-sm text-[#554b45]">{{ optional(optional($action->case)->client)->full_name ?: 'No client linked' }}</td>
                                    <td class="px-5 py-4 text-sm text-[#554b45]">{{ $action->due_date ? \Illuminate\Support\Carbon::parse($action->due_date)->format('M d, Y') : 'Not recorded' }}</td>
                                    <td class="px-5 py-4">
                                        <span class="px-3 py-1 text-xs font-bold {{ $action->action_status === 'Completed' ? 'bg-emerald-100 text-emerald-800' : ($action->action_status === 'In Progress' ? 'bg-[#c7a47b] text-[#030203]' : 'bg-red-100 text-red-800') }}">{{ $action->action_status ?: 'Pending' }}</span>
                                    </td>
                                    <td class="px-5 py-4 text-right"><a href="{{ route('case-actions.show', $action->id) }}" class="text-sm font-bold text-[#9f7957] hover:text-[#030203]">Open</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-5 py-12 text-center text-sm text-[#554b45]">No case actions recorded yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
            <div class="mt-6">{{ $actions->links() }}</div>
        </div>
    </div>
</x-app-layout>
