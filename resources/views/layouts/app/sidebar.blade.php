<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    @php
        $user = auth()->user();
        $isOwner = $user?->isOwner();
        $initialView = request('view', session('view_mode', 'mobile'));
        session(['view_mode' => $initialView]);
        $unreadNotificationsCount = \App\Models\Notification::where('is_read', false)->count();
    @endphp
    <body
        x-data="{
            deviceView: localStorage.getItem('fc_device_view') || '{{ $initialView }}',
            setDeviceView(mode) {
                this.deviceView = mode;
                localStorage.setItem('fc_device_view', mode);
                window.dispatchEvent(new CustomEvent('device-view-changed', { detail: mode }));
            }
        }"
        x-init="
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('view')) {
                setDeviceView(urlParams.get('view'));
            }
        "
        class="min-h-screen bg-neutral-950 text-[var(--fc-text)] antialiased transition-colors duration-200"
    >
        <div class="min-h-screen" :class="deviceView === 'mobile' ? 'bg-neutral-900/60 flex flex-col items-center justify-start sm:py-4' : 'flex'">
            <!-- Desktop & Tablet Sidebar -->
            <aside
                x-show="deviceView === 'desktop'"
                class="hidden lg:flex lg:w-72 lg:flex-col border-e border-[var(--fc-border)] bg-[var(--fc-sidebar)] text-[var(--fc-sidebar-text)] shrink-0 transition-colors duration-200"
            >
                <!-- Brand Header -->
                <div class="flex h-16 items-center px-5 border-b border-[var(--fc-border)]/20 justify-between">
                    <x-app-logo :sidebar="true" />
                </div>

                <!-- Navigation List -->
                <div class="flex-1 overflow-y-auto px-3 py-4 space-y-6 text-sm">
                    @php
                        $user = auth()->user();
                        $isOwner = $user?->isOwner();
                        $unreadNotificationsCount = \App\Models\Notification::where('is_read', false)->count();
                    @endphp

                    <!-- 3-Role Section Switcher -->
                    <div class="p-2.5 rounded-xl bg-[var(--fc-sidebar-hover)]/70 border border-[var(--fc-border)]/30 space-y-1.5 text-xs mb-2">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-[var(--fc-sidebar-muted)] block px-1">
                            {{ $isOwner ? '👑 ওনার সেকশন নেভিগেশন' : '🧑‍🍳 কার্টবয় কাউন্টার' }}
                        </span>

                        <div class="grid grid-cols-1 gap-1">
                            <a href="{{ route('home') }}" target="_blank" class="flex items-center justify-between px-2.5 py-1.5 rounded-lg text-emerald-400 hover:bg-[var(--fc-sidebar)]/80 transition-colors font-bold">
                                <span class="flex items-center gap-2">
                                    <span>🌐</span>
                                    <span>কাস্টমার মেনু</span>
                                </span>
                                <span class="text-[10px] text-slate-400">&nearr;</span>
                            </a>

                            <a href="{{ route('cartboy.index') }}" class="flex items-center justify-between px-2.5 py-1.5 rounded-lg {{ request()->routeIs('cartboy.*') ? 'bg-amber-500 text-slate-950 font-black' : 'text-amber-300 hover:bg-[var(--fc-sidebar)]/80 font-bold' }} transition-colors">
                                <span class="flex items-center gap-2">
                                    <span>🧑‍🍳</span>
                                    <span>কার্টবয় কাউন্টার</span>
                                </span>
                                <span class="text-[10px] opacity-75">POS</span>
                            </a>

                            @if($isOwner)
                                <a href="{{ route('dashboard') }}" class="flex items-center justify-between px-2.5 py-1.5 rounded-lg {{ request()->routeIs('dashboard') ? 'bg-[var(--fc-primary)] text-[var(--fc-primary-text)] font-black' : 'text-sky-300 hover:bg-[var(--fc-sidebar)]/80 font-bold' }} transition-colors">
                                    <span class="flex items-center gap-2">
                                        <span>👑</span>
                                        <span>ওনার ড্যাশবোর্ড</span>
                                    </span>
                                    <span class="text-[10px] opacity-75">Full</span>
                                </a>
                            @endif
                        </div>
                    </div>

                    <!-- 1. মূল কন্ট্রোল (Core Navigation) -->
                    <div>
                        <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-[var(--fc-sidebar-muted)] mb-1">কন্ট্রোল প্যানেল</p>
                        <div class="space-y-1">
                            @if($isOwner)
                                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg font-medium transition-colors {{ request()->routeIs('dashboard') ? 'bg-[var(--fc-primary)] text-[var(--fc-primary-text)] font-semibold shadow-sm' : 'text-[var(--fc-sidebar-text)]/90 hover:bg-[var(--fc-sidebar-hover)]' }}">
                                    <flux:icon name="home" class="size-5 shrink-0" />
                                    <span>ওনার ড্যাশবোর্ড</span>
                                </a>
                            @endif

                            <a href="{{ route('cartboy.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg font-medium transition-colors {{ request()->routeIs('cartboy.*') ? 'bg-amber-500 text-slate-950 font-bold shadow-sm' : 'text-amber-300 hover:bg-[var(--fc-sidebar-hover)]' }}">
                                <flux:icon name="shopping-bag" class="size-5 shrink-0 text-amber-400" />
                                <span class="flex-1">কার্টবয় কাউন্টার (POS)</span>
                            </a>

                            <a href="{{ route('orders.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg font-medium transition-colors {{ request()->routeIs('orders.*') ? 'bg-[var(--fc-primary)] text-[var(--fc-primary-text)] font-semibold' : 'text-[var(--fc-sidebar-text)]/90 hover:bg-[var(--fc-sidebar-hover)]' }}">
                                <flux:icon name="clipboard-document-list" class="size-5 shrink-0" />
                                <span>অর্ডার লিস্ট</span>
                            </a>
                        </div>
                    </div>

                    <!-- 2. খাবার ও মেনু (Food & Prices) -->
                    <div>
                        <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-[var(--fc-sidebar-muted)] mb-1">খাবার ও মেনু</p>
                        <div class="space-y-1">
                            <a href="{{ route('foods.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg font-medium transition-colors {{ request()->routeIs('foods.*') ? 'bg-[var(--fc-primary)] text-[var(--fc-primary-text)] font-semibold' : 'text-[var(--fc-sidebar-text)]/90 hover:bg-[var(--fc-sidebar-hover)]' }}">
                                <flux:icon name="cake" class="size-5 shrink-0 text-amber-400" />
                                <span class="flex-1">খাবার ও দাম পরিবর্তন</span>
                                <span class="text-[10px] uppercase tracking-wider px-1.5 py-0.5 rounded bg-amber-500/20 text-amber-300 font-bold">দাম</span>
                            </a>

                            <a href="{{ route('inventory.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg font-medium transition-colors {{ request()->routeIs('inventory.*') ? 'bg-[var(--fc-primary)] text-[var(--fc-primary-text)] font-semibold' : 'text-[var(--fc-sidebar-text)]/90 hover:bg-[var(--fc-sidebar-hover)]' }}">
                                <flux:icon name="archive-box" class="size-5 shrink-0" />
                                <span>খাবারের স্টক (Stock)</span>
                            </a>
                        </div>
                    </div>

                    <!-- 3. হিসাব ও খরচ (Money, Expenses, Waste) -->
                    @if($isOwner)
                        <div>
                            <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-[var(--fc-sidebar-muted)] mb-1">হিসাব ও খরচ</p>
                            <div class="space-y-1">
                                <a href="{{ route('wastes.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg font-medium transition-colors {{ request()->routeIs('wastes.*') ? 'bg-[var(--fc-primary)] text-[var(--fc-primary-text)] font-semibold' : 'text-red-400 hover:bg-[var(--fc-sidebar-hover)]' }}">
                                    <flux:icon name="trash" class="size-5 shrink-0 text-red-400" />
                                    <span>নষ্ট খাবার (Waste)</span>
                                </a>

                                <a href="{{ route('expenses.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg font-medium transition-colors {{ request()->routeIs('expenses.*') ? 'bg-[var(--fc-primary)] text-[var(--fc-primary-text)] font-semibold' : 'text-amber-400 hover:bg-[var(--fc-sidebar-hover)]' }}">
                                    <flux:icon name="credit-card" class="size-5 shrink-0 text-amber-400" />
                                    <span>দৈনিক খরচ (Expenses)</span>
                                </a>

                                <a href="{{ route('closing.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg font-medium transition-colors {{ request()->routeIs('closing.*') ? 'bg-sky-500 text-slate-950 font-bold shadow-sm' : 'text-sky-400 hover:bg-[var(--fc-sidebar-hover)]' }}">
                                    <flux:icon name="lock-closed" class="size-5 shrink-0 text-sky-400" />
                                    <span class="flex-1">দিনের হিসাব ক্লোজ (EOD)</span>
                                </a>

                                <a href="{{ route('profit-loss.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg font-medium transition-colors {{ request()->routeIs('profit-loss.*') ? 'bg-[var(--fc-primary)] text-[var(--fc-primary-text)] font-semibold' : 'text-emerald-400 hover:bg-[var(--fc-sidebar-hover)]' }}">
                                    <flux:icon name="chart-bar-square" class="size-5 shrink-0 text-emerald-400" />
                                    <span>লাভ-ক্ষতির হিসাব</span>
                                </a>
                            </div>
                        </div>

                        <!-- 4. অন্যান্য ও সেটিংস (Settings & Customers) -->
                        <div>
                            <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-[var(--fc-sidebar-muted)] mb-1">অন্যান্য</p>
                            <div class="space-y-1">
                                <a href="{{ route('customers.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg font-medium transition-colors {{ request()->routeIs('customers.*') ? 'bg-[var(--fc-primary)] text-[var(--fc-primary-text)] font-semibold' : 'text-[var(--fc-sidebar-text)]/90 hover:bg-[var(--fc-sidebar-hover)]' }}">
                                    <flux:icon name="user-group" class="size-5 shrink-0" />
                                    <span>কাস্টমার লিস্ট</span>
                                </a>

                                <a href="{{ route('settings.cart') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg font-medium transition-colors {{ request()->routeIs('settings.*') ? 'bg-[var(--fc-primary)] text-[var(--fc-primary-text)] font-semibold' : 'text-[var(--fc-sidebar-text)]/90 hover:bg-[var(--fc-sidebar-hover)]' }}">
                                    <flux:icon name="cog-6-tooth" class="size-5 shrink-0" />
                                    <span>দোকান সেটিংস ও থিম</span>
                                </a>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- User Profile & Footer in Sidebar -->
                <div class="p-3 border-t border-[var(--fc-border)]/20 bg-[var(--fc-sidebar-hover)]/40">
                    <div class="flex items-center gap-3 px-2 py-2">
                        <div class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-[var(--fc-primary)] text-[var(--fc-primary-text)] font-bold text-sm">
                            {{ $user->initials() }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold truncate text-[var(--fc-sidebar-text)]">{{ $user->name }}</p>
                            <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-bold uppercase tracking-wider {{ $isOwner ? 'bg-emerald-500/20 text-emerald-300' : 'bg-blue-500/20 text-blue-300' }}">
                                {{ $user->role }}
                            </span>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" title="Log out" class="p-1.5 rounded-lg text-[var(--fc-sidebar-muted)] hover:text-red-400 hover:bg-red-500/10 transition-colors">
                                <flux:icon name="arrow-right-start-on-rectangle" class="size-5" />
                            </button>
                        </form>
                    </div>
                </div>
            </aside>

            <!-- Main Content Wrapper -->
            <div
                :class="deviceView === 'mobile' ? 'w-full sm:max-w-md min-h-screen sm:min-h-[94vh] sm:rounded-[36px] bg-[var(--fc-bg)] text-[var(--fc-text)] sm:border sm:border-[var(--fc-border)] shadow-2xl relative flex flex-col overflow-hidden pb-16 my-0 sm:my-3' : 'flex flex-1 flex-col min-w-0 pb-16 lg:pb-0 bg-[var(--fc-bg)] text-[var(--fc-text)]'"
            >
                <!-- Top Navbar: Adapts to Mobile View and Desktop View -->
                <header class="sticky top-0 z-30 flex items-center justify-between border-b border-[var(--fc-border)] bg-[var(--fc-surface)]/95 backdrop-blur px-3 sm:px-4 py-2.5 transition-colors duration-200">
                    <div class="flex items-center gap-2">
                        <!-- Mobile Drawer Toggle -->
                        <button type="button" onclick="document.getElementById('mobile-drawer').classList.toggle('hidden')" class="p-1.5 rounded-xl text-[var(--fc-text-muted)] hover:bg-[var(--fc-card)] border border-[var(--fc-border)]" title="মেনু">
                            <flux:icon name="bars-3" class="size-5" />
                        </button>
                        <div class="flex items-center gap-1.5">
                            <span class="text-xs font-black text-[var(--fc-text)] truncate max-w-[130px] sm:max-w-none">
                                {{ request()->routeIs('cartboy.*') ? '🧑‍🍳 কার্টবয়' : '👑 ওনার প্যানেল' }}
                            </span>
                        </div>
                    </div>

                    <!-- Right Header Controls: Role Switcher & Device Switcher -->
                    <div class="flex items-center gap-1.5 sm:gap-2">
                        <!-- 3-Role Fast Switcher -->
                        <div class="flex items-center gap-1 bg-slate-900/90 p-0.5 rounded-xl border border-slate-800 text-[10px] font-bold">
                            <a href="{{ route('home') }}" class="px-2 py-1 rounded-lg text-emerald-400 hover:bg-slate-800 transition-colors" title="কাস্টমার মেনু">
                                👤 কাস্টমার
                            </a>
                            <a href="{{ route('cartboy.index', ['view' => 'mobile']) }}" class="px-2 py-1 rounded-lg {{ request()->routeIs('cartboy.*') ? 'bg-amber-500 text-slate-950 font-black' : 'text-amber-300 hover:bg-slate-800' }} transition-colors" title="কার্টবয় কাউন্টার">
                                🧑‍🍳 কার্টবয়
                            </a>
                            @if($isOwner)
                                <a href="{{ route('dashboard', ['view' => 'mobile']) }}" class="px-2 py-1 rounded-lg {{ request()->routeIs('dashboard') ? 'bg-sky-500 text-slate-950 font-black' : 'text-sky-300 hover:bg-slate-800' }} transition-colors" title="ওনার ড্যাশবোর্ড">
                                    👑 ওনার
                                </a>
                            @endif
                        </div>

                        <!-- Device Mode Switcher (Mobile Phone vs Desktop Wide) -->
                        <div class="hidden sm:flex items-center gap-0.5 bg-slate-900 p-0.5 rounded-xl border border-slate-800 text-[10px] font-bold">
                            <button
                                type="button"
                                @click="setDeviceView('mobile')"
                                :class="deviceView === 'mobile' ? 'bg-[#D70F64] text-white font-black shadow-xs' : 'text-slate-400 hover:text-white'"
                                class="px-2 py-1 rounded-lg transition-all"
                                title="মোবাইল ফোন ভিউ (Mobile View)"
                            >
                                📱
                            </button>
                            <button
                                type="button"
                                @click="setDeviceView('desktop')"
                                :class="deviceView === 'desktop' ? 'bg-emerald-500 text-slate-950 font-black shadow-xs' : 'text-slate-400 hover:text-white'"
                                class="px-2 py-1 rounded-lg transition-all"
                                title="ডেক্সটপ ফুল ভিউ (Desktop View)"
                            >
                                💻
                            </button>
                        </div>
                    </div>
                </header>

                <!-- Flash Notifications (Success / Error alerts) -->
                @if(session('success'))
                    <div class="m-3 p-3 rounded-xl border border-emerald-500/30 bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 text-xs flex items-center justify-between shadow-xs">
                        <div class="flex items-center gap-2">
                            <flux:icon name="check-circle" class="size-4 text-emerald-500 shrink-0" />
                            <span>{{ session('success') }}</span>
                        </div>
                        <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 font-bold">&times;</button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="m-3 p-3 rounded-xl border border-red-500/30 bg-red-500/10 text-red-700 dark:text-red-300 text-xs flex items-center justify-between shadow-xs">
                        <div class="flex items-center gap-2">
                            <flux:icon name="exclamation-circle" class="size-4 text-red-500 shrink-0" />
                            <span>{{ session('error') }}</span>
                        </div>
                        <button type="button" onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700 font-bold">&times;</button>
                    </div>
                @endif

                <!-- Main Slot -->
                <main :class="deviceView === 'mobile' ? 'p-3 sm:p-4 overflow-y-auto flex-1' : 'flex-1 p-4 sm:p-6 lg:p-8'">
                    {{ $slot }}
                </main>
            </div>
        </div>

        <!-- Mobile Drawer Navigation (Modal style for Owner Phone) -->
        <div id="mobile-drawer" class="hidden fixed inset-0 z-50 lg:hidden" aria-modal="true">
            <div class="fixed inset-0 bg-black/70 backdrop-blur-xs" onclick="document.getElementById('mobile-drawer').classList.add('hidden')"></div>
            <div class="fixed inset-y-0 left-0 w-4/5 max-w-xs bg-[var(--fc-sidebar)] text-[var(--fc-sidebar-text)] p-4 flex flex-col justify-between shadow-2xl z-10 overflow-y-auto">
                <div class="space-y-4">
                    <div class="flex items-center justify-between border-b border-[var(--fc-border)]/20 pb-3">
                        <x-app-logo />
                        <button type="button" onclick="document.getElementById('mobile-drawer').classList.add('hidden')" class="p-1 rounded-lg text-[var(--fc-sidebar-muted)] hover:text-white text-xl font-bold">
                            &times;
                        </button>
                    </div>

                    <nav class="space-y-1.5 text-xs font-semibold">
                        @if($isOwner)
                            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-[var(--fc-sidebar-hover)] {{ request()->routeIs('dashboard') ? 'bg-[var(--fc-primary)] text-[var(--fc-primary-text)] font-bold' : '' }}">
                                <flux:icon name="home" class="size-5" />
                                <span>🏠 হোম / আজকের হিসাব</span>
                            </a>
                        @endif

                        <a href="{{ route('cartboy.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-amber-300 hover:bg-[var(--fc-sidebar-hover)] {{ request()->routeIs('cartboy.*') ? 'bg-amber-500/20 font-bold' : '' }}">
                            <flux:icon name="shopping-bag" class="size-5" />
                            <span>⚡ কার্টবয় কাউন্টার (POS)</span>
                        </a>

                        <a href="{{ route('orders.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-[var(--fc-sidebar-hover)] {{ request()->routeIs('orders.*') ? 'bg-[var(--fc-primary)] text-[var(--fc-primary-text)] font-bold' : '' }}">
                            <flux:icon name="clipboard-document-list" class="size-5" />
                            <span>📋 অর্ডার লিস্ট</span>
                        </a>

                        <a href="{{ route('foods.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-[var(--fc-sidebar-hover)] {{ request()->routeIs('foods.*') ? 'bg-[var(--fc-primary)] text-[var(--fc-primary-text)] font-bold' : '' }}">
                            <flux:icon name="cake" class="size-5" />
                            <span>💰 খাবার ও দাম পরিবর্তন</span>
                        </a>

                        <a href="{{ route('wastes.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-red-400 hover:bg-[var(--fc-sidebar-hover)] {{ request()->routeIs('wastes.*') ? 'bg-red-500/20 font-bold' : '' }}">
                            <flux:icon name="trash" class="size-5" />
                            <span>🗑️ নষ্ট খাবার / অপচয় (Waste)</span>
                        </a>

                        @if($isOwner)
                            <a href="{{ route('expenses.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-amber-400 hover:bg-[var(--fc-sidebar-hover)] {{ request()->routeIs('expenses.*') ? 'bg-amber-500/20 font-bold' : '' }}">
                                <flux:icon name="credit-card" class="size-5" />
                                <span>💸 আজকের খরচ (Expenses)</span>
                            </a>

                            <a href="{{ route('closing.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sky-400 hover:bg-[var(--fc-sidebar-hover)] {{ request()->routeIs('closing.*') ? 'bg-sky-500/20 font-bold' : '' }}">
                                <flux:icon name="lock-closed" class="size-5" />
                                <span>🔒 দিনের হিসাব ক্লোজ (EOD)</span>
                            </a>

                            <a href="{{ route('inventory.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-[var(--fc-sidebar-hover)] {{ request()->routeIs('inventory.*') ? 'bg-[var(--fc-primary)] text-[var(--fc-primary-text)] font-bold' : '' }}">
                                <flux:icon name="archive-box" class="size-5" />
                                <span>📦 ইনভেন্টরি ও স্টক</span>
                            </a>

                            <a href="{{ route('customers.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-[var(--fc-sidebar-hover)] {{ request()->routeIs('customers.*') ? 'bg-[var(--fc-primary)] text-[var(--fc-primary-text)] font-bold' : '' }}">
                                <flux:icon name="user-group" class="size-5" />
                                <span>👥 কাস্টমার তালিকা</span>
                            </a>

                            <a href="{{ route('settings.cart') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-[var(--fc-sidebar-hover)] {{ request()->routeIs('settings.*') ? 'bg-[var(--fc-primary)] text-[var(--fc-primary-text)] font-bold' : '' }}">
                                <flux:icon name="cog-6-tooth" class="size-5" />
                                <span>⚙️ কার্ট সেটিংস ও থিম</span>
                            </a>
                        @endif

                        <a href="{{ route('home') }}" target="_blank" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-emerald-400 hover:bg-[var(--fc-sidebar-hover)] border border-emerald-500/30">
                            <flux:icon name="globe-alt" class="size-5" />
                            <span>🌐 কাস্টমার ডিজিটাল মেনু</span>
                        </a>
                    </nav>
                </div>

                <div class="pt-4 border-t border-[var(--fc-border)]/20">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center justify-center gap-2 py-2.5 px-3 rounded-xl bg-red-500/20 text-red-300 font-bold text-xs">
                            <flux:icon name="arrow-right-start-on-rectangle" class="size-4" />
                            <span>লগআউট করুন (Log out)</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Mobile Bottom Navigation Bar (Very important for fast food cart phone use) -->
        <nav class="lg:hidden fixed bottom-0 inset-x-0 z-40 bg-[var(--fc-surface)] border-t border-[var(--fc-border)] flex items-center justify-around h-16 px-2 shadow-lg backdrop-blur">
            @if($isOwner)
                <a href="{{ route('dashboard') }}" class="flex flex-col items-center gap-1 py-1 px-2 text-[10px] font-bold {{ request()->routeIs('dashboard') ? 'text-[var(--fc-primary)] font-black' : 'text-[var(--fc-text-muted)]' }}">
                    <flux:icon name="home" class="size-5" />
                    <span>হোম</span>
                </a>

                <a href="{{ route('cartboy.index') }}" class="flex flex-col items-center gap-1 py-1 px-3 rounded-2xl bg-amber-500 text-slate-950 font-black text-[10px] shadow-md -mt-3 active:scale-95 transition-transform">
                    <flux:icon name="shopping-bag" class="size-5 text-slate-950" />
                    <span>কার্টবয়</span>
                </a>

                <a href="{{ route('orders.index') }}" class="flex flex-col items-center gap-1 py-1 px-2 text-[10px] font-bold {{ request()->routeIs('orders.*') ? 'text-[var(--fc-primary)] font-black' : 'text-[var(--fc-text-muted)]' }}">
                    <flux:icon name="clipboard-document-list" class="size-5" />
                    <span>অর্ডার</span>
                </a>

                <a href="{{ route('foods.index') }}" class="flex flex-col items-center gap-1 py-1 px-2 text-[10px] font-bold {{ request()->routeIs('foods.*') ? 'text-[var(--fc-primary)] font-black' : 'text-[var(--fc-text-muted)]' }}">
                    <flux:icon name="currency-bangladeshi" class="size-5" />
                    <span>দাম ও মেনু</span>
                </a>

                <button type="button" onclick="document.getElementById('mobile-drawer').classList.remove('hidden')" class="flex flex-col items-center gap-1 py-1 px-2 text-[10px] font-bold text-[var(--fc-text-muted)] hover:text-[var(--fc-text)]">
                    <flux:icon name="bars-3" class="size-5" />
                    <span>আরও</span>
                </button>
            @else
                <a href="{{ route('cartboy.index') }}" class="flex flex-col items-center gap-1 py-1 px-4 rounded-2xl bg-[var(--fc-primary)] text-[var(--fc-primary-text)] font-black text-[10px] shadow-md -mt-3">
                    <flux:icon name="shopping-bag" class="size-5 text-white" />
                    <span>কাউন্টার POS</span>
                </a>

                <a href="{{ route('home') }}" target="_blank" class="flex flex-col items-center gap-1 py-1 px-2 text-[10px] font-bold text-emerald-400">
                    <flux:icon name="globe-alt" class="size-5" />
                    <span>কাস্টমার মেনু</span>
                </a>

                <form method="POST" action="{{ route('logout') }}" class="flex flex-col items-center">
                    @csrf
                    <button type="submit" class="flex flex-col items-center gap-1 py-1 px-2 text-[10px] font-bold text-red-400">
                        <flux:icon name="arrow-right-start-on-rectangle" class="size-5" />
                        <span>লগআউট</span>
                    </button>
                </form>
            @endif
        </nav>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
