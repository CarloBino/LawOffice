<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-bold uppercase text-[#9f7957]">Office record</p>
            <h2 class="mt-2 text-3xl font-extrabold text-[#030203]">Activity Log</h2>
        </div>
    </x-slot>

    <div class="bg-[#eef0ec] py-8">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <section class="bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-[#e3e3df]">
                        <thead>
                            <tr class="text-left text-xs font-bold uppercase text-[#7a716b]">
                                <th class="px-5 py-4">Activity</th>
                                <th class="px-5 py-4">User</th>
                                <th class="px-5 py-4">Module</th>
                                <th class="px-5 py-4">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#e3e3df]">
                            @forelse($activities as $activity)
                                <tr class="transition hover:bg-[#f8f8f6]">
                                    <td class="px-5 py-4">
                                        <p class="font-bold text-[#030203]">{{ $activity->action }}</p>
                                        <p class="mt-1 text-sm text-[#554b45]">{{ $activity->description }}</p>
                                    </td>
                                    <td class="px-5 py-4 text-sm text-[#554b45]">{{ optional($activity->user)->name ?: 'System' }}</td>
                                    <td class="px-5 py-4 text-sm text-[#554b45]">{{ class_basename($activity->subject_type) ?: 'General' }}</td>
                                    <td class="px-5 py-4 text-sm text-[#554b45]">{{ $activity->created_at->format('M d, Y g:i A') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-5 py-12 text-center text-sm text-[#554b45]">No activity recorded yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
            <div class="mt-6">{{ $activities->links() }}</div>
        </div>
    </div>
</x-app-layout>
