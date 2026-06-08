<x-guest-layout>
    <div>
        <p class="text-xs font-bold uppercase text-[#9f7957]">Authorized access</p>
        <h2 class="mt-3 text-4xl font-extrabold text-[#030203]">Welcome back</h2>
        <p class="mt-3 text-sm leading-6 text-[#554b45]">Sign in with the account provided by your administrator.</p>
    </div>

    <x-auth-session-status class="mt-6 border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-800" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="mt-8">
        @csrf

        <div>
            <label for="email" class="block text-sm font-bold text-[#302a27]">Email address</label>
            <input
                id="email"
                class="mt-2 block h-12 w-full border border-[#b8b5af] bg-white px-4 text-[#030203] shadow-none transition placeholder:text-[#8a837d] focus:border-[#9f7957] focus:ring-[#9f7957]"
                type="email"
                name="email"
                value="{{ old('email') }}"
                placeholder="name@lawoffice.com"
                required
                autofocus
                autocomplete="username"
            >
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-sm font-semibold" />
        </div>

        <div class="mt-5">
            <div class="flex items-center justify-between gap-4">
                <label for="password" class="block text-sm font-bold text-[#302a27]">Password</label>
                @if (Route::has('password.request'))
                    <a class="text-sm font-bold text-[#9f7957] hover:text-[#030203]" href="{{ route('password.request') }}">
                        Forgot password?
                    </a>
                @endif
            </div>
            <input
                id="password"
                class="mt-2 block h-12 w-full border border-[#b8b5af] bg-white px-4 text-[#030203] shadow-none transition focus:border-[#9f7957] focus:ring-[#9f7957]"
                type="password"
                name="password"
                required
                autocomplete="current-password"
            >
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-sm font-semibold" />
        </div>

        <div class="mt-5">
            <label for="remember_me" class="inline-flex items-center gap-3">
                <input id="remember_me" type="checkbox" class="h-4 w-4 border-[#9f9b95] text-[#030203] focus:ring-[#9f7957]" name="remember">
                <span class="text-sm text-[#554b45]">Keep me signed in on this device</span>
            </label>
        </div>

        <button type="submit" class="mt-7 flex h-12 w-full items-center justify-center bg-[#030203] px-5 text-sm font-bold text-white transition hover:bg-[#554b45] focus:outline-none focus:ring-2 focus:ring-[#9f7957] focus:ring-offset-2">
            Sign in
        </button>
    </form>

    <div class="mt-8 border-t border-[#d1d2cd] pt-5">
        <p class="text-xs leading-5 text-[#6c635e]">Accounts are created and managed by the office administrator. Contact your administrator if you need access.</p>
    </div>
</x-guest-layout>
