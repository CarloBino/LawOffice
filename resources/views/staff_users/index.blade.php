<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-bold uppercase text-[#9f7957]">Administration</p>
                <h2 class="mt-2 text-3xl font-extrabold text-[#030203]">Staff Accounts</h2>
            </div>
            <a href="{{ route('staff-users.create') }}" class="inline-flex items-center justify-center bg-[#030203] px-4 py-2 text-sm font-bold text-white transition hover:bg-[#554b45]">New account</a>
        </div>
    </x-slot>

    <div class="bg-[#eef0ec] py-8">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            @if(session('status'))
                <div class="mb-4 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('status') }}</div>
            @endif
            @if($errors->has('account'))
                <div class="mb-4 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800">{{ $errors->first('account') }}</div>
            @endif

            <section class="bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-[#e3e3df]">
                        <thead>
                            <tr class="text-left text-xs font-bold uppercase text-[#7a716b]">
                                <th class="px-5 py-4">User</th>
                                <th class="px-5 py-4">Role</th>
                                <th class="px-5 py-4">Status</th>
                                <th class="px-5 py-4">Created</th>
                                <th class="px-5 py-4 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#e3e3df]">
                            @forelse($users as $user)
                                <tr>
                                    <td class="px-5 py-4">
                                        <p class="font-bold text-[#030203]">{{ $user->name }}</p>
                                        <p class="mt-1 text-sm text-[#554b45]">{{ $user->email }}</p>
                                    </td>
                                    <td class="px-5 py-4 text-sm font-semibold capitalize text-[#030203]">{{ $user->role ?: 'staff' }}</td>
                                    <td class="px-5 py-4">
                                        <span class="px-3 py-1 text-xs font-bold {{ $user->status === 'inactive' ? 'bg-red-100 text-red-800' : 'bg-emerald-100 text-emerald-800' }}">{{ ucfirst($user->status ?: 'active') }}</span>
                                    </td>
                                    <td class="px-5 py-4 text-sm text-[#554b45]">{{ $user->created_at ? $user->created_at->format('M d, Y') : 'Not recorded' }}</td>
                                    <td class="px-5 py-4 text-right">
                                        <div class="flex justify-end gap-4">
                                            <a href="{{ route('staff-users.edit', $user) }}" class="text-sm font-bold text-[#9f7957] hover:text-[#030203]">Edit</a>
                                            @unless($user->is(Auth::user()))
                                                <form method="POST" action="{{ route('staff-users.destroy', $user) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-sm font-bold text-red-700 hover:text-[#030203]" onclick="return confirm('Delete this account? This action cannot be undone.')">Delete</button>
                                                </form>
                                            @endunless
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-12 text-center text-sm text-[#554b45]">No staff accounts yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
            <div class="mt-6">{{ $users->links() }}</div>
        </div>
    </div>
</x-app-layout>
