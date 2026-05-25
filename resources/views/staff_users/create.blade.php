<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-sm font-bold uppercase text-[#9f7957]">Administration</p>
            <h2 class="mt-2 text-3xl font-extrabold text-[#030203]">New Staff Account</h2>
        </div>
    </x-slot>

    <div class="bg-[#eef0ec] py-8">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('staff-users.store') }}" class="bg-white p-8 shadow-sm">
                @csrf

                <div class="grid gap-5 sm:grid-cols-2">
                    <label class="sm:col-span-2 text-sm font-bold uppercase text-[#554b45]">
                        Full Name
                        <input type="text" name="name" value="{{ old('name') }}" required autofocus class="mt-2 w-full border-[#c1c1bd] text-[#030203] focus:border-[#9f7957] focus:ring-[#9f7957]">
                        @error('name')<span class="mt-1 block text-xs font-semibold text-red-700">{{ $message }}</span>@enderror
                    </label>

                    <label class="sm:col-span-2 text-sm font-bold uppercase text-[#554b45]">
                        Email
                        <input type="email" name="email" value="{{ old('email') }}" required class="mt-2 w-full border-[#c1c1bd] text-[#030203] focus:border-[#9f7957] focus:ring-[#9f7957]">
                        @error('email')<span class="mt-1 block text-xs font-semibold text-red-700">{{ $message }}</span>@enderror
                    </label>

                    <label class="text-sm font-bold uppercase text-[#554b45]">
                        Role
                        <select name="role" required class="mt-2 w-full border-[#c1c1bd] text-[#030203] focus:border-[#9f7957] focus:ring-[#9f7957]">
                            <option value="staff" @selected(old('role', 'staff') === 'staff')>Staff</option>
                            <option value="secretary" @selected(old('role') === 'secretary')>Secretary</option>
                            <option value="lawyer" @selected(old('role') === 'lawyer')>Lawyer</option>
                            <option value="admin" @selected(old('role') === 'admin')>Administrator</option>
                        </select>
                        @error('role')<span class="mt-1 block text-xs font-semibold text-red-700">{{ $message }}</span>@enderror
                    </label>

                    <label class="text-sm font-bold uppercase text-[#554b45]">
                        Status
                        <select name="status" required class="mt-2 w-full border-[#c1c1bd] text-[#030203] focus:border-[#9f7957] focus:ring-[#9f7957]">
                            <option value="active" @selected(old('status', 'active') === 'active')>Active</option>
                            <option value="inactive" @selected(old('status') === 'inactive')>Inactive</option>
                        </select>
                        @error('status')<span class="mt-1 block text-xs font-semibold text-red-700">{{ $message }}</span>@enderror
                    </label>

                    <label class="text-sm font-bold uppercase text-[#554b45]">
                        Password
                        <input type="password" name="password" required class="mt-2 w-full border-[#c1c1bd] text-[#030203] focus:border-[#9f7957] focus:ring-[#9f7957]">
                        @error('password')<span class="mt-1 block text-xs font-semibold text-red-700">{{ $message }}</span>@enderror
                    </label>

                    <label class="text-sm font-bold uppercase text-[#554b45]">
                        Confirm Password
                        <input type="password" name="password_confirmation" required class="mt-2 w-full border-[#c1c1bd] text-[#030203] focus:border-[#9f7957] focus:ring-[#9f7957]">
                    </label>
                </div>

                <div class="mt-8 flex items-center justify-between border-t border-[#e3e3df] pt-6">
                    <a href="{{ route('staff-users.index') }}" class="text-sm font-bold text-[#554b45] hover:text-[#030203]">Cancel</a>
                    <button type="submit" class="bg-[#030203] px-5 py-3 text-sm font-bold text-white transition hover:bg-[#554b45]">Create account</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
