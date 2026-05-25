<nav x-data="{ open: false }" class="border-b border-[#554b45] bg-[#030203]">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-[72px] justify-between">
            <div class="flex min-w-0">
                <div class="flex shrink-0 items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center">
                        <span class="flex h-10 w-10 items-center justify-center border border-[#9f7957] bg-[#c7a47b] text-xs font-extrabold text-[#030203]">LO</span>
                        <span class="ms-3 truncate text-xl font-bold text-white">{{ config('app.name') }}</span>
                    </a>
                </div>

                <div class="hidden space-x-5 sm:-my-px sm:ms-8 lg:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">{{ __('Dashboard') }}</x-nav-link>
                    <x-nav-link :href="route('cases.index')" :active="request()->routeIs('cases.*')">{{ __('Cases') }}</x-nav-link>
                    <x-nav-link :href="route('clients.index')" :active="request()->routeIs('clients.*')">{{ __('Clients') }}</x-nav-link>
                    <x-nav-link :href="route('hearings.index')" :active="request()->routeIs('hearings.*')">{{ __('Hearings') }}</x-nav-link>
                    <x-nav-link :href="route('billings.index')" :active="request()->routeIs('billings.*')">{{ __('Billings') }}</x-nav-link>
                    <x-nav-link :href="route('search.index')" :active="request()->routeIs('search.*')">{{ __('Search') }}</x-nav-link>

                    <x-dropdown align="left" width="48" contentClasses="py-1 bg-white">
                        <x-slot name="trigger">
                            <button class="inline-flex h-[72px] items-center border-b-2 px-1 pt-1 text-sm font-bold leading-5 transition duration-150 ease-in-out focus:outline-none {{ request()->routeIs('lawyers.*') || request()->routeIs('documents.*') || request()->routeIs('opposing-parties.*') ? 'border-[#c7a47b] text-white' : 'border-transparent text-[#d1d2cd] hover:border-[#9f7957] hover:text-white' }}">
                                <span>Records</span>
                                <svg class="ms-1 h-4 w-4 fill-current" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('lawyers.index')">{{ __('Lawyers') }}</x-dropdown-link>
                            <x-dropdown-link :href="route('documents.index')">{{ __('Documents') }}</x-dropdown-link>
                            <x-dropdown-link :href="route('opposing-parties.index')">{{ __('Opposing Parties') }}</x-dropdown-link>
                        </x-slot>
                    </x-dropdown>

                    @unless(Auth::user()?->isLawyer())
                        <x-dropdown align="left" width="48" contentClasses="py-1 bg-white">
                            <x-slot name="trigger">
                                <button class="inline-flex h-[72px] items-center border-b-2 px-1 pt-1 text-sm font-bold leading-5 transition duration-150 ease-in-out focus:outline-none {{ request()->routeIs('office-expenses.*') || request()->routeIs('activity-logs.*') || request()->routeIs('staff-users.*') ? 'border-[#c7a47b] text-white' : 'border-transparent text-[#d1d2cd] hover:border-[#9f7957] hover:text-white' }}">
                                    <span>Office</span>
                                    <svg class="ms-1 h-4 w-4 fill-current" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <x-dropdown-link :href="route('office-expenses.index')">{{ __('Expenses') }}</x-dropdown-link>
                                <x-dropdown-link :href="route('activity-logs.index')">{{ __('Activity Log') }}</x-dropdown-link>
                                @if(Auth::user()?->isAdmin())
                                    <x-dropdown-link :href="route('staff-users.index')">{{ __('Staff Accounts') }}</x-dropdown-link>
                                @endif
                            </x-slot>
                        </x-dropdown>
                    @endunless
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center border border-[#554b45] bg-[#181614] px-3 py-2 text-sm font-semibold leading-4 text-[#d1d2cd] transition hover:border-[#9f7957] hover:text-white focus:outline-none">
                            <div>{{ Auth::user()->name }}</div>
                            <div class="ms-2">
                                <svg class="h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">{{ __('Profile') }}</x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <div class="-me-2 flex items-center lg:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center border border-[#554b45] p-2 text-[#d1d2cd] transition hover:border-[#9f7957] hover:text-white focus:outline-none">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden lg:hidden">
        <div class="border-b border-[#554b45] px-4 py-3">
            <form method="GET" action="{{ route('search.index') }}">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Search..." class="w-full border border-[#554b45] bg-[#181614] text-sm font-semibold text-white placeholder:text-[#c1c1bd] focus:border-[#9f7957] focus:ring-[#9f7957]">
            </form>
        </div>
        <div class="space-y-1 pb-3 pt-2">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">{{ __('Dashboard') }}</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('cases.index')" :active="request()->routeIs('cases.*')">{{ __('Cases') }}</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('clients.index')" :active="request()->routeIs('clients.*')">{{ __('Clients') }}</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('hearings.index')" :active="request()->routeIs('hearings.*')">{{ __('Hearings') }}</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('billings.index')" :active="request()->routeIs('billings.*')">{{ __('Billings') }}</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('search.index')" :active="request()->routeIs('search.*')">{{ __('Search') }}</x-responsive-nav-link>
            <div class="px-4 pb-1 pt-3 text-xs font-bold uppercase text-[#9f7957]">Records</div>
            <x-responsive-nav-link :href="route('lawyers.index')" :active="request()->routeIs('lawyers.*')">{{ __('Lawyers') }}</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('documents.index')" :active="request()->routeIs('documents.*')">{{ __('Documents') }}</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('opposing-parties.index')" :active="request()->routeIs('opposing-parties.*')">{{ __('Opposing Parties') }}</x-responsive-nav-link>
            @unless(Auth::user()?->isLawyer())
                <div class="px-4 pb-1 pt-3 text-xs font-bold uppercase text-[#9f7957]">Office</div>
                <x-responsive-nav-link :href="route('office-expenses.index')" :active="request()->routeIs('office-expenses.*')">{{ __('Expenses') }}</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('activity-logs.index')" :active="request()->routeIs('activity-logs.*')">{{ __('Activity Log') }}</x-responsive-nav-link>
                @if(Auth::user()?->isAdmin())
                    <x-responsive-nav-link :href="route('staff-users.index')" :active="request()->routeIs('staff-users.*')">{{ __('Staff Accounts') }}</x-responsive-nav-link>
                @endif
            @endunless
        </div>

        <div class="border-t border-[#554b45] pb-1 pt-4">
            <div class="px-4">
                <div class="text-base font-semibold text-white">{{ Auth::user()->name }}</div>
                <div class="text-sm font-medium text-[#c1c1bd]">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">{{ __('Profile') }}</x-responsive-nav-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
