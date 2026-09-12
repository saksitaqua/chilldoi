<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex items-center">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                    <x-nav-link :href="route('admin.calendar.index')" :active="request()->routeIs('admin.calendar.*')">
                        {{ __('ปฏิทิน') }}
                    </x-nav-link>
                    <x-nav-link :href="route('admin.bookings.index')" :active="request()->routeIs('admin.bookings.*')">
                        {{ __('การจอง') }}
                    </x-nav-link>

                    @php
                        $accommodationActive = request()->routeIs(['admin.types.*', 'admin.units.*', 'admin.services.*']);
                        $postActive = request()->routeIs(['admin.stories.*', 'admin.activities.*']);
                        $generalActive = request()->routeIs('admin.expenses.*');
                    @endphp

                    <x-dropdown align="left" width="56">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center gap-1 px-1 pt-1 border-b-2 {{ $accommodationActive ? 'border-indigo-400 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium leading-5 focus:outline-none transition duration-150 ease-in-out">
                                {{ __('ข้อมูลที่พัก/บริการ') }}
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <x-dropdown-link :href="route('admin.types.index')">{{ __('ประเภทห้อง') }}</x-dropdown-link>
                            <x-dropdown-link :href="route('admin.units.index')">{{ __('บ้านพัก / จุดกางเต็นท์') }}</x-dropdown-link>
                            <x-dropdown-link :href="route('admin.services.index')">{{ __('บริการเสริม') }}</x-dropdown-link>
                        </x-slot>
                    </x-dropdown>

                    <x-dropdown align="left" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center gap-1 px-1 pt-1 border-b-2 {{ $postActive ? 'border-indigo-400 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium leading-5 focus:outline-none transition duration-150 ease-in-out">
                                {{ __('ข้อมูลโพสต์') }}
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <x-dropdown-link :href="route('admin.stories.index')">{{ __('โพสต์ / แนะนำ') }}</x-dropdown-link>
                            <x-dropdown-link :href="route('admin.activities.index')">{{ __('กิจกรรม') }}</x-dropdown-link>
                        </x-slot>
                    </x-dropdown>

                    <x-dropdown align="left" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center gap-1 px-1 pt-1 border-b-2 {{ $generalActive ? 'border-indigo-400 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium leading-5 focus:outline-none transition duration-150 ease-in-out">
                                {{ __('บันทึกทั่วไป') }}
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <x-dropdown-link :href="route('admin.expenses.index', ['type' => 'income'])">{{ __('บันทึกรายรับ') }}</x-dropdown-link>
                            <x-dropdown-link :href="route('admin.expenses.index', ['type' => 'expense'])">{{ __('บันทึกรายจ่าย') }}</x-dropdown-link>
                        </x-slot>
                    </x-dropdown>

                    <x-nav-link :href="route('admin.banners.index')" :active="request()->routeIs('admin.banners.*')">
                        {{ __('แบนเนอร์') }}
                    </x-nav-link>
                    <x-nav-link :href="route('admin.reports.summary')" :active="request()->routeIs('admin.reports.*')">
                        {{ __('รายงานสรุป') }}
                    </x-nav-link>
                    @if (Auth::user()->isAdmin())
                        <x-nav-link :href="route('admin.employees.index')" :active="request()->routeIs('admin.employees.*')">
                            {{ __('พนักงาน') }}
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('admin.calendar.index')" :active="request()->routeIs('admin.calendar.*')">
                {{ __('ปฏิทิน') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('admin.bookings.index')" :active="request()->routeIs('admin.bookings.*')">
                {{ __('การจอง') }}
            </x-responsive-nav-link>
            <div class="px-4 pt-3 pb-1 text-xs font-semibold text-gray-400 uppercase">{{ __('ข้อมูลที่พัก/บริการ') }}</div>
            <x-responsive-nav-link :href="route('admin.types.index')" :active="request()->routeIs('admin.types.*')">
                {{ __('ประเภทห้อง') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('admin.units.index')" :active="request()->routeIs('admin.units.*')">
                {{ __('บ้านพัก / จุดกางเต็นท์') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('admin.services.index')" :active="request()->routeIs('admin.services.*')">
                {{ __('บริการเสริม') }}
            </x-responsive-nav-link>

            <div class="px-4 pt-3 pb-1 text-xs font-semibold text-gray-400 uppercase">{{ __('ข้อมูลโพสต์') }}</div>
            <x-responsive-nav-link :href="route('admin.stories.index')" :active="request()->routeIs('admin.stories.*')">
                {{ __('โพสต์ / แนะนำ') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('admin.activities.index')" :active="request()->routeIs('admin.activities.*')">
                {{ __('กิจกรรม') }}
            </x-responsive-nav-link>

            <div class="px-4 pt-3 pb-1 text-xs font-semibold text-gray-400 uppercase">{{ __('บันทึกทั่วไป') }}</div>
            <x-responsive-nav-link :href="route('admin.expenses.index', ['type' => 'income'])" :active="request()->routeIs('admin.expenses.*') && request('type') === 'income'">
                {{ __('บันทึกรายรับ') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('admin.expenses.index', ['type' => 'expense'])" :active="request()->routeIs('admin.expenses.*') && request('type') !== 'income'">
                {{ __('บันทึกรายจ่าย') }}
            </x-responsive-nav-link>

            <div class="px-4 pt-3 pb-1 text-xs font-semibold text-gray-400 uppercase">{{ __('อื่นๆ') }}</div>
            <x-responsive-nav-link :href="route('admin.banners.index')" :active="request()->routeIs('admin.banners.*')">
                {{ __('แบนเนอร์') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('admin.reports.summary')" :active="request()->routeIs('admin.reports.*')">
                {{ __('รายงานสรุป') }}
            </x-responsive-nav-link>
            @if (Auth::user()->isAdmin())
                <x-responsive-nav-link :href="route('admin.employees.index')" :active="request()->routeIs('admin.employees.*')">
                    {{ __('พนักงาน') }}
                </x-responsive-nav-link>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
