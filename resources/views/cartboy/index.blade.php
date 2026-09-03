<x-layouts::app title="কার্টবয় ও কাউন্টার প্যানেল">
    <div x-data="cartBoyWorkspace()" x-init="init()" class="space-y-4 pb-20 sm:pb-4">

        <!-- Top Cart Status & Operations Header -->
        <!-- Top Cart Status & Operations Header -->
        <div class="p-3 sm:p-4 rounded-2xl bg-[var(--fc-card)] border border-[var(--fc-border)] flex flex-col md:flex-row md:items-center md:justify-between gap-3 shadow-xs">
            <div class="flex items-center gap-3">
                <div class="size-10 rounded-2xl {{ $isCartOpen ? 'bg-emerald-500/15 text-emerald-500 border border-emerald-500/30' : 'bg-red-500/15 text-red-500 border border-red-500/30' }} flex items-center justify-center text-lg font-black shrink-0">
                    {{ $isCartOpen ? '🟢' : '🔴' }}
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-sm font-black text-[var(--fc-text)]">
                            {{ $isCartOpen ? 'কার্ট খোলা আছে (Taking Orders)' : 'কার্ট সাময়িক বন্ধ (Cart Closed)' }}
                        </h1>
                        <span class="text-[10px] px-2 py-0.5 rounded-full font-black {{ $isCartOpen ? 'bg-emerald-500/20 text-emerald-500' : 'bg-red-500/20 text-red-500' }}">
                            {{ $isCartOpen ? 'লাইভ চালু' : 'অর্ডার স্থগিত' }}
                        </span>
                    </div>
                    <p class="text-[11px] text-[var(--fc-text-muted)] mt-0.5">
                        {{ $isOwner ? '👑 ওনার মোডে আছেন' : '🧑‍🍳 কার্টবয় হিসেবে কর্মরত' }} • আজকের তারিখ: {{ now()->translatedFormat('d M Y, h:i A') }}
                    </p>
                </div>
            </div>

            <!-- Top Actions: 2-Column Grid on Mobile, Flex on Desktop -->
            <div :class="deviceView === 'mobile' ? 'grid grid-cols-2 gap-1.5 w-full pt-1' : 'flex items-center gap-2 flex-wrap'">
                <form action="{{ route('cart.toggle-status') }}" method="POST" class="w-full">
                    @csrf
                    <button
                        type="submit"
                        class="w-full px-3 py-2 rounded-xl text-xs font-black transition-all flex items-center justify-center gap-1.5 shadow-xs {{ $isCartOpen ? 'bg-red-500/15 hover:bg-red-500/25 text-red-600 dark:text-red-400 border border-red-500/30' : 'bg-emerald-500 hover:bg-emerald-400 text-slate-950 shadow-md' }}"
                    >
                        <span>{{ $isCartOpen ? '🔴 কার্ট বন্ধ করুন' : '🟢 কার্ট এখনই খুলুন' }}</span>
                    </button>
                </form>

                <button
                    type="button"
                    @click="toggleAudioAlert()"
                    :class="audioEnabled ? 'bg-emerald-500/20 text-emerald-400 border-emerald-500/40' : 'bg-slate-800 text-slate-400 border-slate-700'"
                    class="w-full px-3 py-2 rounded-xl border font-bold text-xs flex items-center justify-center gap-1.5 transition-all shadow-xs"
                    title="নতুন অর্ডার আসলে অ্যালার্ট সাউন্ড বাজার জন্য"
                >
                    <span x-text="audioEnabled ? '🔊 অ্যালার্ট চালু' : '🔇 অ্যালার্ট বন্ধ'"></span>
                </button>

                <button
                    type="button"
                    @click="qrModalOpen = true"
                    class="w-full px-3 py-2 rounded-xl bg-purple-500/15 hover:bg-purple-500/25 border border-purple-500/30 text-purple-400 font-bold text-xs flex items-center justify-center gap-1"
                >
                    📱 কাউন্টার QR
                </button>

                <a href="{{ route('home') }}" target="_blank" class="w-full px-3 py-2 rounded-xl bg-[var(--fc-bg)] hover:bg-[var(--fc-card)] border border-[var(--fc-border)] text-[var(--fc-text)] font-bold text-xs flex items-center justify-center gap-1">
                    🌐 কাস্টমার মেনু &nearr;
                </a>

                @if($isOwner)
                    <a href="{{ route('dashboard') }}" class="w-full px-3 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs flex items-center justify-center gap-1">
                        👑 ওনার ড্যাশবোর্ড &rarr;
                    </a>
                @endif
            </div>
        </div>

        <!-- Date Switcher / Automatic Daily History Selector -->
        <div class="p-3 sm:p-4 rounded-2xl bg-[var(--fc-card)] border border-[var(--fc-border)] flex flex-col gap-2.5 shadow-2xs">
            <div class="flex items-center gap-2">
                <div class="size-8 rounded-xl bg-[var(--fc-bg)] border border-[var(--fc-border)] flex items-center justify-center text-sm shrink-0">
                    📅
                </div>
                <div>
                    <h3 class="text-xs font-black text-[var(--fc-text)]">
                        {{ ($isToday ?? true) ? 'আজকের লাইভ রেকর্ড (' . now()->translatedFormat('d M Y') . ')' : Carbon\Carbon::parse($selectedDate ?? now())->translatedFormat('d F Y, l') . ' এর রেকর্ড' }}
                    </h3>
                    <p class="text-[10px] text-[var(--fc-text-muted)]">
                        {{ ($isToday ?? true) ? 'প্রতি মুহূর্তের সেলস লাইভ আপডেট হচ্ছে' : 'অতীতের দিনের সম্পূর্ণ সংরক্ষিত বিক্রয় ও শিফট হিসাব' }}
                    </p>
                </div>
            </div>

            <form method="GET" action="{{ route('cartboy.index') }}" class="space-y-2 w-full">
                <div class="grid grid-cols-2 gap-1.5 w-full">
                    <a
                        href="{{ route('cartboy.index') }}"
                        class="px-3 py-2 rounded-xl text-xs font-bold text-center transition-all {{ ($isToday ?? true) ? 'bg-[var(--fc-primary)] text-[var(--fc-primary-text)] font-black shadow-xs' : 'bg-[var(--fc-bg)] text-[var(--fc-text-muted)] border border-[var(--fc-border)] hover:text-[var(--fc-text)]' }}"
                    >
                        আজকে (Today)
                    </a>
                    <a
                        href="{{ route('cartboy.index', ['date' => now()->subDay()->toDateString()]) }}"
                        class="px-3 py-2 rounded-xl text-xs font-bold text-center transition-all {{ (isset($selectedDate) && $selectedDate->isYesterday()) ? 'bg-[var(--fc-primary)] text-[var(--fc-primary-text)] font-black shadow-xs' : 'bg-[var(--fc-bg)] text-[var(--fc-text-muted)] border border-[var(--fc-border)] hover:text-[var(--fc-text)]' }}"
                    >
                        গতকাল (Yesterday)
                    </a>
                </div>
                <div class="flex items-center gap-1.5 w-full">
                    <input
                        type="date"
                        name="date"
                        value="{{ isset($selectedDate) ? $selectedDate->toDateString() : now()->toDateString() }}"
                        max="{{ now()->toDateString() }}"
                        class="flex-1 px-3 py-2 rounded-xl bg-[var(--fc-bg)] border border-[var(--fc-border)] text-xs text-[var(--fc-text)] font-bold outline-none"
                    />
                    <button
                        type="submit"
                        class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold transition-colors shadow-xs shrink-0"
                    >
                        হিস্ট্রি দেখুন
                    </button>
                </div>
            </form>
        </div>

        <!-- Real-Time Metrics Overview Cards (Responsive 2-Column Grid on Mobile, 6-Col on Wide Desktop) -->
        <div :class="deviceView === 'mobile' ? 'grid grid-cols-2 gap-2' : 'grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2.5'">
            <!-- 1. Total Completed Sales -->
            <div class="p-3 rounded-2xl bg-[var(--fc-card)] border border-[var(--fc-border)] space-y-1 overflow-hidden">
                <span class="text-[10px] font-bold text-[var(--fc-text-muted)] uppercase tracking-wider block truncate">মোট বিক্রি</span>
                <div class="text-sm sm:text-base font-black text-emerald-600 dark:text-emerald-400 truncate">
                    ৳<span x-text="Number(summaryData.completed_sales).toLocaleString()">{{ number_format($summary['completed_sales'], 0) }}</span>
                </div>
                <span class="text-[10px] text-[var(--fc-text-muted)] block truncate"><span x-text="summaryData.total_orders">{{ $summary['total_orders'] }}</span>টি সম্পন্ন</span>
            </div>

            <!-- 2. Cash in Hand -->
            <div class="p-3 rounded-2xl bg-[var(--fc-card)] border border-[var(--fc-border)] space-y-1 overflow-hidden">
                <span class="text-[10px] font-bold text-[var(--fc-text-muted)] uppercase tracking-wider block truncate">ড্রয়ারে ক্যাশ</span>
                <div class="text-sm sm:text-base font-black text-emerald-500 truncate">
                    ৳<span x-text="Number(summaryData.cash).toLocaleString()">{{ number_format($summary['payment_breakdown']['cash'], 0) }}</span>
                </div>
                <span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-semibold block truncate">নগদ টাকা</span>
            </div>

            <!-- 3. Digital (bKash/Nagad) -->
            <div class="p-3 rounded-2xl bg-[var(--fc-card)] border border-[var(--fc-border)] space-y-1 overflow-hidden">
                <span class="text-[10px] font-bold text-[var(--fc-text-muted)] uppercase tracking-wider block truncate">বিকাশ / ডিজিটাল</span>
                <div class="text-sm sm:text-base font-black text-pink-500 truncate">
                    ৳<span x-text="Number(summaryData.digital).toLocaleString()">{{ number_format($summary['payment_breakdown']['bkash'] + $summary['payment_breakdown']['nagad'] + $summary['payment_breakdown']['rocket'], 0) }}</span>
                </div>
                <span class="text-[10px] text-[var(--fc-text-muted)] block truncate">মোবাইল ওয়ালেট</span>
            </div>

            <!-- 4. Parcel Orders -->
            <div class="p-3 rounded-2xl bg-[var(--fc-card)] border border-[var(--fc-border)] space-y-1 overflow-hidden">
                <span class="text-[10px] font-bold text-[var(--fc-text-muted)] uppercase tracking-wider block truncate">পার্সেল / টেকওয়ে</span>
                <div class="text-sm sm:text-base font-black text-blue-500 truncate">
                    <span x-text="summaryData.parcel_orders">{{ $summary['parcel_orders'] }}</span>টি
                </div>
                <span class="text-[10px] text-blue-400 font-semibold block truncate">৳<span x-text="Number(summaryData.parcel_sales).toLocaleString()">{{ number_format($summary['parcel_sales'], 0) }}</span></span>
            </div>

            <!-- 5. Waste Loss -->
            <div class="p-3 rounded-2xl bg-[var(--fc-card)] border border-[var(--fc-border)] space-y-1 overflow-hidden">
                <span class="text-[10px] font-bold text-[var(--fc-text-muted)] uppercase tracking-wider block truncate">নষ্ট / অপচয় ক্ষতি</span>
                <div class="text-sm sm:text-base font-black text-red-500 truncate">
                    ৳<span x-text="Number(summaryData.total_waste).toLocaleString()">{{ number_format($summary['total_waste'], 0) }}</span>
                </div>
                <span class="text-[10px] text-red-400 block truncate">{{ $todayWastes->count() }} বার নষ্ট</span>
            </div>

            <!-- 6. Net Profit -->
            <div class="p-3 rounded-2xl bg-gradient-to-br from-emerald-500/10 to-transparent border border-emerald-500/30 space-y-1 overflow-hidden">
                <span class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider block truncate">আজকের নীট লাভ</span>
                <div class="text-sm sm:text-base font-black text-emerald-500 truncate">
                    ৳<span x-text="Number(summaryData.net_profit).toLocaleString()">{{ number_format($summary['net_profit'], 0) }}</span>
                </div>
                <span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-bold block truncate">মার্জিন {{ $summary['profit_margin'] }}%</span>
            </div>
        </div>

        <!-- Floating Live Audio Alert Notice (Appears when new order arrives) -->
        <div
            x-show="newOrderAlert.show"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 scale-95"
            class="fixed bottom-4 right-4 sm:bottom-6 sm:right-6 z-50 max-w-sm w-full bg-gradient-to-br from-amber-500 via-orange-500 to-red-500 text-slate-950 p-4 rounded-3xl shadow-2xl border-2 border-white/50"
            x-cloak
        >
            <div class="flex items-start justify-between gap-2 pb-2 border-b border-black/15">
                <div class="flex items-center gap-2">
                    <span class="text-2xl animate-bounce">🔔</span>
                    <div>
                        <h3 class="font-black text-sm tracking-tight text-white drop-shadow-xs">নতুন কাস্টমার অর্ডার এসেছে!</h3>
                        <p class="text-[11px] font-black text-black/80" x-text="newOrderAlert.orderNumber"></p>
                    </div>
                </div>
                <button @click="newOrderAlert.show = false" class="size-6 rounded-full bg-black/20 text-black hover:bg-black/40 font-bold flex items-center justify-center text-xs">&times;</button>
            </div>

            <div class="py-2.5 space-y-1.5 text-xs text-white drop-shadow-2xs">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-black/80">কাস্টমার:</span>
                    <span class="font-black text-white" x-text="newOrderAlert.customerName"></span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="font-bold text-black/80">অর্ডারের ধরন:</span>
                    <span class="px-2 py-0.5 rounded-md bg-black/30 text-yellow-200 font-black text-[11px]" x-text="newOrderAlert.orderTypeBadge"></span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="font-bold text-black/80">মোট বিল:</span>
                    <span class="font-black text-sm text-yellow-300" x-text="'৳' + newOrderAlert.totalAmount"></span>
                </div>
                <div x-show="newOrderAlert.itemsSummary" class="pt-1 text-[11px] text-white/95 font-semibold bg-black/20 p-2 rounded-xl border border-white/20 truncate" x-text="newOrderAlert.itemsSummary"></div>
            </div>

            <div class="pt-2 flex gap-2">
                <button
                    type="button"
                    @click="newOrderAlert.show = false; activeTab = 'kitchen'"
                    class="flex-1 py-2.5 rounded-xl bg-slate-950 hover:bg-black text-amber-300 font-black text-xs text-center shadow-md flex items-center justify-center gap-1 transition-all"
                >
                    🧑‍🍳 কিচেনে দেখুন
                </button>
                <button
                    type="button"
                    @click="acknowledgeAndCook(newOrderAlert.orderId)"
                    class="px-3.5 py-2.5 rounded-xl bg-white hover:bg-slate-100 text-slate-950 font-black text-xs shadow-md transition-all active:scale-95"
                >
                    🍳 রান্না শুরু
                </button>
            </div>
        </div>

        <!-- Toast notification -->
        <div
            x-show="toast.show"
            x-transition
            class="p-3 rounded-xl bg-emerald-500/15 border border-emerald-500/40 text-emerald-700 dark:text-emerald-300 text-xs font-bold flex items-center justify-between shadow-sm"
            x-cloak
        >
            <span x-text="toast.message"></span>
            <button type="button" @click="toast.show = false" class="text-base font-bold">&times;</button>
        </div>

        <!-- Workspace Tabs for Cart Boy & Operations -->
        <div class="flex items-center gap-1.5 border-b border-[var(--fc-border)] pb-2 overflow-x-auto scrollbar-none">
            <!-- Tab 1: POS -->
            <button
                type="button"
                @click="activeTab = 'pos'"
                :class="activeTab === 'pos' ? 'bg-[var(--fc-primary)] text-[var(--fc-primary-text)] font-black shadow-sm' : 'bg-[var(--fc-card)] text-[var(--fc-text-muted)] hover:text-[var(--fc-text)] border border-[var(--fc-border)]'"
                class="px-3.5 py-2 rounded-2xl text-xs shrink-0 transition-all font-bold flex items-center gap-1"
            >
                <span>⚡ কাউন্টার POS</span>
            </button>

            <!-- Tab 2: Kitchen -->
            <button
                type="button"
                @click="activeTab = 'kitchen'"
                :class="activeTab === 'kitchen' ? 'bg-[var(--fc-primary)] text-[var(--fc-primary-text)] font-black shadow-sm' : 'bg-[var(--fc-card)] text-[var(--fc-text-muted)] hover:text-[var(--fc-text)] border border-[var(--fc-border)]'"
                class="px-3.5 py-2 rounded-2xl text-xs shrink-0 transition-all font-bold flex items-center gap-1 relative"
            >
                <span>🍳 কিচেন</span>
                <span class="size-4.5 rounded-full bg-amber-500 text-slate-950 font-black text-[10px] flex items-center justify-center" x-text="liveOrdersList.length"></span>
            </button>

            <!-- Tab 3: Sales Timeline & Item Breakdown -->
            <button
                type="button"
                @click="activeTab = 'timeline'"
                :class="activeTab === 'timeline' ? 'bg-[var(--fc-primary)] text-[var(--fc-primary-text)] font-black shadow-sm' : 'bg-[var(--fc-card)] text-[var(--fc-text-muted)] hover:text-[var(--fc-text)] border border-[var(--fc-border)]'"
                class="px-3.5 py-2 rounded-2xl text-xs shrink-0 transition-all font-bold flex items-center gap-1"
            >
                <span>📊 লাইভ সেলস ও টাইমলাইন</span>
                <span class="text-[10px] opacity-80">(৳{{ number_format($summary['completed_sales'], 0) }})</span>
            </button>

            <!-- Tab 4: Parcel Orders -->
            <button
                type="button"
                @click="activeTab = 'parcel'"
                :class="activeTab === 'parcel' ? 'bg-[var(--fc-primary)] text-[var(--fc-primary-text)] font-black shadow-sm' : 'bg-[var(--fc-card)] text-[var(--fc-text-muted)] hover:text-[var(--fc-text)] border border-[var(--fc-border)]'"
                class="px-3.5 py-2 rounded-2xl text-xs shrink-0 transition-all font-bold flex items-center gap-1"
            >
                <span>🛍️ পার্সেল কাস্টমার</span>
                <span class="size-4.5 rounded-full bg-blue-500 text-white font-black text-[10px] flex items-center justify-center">{{ $summary['parcel_orders'] }}</span>
            </button>

            <!-- Tab 5: Payment Breakdown -->
            <button
                type="button"
                @click="activeTab = 'payments'"
                :class="activeTab === 'payments' ? 'bg-[var(--fc-primary)] text-[var(--fc-primary-text)] font-black shadow-sm' : 'bg-[var(--fc-card)] text-[var(--fc-text-muted)] hover:text-[var(--fc-text)] border border-[var(--fc-border)]'"
                class="px-3.5 py-2 rounded-2xl text-xs shrink-0 transition-all font-bold flex items-center gap-1"
            >
                <span>💵 পেমেন্ট ব্রেকডাউন</span>
            </button>

            <!-- Tab 6: Waste Logging -->
            <button
                type="button"
                @click="activeTab = 'waste'"
                :class="activeTab === 'waste' ? 'bg-[var(--fc-primary)] text-[var(--fc-primary-text)] font-black shadow-sm' : 'bg-[var(--fc-card)] text-[var(--fc-text-muted)] hover:text-[var(--fc-text)] border border-[var(--fc-border)]'"
                class="px-3.5 py-2 rounded-2xl text-xs shrink-0 transition-all font-bold flex items-center gap-1"
            >
                <span>🗑️ ওয়েস্টেজ লগ</span>
                @if($summary['total_waste'] > 0)
                    <span class="text-[10px] text-red-400 font-black">৳{{ number_format($summary['total_waste'], 0) }}</span>
                @endif
            </button>

            <!-- Tab 7: Shift Close -->
            <button
                type="button"
                @click="activeTab = 'shift_close'"
                :class="activeTab === 'shift_close' ? 'bg-amber-500 text-slate-950 font-black shadow-sm' : 'bg-[var(--fc-card)] text-[var(--fc-text-muted)] hover:text-[var(--fc-text)] border border-[var(--fc-border)]'"
                class="px-3.5 py-2 rounded-2xl text-xs shrink-0 transition-all font-bold flex items-center gap-1"
            >
                <span>🔒 কার্ট শিফট ক্লোজ</span>
            </button>

            <!-- Tab 8: Today Receipts -->
            <button
                type="button"
                @click="activeTab = 'all_orders'"
                :class="activeTab === 'all_orders' ? 'bg-[var(--fc-primary)] text-[var(--fc-primary-text)] font-black shadow-sm' : 'bg-[var(--fc-card)] text-[var(--fc-text-muted)] hover:text-[var(--fc-text)] border border-[var(--fc-border)]'"
                class="px-3.5 py-2 rounded-2xl text-xs shrink-0 transition-all font-bold flex items-center gap-1"
            >
                <span>📋 আজকের সকল রসিদ</span>
                <span class="size-4.5 rounded-full bg-slate-700 text-white font-black text-[10px] flex items-center justify-center" x-text="allTodayOrdersList.length"></span>
            </button>

            <!-- Tab 9: Stock -->
            <button
                type="button"
                @click="activeTab = 'stock'"
                :class="activeTab === 'stock' ? 'bg-[var(--fc-primary)] text-[var(--fc-primary-text)] font-black shadow-sm' : 'bg-[var(--fc-card)] text-[var(--fc-text-muted)] hover:text-[var(--fc-text)] border border-[var(--fc-border)]'"
                class="px-3.5 py-2 rounded-2xl text-xs shrink-0 transition-all font-bold flex items-center gap-1"
            >
                <span>📦 স্টক</span>
            </button>
        </div>

        <!-- =================================================================== -->
        <!-- TAB 1: POS COUNTER ORDERING -->
        <!-- =================================================================== -->
        <div x-show="activeTab === 'pos'" class="space-y-4">
            <div :class="deviceView === 'mobile' ? 'space-y-3' : 'grid grid-cols-1 lg:grid-cols-12 gap-4 items-start'">

                <!-- Left: Food Catalog & Search (7 Cols on desktop, full width on mobile) -->
                <div :class="deviceView === 'mobile' ? 'space-y-3' : 'lg:col-span-7 space-y-3'">
                    <!-- Search & Mobile Cart Toggle Bar -->
                    <div class="flex items-center gap-2">
                        <div class="relative flex-1">
                            <input
                                type="text"
                                x-model="searchQuery"
                                placeholder="খাবার খুঁজুন (যেমন: বার্গার, চা, নুডুলস, হালিম)..."
                                class="w-full px-4 py-2.5 rounded-2xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs text-[var(--fc-text)] focus:border-[var(--fc-primary)] outline-none"
                            />
                        </div>

                        <!-- Mobile Cart Drawer Toggle -->
                        <button
                            type="button"
                            @click="mobileCartOpen = true"
                            :class="deviceView === 'mobile' ? 'flex' : 'lg:hidden flex'"
                            class="px-3.5 py-2.5 rounded-2xl bg-emerald-500 text-slate-950 text-xs font-black items-center gap-1.5 shadow-md active:scale-95"
                        >
                            <span>🛒 কার্ট</span>
                            <span x-text="'(৳' + cartTotalPrice() + ')'"></span>
                        </button>
                    </div>

                    <!-- Category Pills -->
                    <div class="flex items-center gap-1.5 overflow-x-auto pb-1 scrollbar-none">
                        <button
                            type="button"
                            @click="selectedCategory = 'all'"
                            :class="selectedCategory === 'all' ? 'bg-[var(--fc-primary)] text-[var(--fc-primary-text)] font-black' : 'bg-[var(--fc-card)] text-[var(--fc-text-muted)] border border-[var(--fc-border)]'"
                            class="px-3.5 py-1.5 rounded-full text-xs font-bold shrink-0"
                        >
                            সব খাবার
                        </button>
                        @foreach($categories as $cat)
                            <button
                                type="button"
                                @click="selectedCategory = {{ $cat->id }}"
                                :class="selectedCategory === {{ $cat->id }} ? 'bg-[var(--fc-primary)] text-[var(--fc-primary-text)] font-black' : 'bg-[var(--fc-card)] text-[var(--fc-text-muted)] border border-[var(--fc-border)]'"
                                class="px-3.5 py-1.5 rounded-full text-xs font-bold shrink-0"
                            >
                                {{ $cat->bengali_name ?? $cat->name }}
                            </button>
                        @endforeach
                    </div>

                    <!-- Food Cards Grid -->
                    <div :class="deviceView === 'mobile' ? 'grid grid-cols-2 gap-2' : 'grid grid-cols-2 sm:grid-cols-3 gap-2.5'">
                        <template x-for="food in filteredFoods" :key="food.id">
                            <div
                                @click="addToCart(food)"
                                class="fc-card p-2.5 rounded-2xl cursor-pointer hover:border-emerald-500/50 hover:shadow-md transition-all flex flex-col justify-between shadow-xs active:scale-95 group border border-[var(--fc-border)] bg-[var(--fc-card)]"
                            >
                                <div>
                                    <!-- Food Picture on Top: Clear Visual Preview & Stock -->
                                    <div class="relative w-full h-24 sm:h-28 rounded-xl overflow-hidden mb-2 bg-slate-900 border border-[var(--fc-border)]/50">
                                        <img
                                            :src="food.image_url"
                                            :alt="food.name"
                                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                            loading="lazy"
                                        />
                                        <!-- Stock Badge -->
                                        <span
                                            class="absolute bottom-1 right-1 text-[9px] font-black px-1.5 py-0.5 rounded-md backdrop-blur-md shadow-xs"
                                            :class="food.current_stock <= 5 ? 'bg-red-500/90 text-white' : 'bg-emerald-600/90 text-white'"
                                            x-text="food.current_stock + ' ' + food.unit"
                                        ></span>
                                    </div>

                                    <!-- Food Names: Bengali in Large Bold, English below, Fully Legible -->
                                    <div class="space-y-0.5">
                                        <h3
                                            class="font-black text-xs sm:text-sm text-[var(--fc-text)] group-hover:text-emerald-500 leading-snug line-clamp-2 min-h-[34px]"
                                            x-text="food.bengali_name || food.name"
                                            :title="food.bengali_name || food.name"
                                        ></h3>
                                        <p
                                            class="text-[10px] text-[var(--fc-text-muted)] truncate"
                                            x-text="food.name"
                                        ></p>
                                    </div>
                                </div>

                                <!-- Price and Add Button -->
                                <div class="mt-2 pt-2 border-t border-[var(--fc-border)]/60 flex items-center justify-between">
                                    <div>
                                        <span class="text-[9px] text-[var(--fc-text-muted)] block">মূল্য</span>
                                        <span class="font-black text-sm sm:text-base text-emerald-600 dark:text-emerald-400" x-text="'৳' + Number(food.selling_price).toFixed(0)"></span>
                                    </div>
                                    <button
                                        type="button"
                                        class="px-2.5 py-1.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black text-xs flex items-center gap-1 shadow-sm active:scale-95 transition-transform"
                                    >
                                        <span>+</span>
                                        <span class="text-[10px]">যোগ</span>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Right: Active Cart Panel with Change Calculator (5 Cols, or Mobile Drawer) -->
                <div
                    :class="mobileCartOpen ? 'fixed inset-0 z-50 p-4 bg-black/80 flex items-end sm:items-center justify-center' : (deviceView === 'mobile' ? 'hidden' : 'hidden lg:block lg:col-span-5')"
                >
                    <div class="w-full sm:max-w-md fc-card p-4 rounded-3xl shadow-xl border border-[var(--fc-border)] max-h-[90vh] flex flex-col justify-between bg-[var(--fc-card)]">
                        <!-- Cart Header -->
                        <div class="flex items-center justify-between pb-2.5 border-b border-[var(--fc-border)] mb-3">
                            <div class="flex items-center gap-2">
                                <span class="text-base">🛒</span>
                                <h3 class="font-black text-sm text-[var(--fc-text)]">কাউন্টার অর্ডার কার্ট</h3>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" @click="clearCart()" class="text-[11px] text-red-500 font-bold hover:underline" x-show="cart.length > 0">খালি করুন</button>
                                <button type="button" @click="mobileCartOpen = false" :class="deviceView === 'mobile' ? 'block' : 'lg:hidden'" class="text-lg font-bold text-[var(--fc-text-muted)]">&times;</button>
                            </div>
                        </div>

                        <!-- Cart Items List -->
                        <div class="space-y-2 overflow-y-auto max-h-48 pr-1 flex-1">
                            <template x-for="item in cart" :key="item.id">
                                <div class="p-2.5 rounded-2xl bg-[var(--fc-bg)] border border-[var(--fc-border)] flex items-center justify-between text-xs">
                                    <div>
                                        <p class="font-bold text-[var(--fc-text)]" x-text="item.name"></p>
                                        <p class="text-[11px] text-[var(--fc-text-muted)]">৳<span x-text="item.price"></span> × <span x-text="item.qty"></span></p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-black text-emerald-600 dark:text-emerald-400">৳<span x-text="item.price * item.qty"></span></span>
                                        <div class="flex items-center gap-1">
                                            <button type="button" @click="decreaseQty(item.id)" class="size-6 rounded-lg bg-[var(--fc-card)] border border-[var(--fc-border)] font-bold flex items-center justify-center text-xs">-</button>
                                            <span class="w-4 text-center font-bold" x-text="item.qty"></span>
                                            <button type="button" @click="addToCart(item)" class="size-6 rounded-lg bg-[var(--fc-card)] border border-[var(--fc-border)] font-bold flex items-center justify-center text-xs">+</button>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <div x-show="cart.length === 0" class="py-8 text-center text-xs text-[var(--fc-text-muted)]">
                                <p>কার্ট খালি। খাবার যোগ করতে ক্লিক করুন।</p>
                            </div>
                        </div>

                        <!-- Order Form, Change Calculator & Checkout -->
                        <div class="pt-3 border-t border-[var(--fc-border)] space-y-2.5 mt-3">
                            <!-- Total Amount Banner -->
                            <div class="p-2.5 rounded-xl bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-between text-xs">
                                <span class="font-bold text-emerald-700 dark:text-emerald-300">মোট বিল:</span>
                                <span class="font-black text-lg text-emerald-600 dark:text-emerald-400">৳<span x-text="cartTotalPrice()"></span></span>
                            </div>

                            <!-- Speed Cash & Change Calculator (Street-Food Game Changer!) -->
                            <div class="p-2.5 rounded-xl bg-[var(--fc-bg)] border border-[var(--fc-border)] space-y-1.5">
                                <div class="flex items-center justify-between text-[11px]">
                                    <span class="font-bold text-[var(--fc-text)]">💵 কাস্টমার ক্যাশ দিয়েছেন:</span>
                                    <!-- Quick Note Buttons -->
                                    <div class="flex items-center gap-1">
                                        <button type="button" @click="cashReceived = 100" class="px-1.5 py-0.5 rounded bg-[var(--fc-card)] border border-[var(--fc-border)] text-[10px] font-bold hover:bg-emerald-500 hover:text-slate-950">100</button>
                                        <button type="button" @click="cashReceived = 200" class="px-1.5 py-0.5 rounded bg-[var(--fc-card)] border border-[var(--fc-border)] text-[10px] font-bold hover:bg-emerald-500 hover:text-slate-950">200</button>
                                        <button type="button" @click="cashReceived = 500" class="px-1.5 py-0.5 rounded bg-[var(--fc-card)] border border-[var(--fc-border)] text-[10px] font-bold hover:bg-emerald-500 hover:text-slate-950">500</button>
                                        <button type="button" @click="cashReceived = 1000" class="px-1.5 py-0.5 rounded bg-[var(--fc-card)] border border-[var(--fc-border)] text-[10px] font-bold hover:bg-emerald-500 hover:text-slate-950">1000</button>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2">
                                    <input
                                        type="number"
                                        x-model.number="cashReceived"
                                        placeholder="টাকার পরিমাণ লিখুন (যেমন: 500)"
                                        class="flex-1 px-2.5 py-1.5 rounded-lg border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs text-[var(--fc-text)] outline-none"
                                    />
                                    <button type="button" @click="cashReceived = cartTotalPrice()" class="px-2 py-1.5 rounded-lg bg-[var(--fc-card)] border border-[var(--fc-border)] text-[10px] font-bold hover:bg-emerald-500/20">
                                        বরাবর
                                    </button>
                                </div>

                                <!-- Change Return Calculation Result -->
                                <template x-if="cashReceived > 0 && cartTotalPrice() > 0">
                                    <div class="pt-1 text-xs">
                                        <template x-if="cashReceived >= cartTotalPrice()">
                                            <div class="flex items-center justify-between font-bold text-emerald-600 dark:text-emerald-400">
                                                <span>বাকি ফেরত দিতে হবে:</span>
                                                <span class="text-sm font-black" x-text="'৳' + (cashReceived - cartTotalPrice())"></span>
                                            </div>
                                        </template>
                                        <template x-if="cashReceived < cartTotalPrice()">
                                            <div class="flex items-center justify-between font-bold text-amber-500">
                                                <span>আরও দিতে হবে:</span>
                                                <span class="text-sm font-black" x-text="'৳' + (cartTotalPrice() - cashReceived)"></span>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>

                            <!-- Order Kitchen Status Target -->
                            <div>
                                <label class="block text-[10px] font-bold text-[var(--fc-text-muted)] mb-1">অর্ডারের পরবর্তী ধাপ:</label>
                                <div class="grid grid-cols-2 gap-1.5 text-xs font-bold">
                                    <button
                                        type="button"
                                        @click="targetStatus = 'preparing'"
                                        :class="targetStatus === 'preparing' ? 'bg-amber-500 text-slate-950 font-black' : 'bg-[var(--fc-bg)] text-[var(--fc-text-muted)] border border-[var(--fc-border)]'"
                                        class="py-1.5 rounded-xl text-center flex items-center justify-center gap-1 transition-all"
                                    >
                                        <span>🍳 কিচেনে পাঠান</span>
                                    </button>
                                    <button
                                        type="button"
                                        @click="targetStatus = 'completed'"
                                        :class="targetStatus === 'completed' ? 'bg-emerald-500 text-slate-950 font-black' : 'bg-[var(--fc-bg)] text-[var(--fc-text-muted)] border border-[var(--fc-border)]'"
                                        class="py-1.5 rounded-xl text-center flex items-center justify-center gap-1 transition-all"
                                    >
                                        <span>✅ তৈরি খাবার ডেলিভারি</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Order Type & Payment Method -->
                            <div class="grid grid-cols-2 gap-2 text-xs">
                                <div>
                                    <label class="block text-[10px] font-bold text-[var(--fc-text-muted)] mb-1">অর্ডার ধরন:</label>
                                    <select x-model="orderType" class="w-full px-2.5 py-1.5 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-bg)] text-xs text-[var(--fc-text)] outline-none">
                                        <option value="counter">কাউন্টার পিকআপ</option>
                                        <option value="takeaway">পার্সেল</option>
                                        <option value="dine_in">বসে খাওয়া</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-[var(--fc-text-muted)] mb-1">পেমেন্ট মাধ্যম:</label>
                                    <select x-model="paymentMethod" class="w-full px-2.5 py-1.5 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-bg)] text-xs text-[var(--fc-text)] outline-none">
                                        <option value="cash">💵 ক্যাশ</option>
                                        <option value="bkash">📱 বিকাশ</option>
                                        <option value="nagad">⚡ নগদ</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Customer Phone (Optional) -->
                            <input
                                type="tel"
                                x-model="customerPhone"
                                placeholder="কাস্টমার মোবাইল (ঐচ্ছিক)"
                                class="w-full px-3 py-1.5 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-bg)] text-xs text-[var(--fc-text)] outline-none"
                            />

                            <!-- Submit POS Order Button -->
                            <button
                                type="button"
                                @click="submitPosOrder()"
                                :disabled="cart.length === 0 || isSubmitting"
                                class="w-full py-3.5 rounded-2xl bg-emerald-500 hover:bg-emerald-400 disabled:opacity-50 text-slate-950 font-black text-xs flex items-center justify-center gap-1.5 shadow-md active:scale-98 transition-all"
                            >
                                <span x-show="!isSubmitting">✅ অর্ডার নিশ্চিত করুন (৳<span x-text="cartTotalPrice()"></span>)</span>
                                <span x-show="isSubmitting">প্রক্রিয়াকরণ হচ্ছে...</span>
                            </button>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Floating Mobile Cart Button (for Mobile View) -->
            <div
                x-show="cartTotalCount() > 0 && deviceView === 'mobile'"
                x-transition
                class="fixed bottom-4 left-0 right-0 z-40 px-4 pointer-events-none"
            >
                <div class="max-w-md mx-auto pointer-events-auto">
                    <button
                        @click="mobileCartOpen = true"
                        type="button"
                        class="w-full py-3 px-4 rounded-2xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 shadow-xl font-black text-xs sm:text-sm flex items-center justify-between active:scale-95 transition-all"
                    >
                        <div class="flex items-center gap-2">
                            <span class="size-6 rounded-lg bg-slate-950/20 flex items-center justify-center font-bold text-xs" x-text="cartTotalCount()"></span>
                            <span>কার্ট দেখুন ও ক্যাশ হিসাব</span>
                        </div>
                        <div class="flex items-center gap-1.5 text-sm">
                            <span x-text="'৳' + cartTotalPrice()"></span>
                            <span>&rarr;</span>
                        </div>
                    </button>
                </div>
            </div>
        </div>

        <!-- =================================================================== -->
        <!-- TAB 2: LIVE KITCHEN DISPLAY SYSTEM (KDS) -->
        <!-- =================================================================== -->
        <div x-show="activeTab === 'kitchen'" class="space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2 border-b border-[var(--fc-border)]">
                <div>
                    <h2 class="text-base font-black text-[var(--fc-text)]">কিচেন ও লাইভ চলমান অর্ডারসমূহ</h2>
                    <p class="text-xs text-[var(--fc-text-muted)]">নতুন অনলাইন ও কাউন্টার অর্ডার আসলে স্বয়ংক্রিয়ভাবে অডিও বেল বাজবে</p>
                </div>

                <!-- Kitchen Filter Pills & Refresh -->
                <div class="flex items-center gap-2">
                    <div class="flex items-center gap-1 bg-[var(--fc-card)] p-1 rounded-xl border border-[var(--fc-border)] text-xs">
                        <button
                            type="button"
                            @click="kitchenFilter = 'all'"
                            :class="kitchenFilter === 'all' ? 'bg-[var(--fc-primary)] text-[var(--fc-primary-text)] font-black' : 'text-[var(--fc-text-muted)]'"
                            class="px-2.5 py-1 rounded-lg font-bold"
                        >সব (<span x-text="liveOrdersList.length"></span>)</button>
                        <button
                            type="button"
                            @click="kitchenFilter = 'pending'"
                            :class="kitchenFilter === 'pending' ? 'bg-amber-500 text-slate-950 font-black' : 'text-[var(--fc-text-muted)]'"
                            class="px-2.5 py-1 rounded-lg font-bold"
                        >পেন্ডিং</button>
                        <button
                            type="button"
                            @click="kitchenFilter = 'preparing'"
                            :class="kitchenFilter === 'preparing' ? 'bg-blue-500 text-white font-black' : 'text-[var(--fc-text-muted)]'"
                            class="px-2.5 py-1 rounded-lg font-bold"
                        >রান্না হচ্ছে</button>
                        <button
                            type="button"
                            @click="kitchenFilter = 'ready'"
                            :class="kitchenFilter === 'ready' ? 'bg-emerald-500 text-slate-950 font-black' : 'text-[var(--fc-text-muted)]'"
                            class="px-2.5 py-1 rounded-lg font-bold"
                        >রেডি</button>
                    </div>

                    <button type="button" @click="pollLiveOrders()" class="px-3 py-1.5 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs font-bold text-[var(--fc-text)] flex items-center gap-1 hover:bg-[var(--fc-bg)]">
                        <span>🔄 রিফ্রেশ</span>
                    </button>
                </div>
            </div>

            <!-- Orders Cards Grid -->
            <div :class="deviceView === 'mobile' ? 'grid grid-cols-1 gap-3' : 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3'">
                <template x-for="order in filteredKitchenOrders" :key="order.id">
                    <div
                        class="fc-card p-4 rounded-3xl shadow-xs border flex flex-col justify-between space-y-3 transition-all"
                        :class="order.order_status === 'pending' ? 'border-amber-500/60 bg-amber-500/5' : (order.order_status === 'ready' ? 'border-emerald-500/60 bg-emerald-500/5' : 'border-[var(--fc-border)]')"
                    >
                        <div>
                            <!-- Header: Order #, Time & Status -->
                            <div class="flex items-start justify-between gap-2 mb-2 pb-2 border-b border-[var(--fc-border)]/60">
                                <div>
                                    <span class="font-black text-sm text-[var(--fc-primary)]" x-text="'#' + order.order_number"></span>
                                    <p class="text-[10px] text-[var(--fc-text-muted)]" x-text="order.created_time + ' (' + (order.time_diff || '') + ')'"></p>
                                </div>
                                <span
                                    class="px-2.5 py-0.5 rounded-full font-black text-[10px] uppercase"
                                    :class="{
                                        'bg-amber-500/20 text-amber-600 dark:text-amber-300 border border-amber-500/30 animate-pulse': order.order_status === 'pending',
                                        'bg-blue-500/20 text-blue-600 dark:text-blue-300 border border-blue-500/30': order.order_status === 'preparing',
                                        'bg-emerald-500/20 text-emerald-600 dark:text-emerald-300 border border-emerald-500/30': order.order_status === 'ready'
                                    }"
                                    x-text="order.status_bn"
                                ></span>
                            </div>

                            <!-- Customer & Table/Parcel Info -->
                            <div class="flex items-center justify-between gap-1 mb-1.5">
                                <p class="text-xs font-bold text-[var(--fc-text)] truncate" x-text="'কাস্টমার: ' + (order.customer_name || 'গেস্ট')"></p>
                                <span
                                    class="px-2 py-0.5 rounded-lg text-[10px] font-black shrink-0"
                                    :class="order.table_no ? 'bg-purple-500/20 text-purple-400 border border-purple-500/30' : 'bg-orange-500/20 text-orange-400 border border-orange-500/30'"
                                    x-text="order.table_no ? ('🪑 টেবিল ' + order.table_no) : '🛍️ পার্সেল'"
                                ></span>
                            </div>
                            <div class="flex items-center justify-between text-[10px] pb-1 text-[var(--fc-text-muted)] font-semibold">
                                <span x-text="'পেমেন্ট: ' + (order.payment_method === 'bkash' ? 'বিকাশ' : (order.payment_method === 'nagad' ? 'নগদ' : 'ক্যাশ'))"></span>
                                <span class="font-black text-amber-500" x-text="'মোট ৳' + order.total_amount"></span>
                            </div>

                            <!-- Items List -->
                            <div class="space-y-1.5 bg-[var(--fc-bg)] p-3 rounded-2xl text-xs border border-[var(--fc-border)]/50">
                                <template x-for="item in order.items" :key="item.id">
                                    <div class="flex justify-between items-center">
                                        <span class="font-bold text-[var(--fc-text)]" x-text="item.food_name"></span>
                                        <span class="font-black text-emerald-500 text-xs px-2 py-0.5 rounded bg-[var(--fc-card)] border border-[var(--fc-border)]" x-text="'× ' + item.quantity"></span>
                                    </div>
                                </template>
                            </div>

                            <!-- Notes (if any) -->
                            <p x-show="order.notes" class="text-[11px] text-amber-500 mt-2 italic bg-amber-500/10 p-2 rounded-xl border border-amber-500/20" x-text="order.notes"></p>
                        </div>

                        <!-- 1-Tap Status Action Buttons -->
                        <div class="pt-2 border-t border-[var(--fc-border)]/60 flex flex-col gap-2">
                            <!-- If Pending: Start Cooking -->
                            <button
                                type="button"
                                x-show="order.order_status === 'pending'"
                                @click="updateStatus(order.id, 'preparing')"
                                class="w-full py-3 rounded-2xl bg-blue-500 hover:bg-blue-400 text-white font-black text-xs flex items-center justify-center gap-1.5 shadow-md active:scale-98 transition-all"
                            >
                                <span>🍳 রান্না শুরু করুন (Start Cooking)</span>
                            </button>

                            <!-- If Preparing: Mark Ready -->
                            <button
                                type="button"
                                x-show="order.order_status === 'preparing'"
                                @click="updateStatus(order.id, 'ready')"
                                class="w-full py-3 rounded-2xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs flex items-center justify-center gap-1.5 shadow-md active:scale-98 transition-all"
                            >
                                <span>🔔 খাবার তৈরি শেষ (Mark Ready)</span>
                            </button>

                            <!-- If Ready: Mark Completed / Served -->
                            <button
                                type="button"
                                x-show="order.order_status === 'ready'"
                                @click="updateStatus(order.id, 'completed')"
                                class="w-full py-3 rounded-2xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black text-xs flex items-center justify-center gap-1.5 shadow-md active:scale-98 transition-all"
                            >
                                <span>✅ কাস্টমারকে দেওয়া হয়েছে (Served)</span>
                            </button>

                            <!-- Invoice Button -->
                            <a
                                :href="'/orders/' + order.id + '/invoice'"
                                target="_blank"
                                class="text-center text-[10px] text-[var(--fc-text-muted)] hover:text-[var(--fc-text)] hover:underline"
                            >
                                📄 রসিদ টোকেন দেখুন
                            </a>
                        </div>
                    </div>
                </template>

                <div x-show="filteredKitchenOrders.length === 0" class="col-span-full py-12 text-center fc-card p-6 rounded-3xl border border-[var(--fc-border)]">
                    <span class="text-4xl">🎉</span>
                    <h3 class="font-bold text-sm text-[var(--fc-text)] mt-2">বর্তমানে কোনো পেন্ডিং অর্ডার নেই!</h3>
                    <p class="text-xs text-[var(--fc-text-muted)] mt-1">সব খাবার প্রস্তুত ও কাস্টমারকে সরবরাহ করা হয়েছে। নতুন অর্ডার আসলে অ্যালার্ট বেজে উঠবে।</p>
                </div>
            </div>
        </div>

        <!-- =================================================================== -->
        <!-- TAB 3: ALL TODAY'S ORDERS & RECEIPTS -->
        <!-- =================================================================== -->
        <div x-show="activeTab === 'all_orders'" class="space-y-4">
            <div class="flex items-center justify-between pb-2 border-b border-[var(--fc-border)]">
                <div>
                    <h2 class="text-base font-black text-[var(--fc-text)]">আজকের নিশ্চিত করা সকল অর্ডার ও রসিদ</h2>
                    <p class="text-xs text-[var(--fc-text-muted)]">কাউন্টার ও অনলাইন থেকে আজকের নিশ্চিত হওয়া সমস্ত টোকেন ও বিল</p>
                </div>
                <button type="button" @click="location.reload()" class="px-3 py-1.5 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs font-bold text-[var(--fc-text)] flex items-center gap-1">
                    <span>🔄 রিফ্রেশ</span>
                </button>
            </div>

            <!-- Orders Table / Mobile Cards -->
            <div class="space-y-3">
                <template x-for="order in allTodayOrdersList" :key="order.id">
                    <div class="fc-card p-4 rounded-3xl shadow-xs border border-[var(--fc-border)] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="space-y-1.5">
                            <div class="flex items-center gap-2">
                                <span class="font-black text-sm text-[var(--fc-primary)]" x-text="'#' + order.order_number"></span>
                                <span class="text-xs text-[var(--fc-text-muted)]" x-text="order.created_time"></span>
                                <span
                                    class="px-2.5 py-0.5 rounded-full font-bold text-[10px]"
                                    :class="{
                                        'bg-amber-500/15 text-amber-600': order.order_status === 'pending',
                                        'bg-blue-500/15 text-blue-600': order.order_status === 'preparing',
                                        'bg-emerald-500/15 text-emerald-600': order.order_status === 'ready' || order.order_status === 'completed'
                                    }"
                                    x-text="order.status_bn"
                                ></span>
                            </div>

                            <p class="text-xs text-[var(--fc-text)] font-semibold">
                                কাস্টমার: <span x-text="order.customer_name"></span>
                                <span x-show="order.customer_phone" class="text-[var(--fc-text-muted)] font-normal" x-text="'(' + order.customer_phone + ')'"></span>
                            </p>

                            <!-- Items Summary -->
                            <div class="flex flex-wrap gap-1.5 text-xs text-[var(--fc-text-muted)]">
                                <template x-for="item in order.items" :key="item.id">
                                    <span class="px-2 py-0.5 rounded-lg bg-[var(--fc-bg)] border border-[var(--fc-border)] font-medium text-[11px]">
                                        <span x-text="item.food_name"></span>
                                        <strong class="text-[var(--fc-primary)]" x-text="' × ' + item.quantity"></strong>
                                    </span>
                                </template>
                            </div>
                        </div>

                        <!-- Right: Total & Invoice Button -->
                        <div class="flex items-center sm:flex-col sm:items-end justify-between gap-2 pt-2 sm:pt-0 border-t sm:border-t-0 border-[var(--fc-border)]/60">
                            <div>
                                <span class="text-base font-black text-emerald-600 dark:text-emerald-400" x-text="'৳' + order.total_amount"></span>
                                <span class="text-[10px] text-[var(--fc-text-muted)] uppercase block text-end" x-text="order.payment_method"></span>
                            </div>

                            <a
                                :href="'/orders/' + order.id + '/invoice'"
                                target="_blank"
                                class="px-3 py-1.5 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-bg)] hover:bg-[var(--fc-card)] text-xs font-bold text-[var(--fc-text)] flex items-center gap-1 transition-colors shadow-2xs"
                            >
                                <span>📄 রসিদ / টোকেন প্রিন্ট</span>
                            </a>
                        </div>
                    </div>
                </template>

                <div x-show="allTodayOrdersList.length === 0" class="py-12 text-center fc-card p-6 rounded-3xl border border-[var(--fc-border)]">
                    <p class="text-xs text-[var(--fc-text-muted)]">আজকে এখনও কোনো অর্ডার তৈরি করা হয়নি। বামপাশের POS ট্যাব থেকে প্রথম অর্ডারটি নিন!</p>
                </div>
            </div>
        </div>

        <!-- =================================================================== -->
        <!-- TAB 4: STOCK OVERVIEW -->
        <!-- =================================================================== -->
        <div x-show="activeTab === 'stock'" class="space-y-4">
            <div class="flex items-center justify-between pb-2 border-b border-[var(--fc-border)]">
                <div>
                    <h2 class="text-base font-black text-[var(--fc-text)]">খাবারের বর্তমান স্টক</h2>
                    <p class="text-xs text-[var(--fc-text-muted)]">কোন খাবারের কতটুকু স্টক বাকি আছে তা সরাসরি দেখুন</p>
                </div>
            </div>

            <div :class="deviceView === 'mobile' ? 'grid grid-cols-1 gap-2.5' : 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3'">
                @foreach($stockItems as $item)
                    <div class="fc-card p-3.5 rounded-2xl border border-[var(--fc-border)] flex items-center justify-between shadow-2xs">
                        <div>
                            <h4 class="font-bold text-xs text-[var(--fc-text)]">{{ $item->bengali_name ?? $item->name }}</h4>
                            <p class="text-[10px] text-[var(--fc-text-muted)]">{{ $item->name }}</p>
                        </div>
                        <div class="text-end">
                            <span class="px-2.5 py-1 rounded-xl font-black text-xs {{ $item->current_stock <= 5 ? 'bg-red-500/15 text-red-500 border border-red-500/30' : 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30' }}">
                                {{ $item->current_stock }} {{ $item->unit }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- =================================================================== -->
        <!-- TAB: SALES TIMELINE & ITEM-WISE FINANCIAL BREAKDOWN -->
        <!-- =================================================================== -->
        <div x-show="activeTab === 'timeline'" class="space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-2 border-b border-[var(--fc-border)] gap-2">
                <div>
                    <h2 class="text-base font-black text-[var(--fc-text)]">📊 লাইভ সেলস ও আইটেম টাইমলাইন</h2>
                    <p class="text-xs text-[var(--fc-text-muted)]">সারাদিন কোন সময়ে কোন খাবার বিক্রি হয়েছে এবং মোট লাভ-ক্ষতির পূর্ণাঙ্গ হিসাব</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-3 py-1.5 rounded-xl bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 font-bold text-xs border border-emerald-500/30">
                        মোট বিক্রি: ৳{{ number_format($summary['completed_sales'], 0) }}
                    </span>
                </div>
            </div>

            <!-- Financial Profit Flow Calculation Card -->
            <div class="p-4 rounded-2xl bg-gradient-to-br from-emerald-500/10 via-[var(--fc-card)] to-transparent border border-emerald-500/30 space-y-3">
                <h3 class="text-xs font-black text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">
                    সারাদিনের পূর্ণাঙ্গ লাভ-ক্ষতি ক্যালকুলেশন ফ্লো
                </h3>
                <div :class="deviceView === 'mobile' ? 'grid grid-cols-2 gap-2 text-center' : 'grid grid-cols-2 sm:grid-cols-4 gap-2 text-center'">
                    <div class="p-2.5 rounded-xl bg-[var(--fc-bg)] border border-[var(--fc-border)]">
                        <span class="text-[10px] text-[var(--fc-text-muted)] block font-semibold">১. মোট বিক্রি (Sales)</span>
                        <span class="text-sm font-black text-emerald-600 dark:text-emerald-400">৳{{ number_format($summary['completed_sales'], 0) }}</span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-[var(--fc-bg)] border border-[var(--fc-border)]">
                        <span class="text-[10px] text-[var(--fc-text-muted)] block font-semibold">২. কাঁচামাল ও খরচ (Cost)</span>
                        <span class="text-sm font-black text-amber-500">- ৳{{ number_format($summary['cogs'] + $summary['total_expenses'], 0) }}</span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-[var(--fc-bg)] border border-[var(--fc-border)]">
                        <span class="text-[10px] text-[var(--fc-text-muted)] block font-semibold">৩. নষ্ট খাবার (Waste)</span>
                        <span class="text-sm font-black text-red-500">- ৳{{ number_format($summary['total_waste'], 0) }}</span>
                    </div>
                    <div class="p-2.5 rounded-xl bg-emerald-500/15 border border-emerald-500/40">
                        <span class="text-[10px] text-emerald-600 dark:text-emerald-400 block font-bold">৪. বর্তমান নীট লাভ</span>
                        <span class="text-sm font-black text-emerald-500">৳{{ number_format($summary['net_profit'], 0) }}</span>
                    </div>
                </div>
            </div>

            <!-- 2-Column Grid: Timeline on Left/Top, Item Aggregation on Right/Bottom -->
            <div :class="deviceView === 'mobile' ? 'space-y-4' : 'grid grid-cols-1 lg:grid-cols-12 gap-4'">
                <!-- Timeline: Individually which item sold at what exact time -->
                <div class="lg:col-span-7 space-y-3">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xs font-black text-[var(--fc-text)] flex items-center gap-1.5">
                            <span>⏱️</span>
                            <span>আইটেম অনুযায়ী বিক্রির টাইমলাইন (Chronological Sales Log)</span>
                        </h3>
                        <span class="text-[10px] text-[var(--fc-text-muted)]">সর্বমোট {{ count($salesTimeline) }}টি খাবার বিক্রয়</span>
                    </div>

                    @if(count($salesTimeline) > 0)
                        <div class="space-y-2 max-h-[600px] overflow-y-auto pr-1">
                            @foreach($salesTimeline as $sale)
                                <div class="p-3 rounded-2xl bg-[var(--fc-card)] border border-[var(--fc-border)] flex items-center justify-between gap-3 shadow-2xs hover:border-[var(--fc-primary)]/40 transition-colors">
                                    <div class="flex items-center gap-3">
                                        <div class="size-9 rounded-xl bg-[var(--fc-bg)] border border-[var(--fc-border)] flex flex-col items-center justify-center shrink-0">
                                            <span class="text-[9px] font-black text-[var(--fc-primary)]">{{ Carbon\Carbon::parse(data_get($sale, 'time') ?? data_get($sale, 'created_at'))->format('h:i') }}</span>
                                            <span class="text-[8px] text-[var(--fc-text-muted)]">{{ Carbon\Carbon::parse(data_get($sale, 'time') ?? data_get($sale, 'created_at'))->format('A') }}</span>
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <h4 class="font-black text-xs text-[var(--fc-text)]">{{ data_get($sale, 'food_name') }}</h4>
                                                <span class="px-1.5 py-0.2 rounded text-[9px] font-black {{ in_array(data_get($sale, 'order_type'), ['parcel', 'takeaway']) ? 'bg-blue-500/15 text-blue-400 border border-blue-500/30' : 'bg-slate-700/40 text-slate-300' }}">
                                                    {{ data_get($sale, 'order_type_bn', 'কাউন্টার') }}
                                                </span>
                                            </div>
                                            <p class="text-[10px] text-[var(--fc-text-muted)] mt-0.5">
                                                <span>{{ data_get($sale, 'quantity') }} টি × ৳{{ number_format((float) data_get($sale, 'unit_price'), 0) }}</span>
                                                <span class="mx-1">•</span>
                                                <span class="uppercase font-semibold text-[9px]">{{ data_get($sale, 'payment_method') }}</span>
                                                @if(data_get($sale, 'customer_name'))
                                                    <span class="mx-1">•</span>
                                                    <span>{{ data_get($sale, 'customer_name') }}</span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>

                                    <div class="text-end shrink-0">
                                        <div class="text-xs font-black text-emerald-600 dark:text-emerald-400">
                                            +৳{{ number_format((float) data_get($sale, 'subtotal'), 0) }}
                                        </div>
                                        <div class="text-[9px] text-[var(--fc-text-muted)]">
                                            লাভ: ৳{{ number_format((float) data_get($sale, 'profit'), 0) }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-8 text-center rounded-2xl bg-[var(--fc-card)] border border-[var(--fc-border)]">
                            <p class="text-xs text-[var(--fc-text-muted)]">আজকে এখনও কোনো খাবার বিক্রি হয়নি। POS থেকে প্রথম অর্ডার নিন!</p>
                        </div>
                    @endif
                </div>

                <!-- Right: Item-Wise Sales Summary -->
                <div class="lg:col-span-5 space-y-3">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xs font-black text-[var(--fc-text)] flex items-center gap-1.5">
                            <span>📦</span>
                            <span>খাবারভিত্তিক মোট বিক্রয় (Item-wise Summary)</span>
                        </h3>
                    </div>

                    @if(count($itemWiseSales) > 0)
                        <div class="p-3.5 rounded-2xl bg-[var(--fc-card)] border border-[var(--fc-border)] space-y-2">
                            @foreach($itemWiseSales as $item)
                                <div class="p-2.5 rounded-xl bg-[var(--fc-bg)] border border-[var(--fc-border)]/60 flex items-center justify-between">
                                    <div>
                                        <h4 class="text-xs font-bold text-[var(--fc-text)]">{{ data_get($item, 'food_name') }}</h4>
                                        <span class="text-[10px] text-[var(--fc-text-muted)]">মোট বিক্রি: {{ data_get($item, 'quantity') ?? data_get($item, 'total_quantity') }} টি</span>
                                    </div>
                                    <div class="text-end">
                                        <span class="text-xs font-black text-emerald-600 dark:text-emerald-400">৳{{ number_format((float) (data_get($item, 'revenue') ?? data_get($item, 'total_revenue')), 0) }}</span>
                                        <span class="text-[9px] text-emerald-500 block font-semibold">লাভ: ৳{{ number_format((float) (data_get($item, 'profit') ?? data_get($item, 'total_profit')), 0) }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-8 text-center rounded-2xl bg-[var(--fc-card)] border border-[var(--fc-border)]">
                            <p class="text-xs text-[var(--fc-text-muted)]">কোনো আইটেম বিক্রি পাওয়া যায়নি</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- =================================================================== -->
        <!-- TAB: PARCEL ORDERS ("কারা পার্সেল নিলো") -->
        <!-- =================================================================== -->
        <div x-show="activeTab === 'parcel'" class="space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-2 border-b border-[var(--fc-border)] gap-2">
                <div>
                    <h2 class="text-base font-black text-[var(--fc-text)]">🛍️ পার্সেল নেওয়া কাস্টমারদের তালিকা</h2>
                    <p class="text-xs text-[var(--fc-text-muted)]">আজকে যারা খাবার পার্সেল/টেকওয়ে নিয়ে গেছেন তাদের তালিকা, মোবাইল নম্বর ও রসিদ</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-3 py-1.5 rounded-xl bg-blue-500/15 text-blue-500 font-bold text-xs border border-blue-500/30">
                        মোট পার্সেল: {{ $summary['parcel_orders'] }}টি (৳{{ number_format($summary['parcel_sales'], 0) }})
                    </span>
                </div>
            </div>

            @if($parcelOrders->isNotEmpty())
                <div :class="deviceView === 'mobile' ? 'grid grid-cols-1 gap-3' : 'grid grid-cols-1 md:grid-cols-2 gap-3'">
                    @foreach($parcelOrders as $pOrder)
                        <div class="p-3.5 sm:p-4 rounded-2xl bg-[var(--fc-card)] border border-[var(--fc-border)] flex flex-col justify-between gap-3 shadow-2xs">
                            <div class="space-y-2.5">
                                <div class="flex items-start justify-between gap-2 pb-2 border-b border-[var(--fc-border)]/50">
                                    <div class="flex items-center gap-2.5">
                                        <span class="size-8 rounded-xl bg-blue-500/15 text-blue-500 flex items-center justify-center font-black text-sm shrink-0">🛍️</span>
                                        <div>
                                            <span class="font-black text-xs sm:text-sm text-[var(--fc-text)] block whitespace-nowrap">#{{ $pOrder->order_number }}</span>
                                            <span class="text-[10px] text-[var(--fc-text-muted)] block">{{ $pOrder->created_at->format('h:i A') }} ({{ $pOrder->created_at->diffForHumans() }})</span>
                                        </div>
                                    </div>
                                    <div class="text-end shrink-0">
                                        <span class="text-sm sm:text-base font-black text-emerald-600 dark:text-emerald-400 block">৳{{ number_format($pOrder->total_amount, 0) }}</span>
                                        <span class="text-[9px] uppercase font-bold text-[var(--fc-text-muted)] block">{{ $pOrder->payment_method }}</span>
                                    </div>
                                </div>

                                <div class="p-2.5 rounded-xl bg-[var(--fc-bg)] border border-[var(--fc-border)]/60 text-xs flex items-center justify-between flex-wrap gap-1">
                                    <div class="font-bold text-[var(--fc-text)]">
                                        <span>কাস্টমার:</span> {{ $pOrder->customer?->name ?? 'গেস্ট কাস্টমার' }}
                                    </div>
                                    @if($pOrder->customer?->phone)
                                        <a href="tel:{{ $pOrder->customer->phone }}" class="text-[11px] font-bold text-blue-500 hover:underline">
                                            {{ $pOrder->customer->phone }} 📞
                                        </a>
                                    @endif
                                </div>

                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($pOrder->items as $pItem)
                                        <span class="px-2.5 py-1 rounded-xl bg-[var(--fc-bg)] border border-[var(--fc-border)] text-xs font-medium text-[var(--fc-text)] flex items-center gap-1">
                                            <span>{{ $pItem->food_name }}</span>
                                            <span class="font-black text-emerald-600 dark:text-emerald-400">×{{ $pItem->quantity }}</span>
                                        </span>
                                    @endforeach
                                </div>
                            </div>

                            <!-- WhatsApp Receipt Link Button & Invoice Link -->
                            <div class="pt-2.5 border-t border-[var(--fc-border)]/60 flex items-center gap-2">
                                @if($pOrder->customer?->phone)
                                    @php
                                        $cleanPhone = preg_replace('/[^0-9]/', '', $pOrder->customer->phone);
                                        if (!str_starts_with($cleanPhone, '88')) {
                                            $cleanPhone = '88' . ltrim($cleanPhone, '0');
                                        }
                                        $waText = urlencode("আসসালামু আলাইকুম! আপনার পার্সেল অর্ডার #{$pOrder->order_number} গ্রহণ করা হয়েছে। মোট বিল: ৳" . number_format($pOrder->total_amount, 0) . "। আমাদের ফুড কার্ট থেকে খাবার নেওয়ার জন্য ধন্যবাদ!");
                                    @endphp
                                    <a
                                        href="https://wa.me/{{ $cleanPhone }}?text={{ $waText }}"
                                        target="_blank"
                                        class="flex-1 py-2 px-3 rounded-xl bg-emerald-500/15 hover:bg-emerald-500/25 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30 text-xs font-bold flex items-center justify-center gap-1.5 transition-colors shadow-2xs whitespace-nowrap"
                                    >
                                        <span>💬 হোয়াটসঅ্যাপে রসিদ</span>
                                    </a>
                                @endif

                                <a
                                    href="{{ route('orders.invoice', $pOrder) }}"
                                    target="_blank"
                                    class="py-2 px-3 rounded-xl bg-[var(--fc-bg)] hover:bg-[var(--fc-card)] border border-[var(--fc-border)] text-xs font-bold text-[var(--fc-text)] flex items-center justify-center gap-1 shrink-0 shadow-2xs whitespace-nowrap"
                                >
                                    <span>📄 রসিদ</span>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="p-8 text-center rounded-2xl bg-[var(--fc-card)] border border-[var(--fc-border)]">
                    <p class="text-xs text-[var(--fc-text-muted)]">আজকে কোনো পার্সেল অর্ডার গ্রহণ করা হয়নি।</p>
                </div>
            @endif
        </div>

        <!-- =================================================================== -->
        <!-- TAB: PAYMENT BREAKDOWN (ক্যাশ ও ডিজিটাল পেমেন্ট) -->
        <!-- =================================================================== -->
        <div x-show="activeTab === 'payments'" class="space-y-4">
            <div class="flex items-center justify-between pb-2 border-b border-[var(--fc-border)]">
                <div>
                    <h2 class="text-base font-black text-[var(--fc-text)]">💵 পেমেন্ট মাধ্যম ও ড্রয়ারের ক্যাশ হিসাব</h2>
                    <p class="text-xs text-[var(--fc-text-muted)]">কোন মাধ্যমে কত টাকা পেমেন্ট হয়েছে এবং ড্রয়ারের বর্তমান ক্যাশ ব্যালেন্স</p>
                </div>
            </div>

            <div :class="deviceView === 'mobile' ? 'grid grid-cols-2 gap-2' : 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3'">
                <!-- Cash -->
                <div class="p-3 sm:p-4 rounded-2xl bg-[var(--fc-card)] border border-emerald-500/30 space-y-1.5 overflow-hidden">
                    <div class="flex items-center justify-between gap-1">
                        <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 truncate">💵 নগদ ক্যাশ</span>
                        <span class="size-6 rounded-lg bg-emerald-500/15 text-emerald-500 flex items-center justify-center text-[9px] font-black shrink-0">নগদ</span>
                    </div>
                    <div class="text-base sm:text-xl font-black text-[var(--fc-text)] truncate">
                        ৳{{ number_format($summary['payment_breakdown']['cash'], 0) }}
                    </div>
                    <p class="text-[10px] text-[var(--fc-text-muted)] truncate">কাউন্টার ড্রয়ারে ক্যাশ</p>
                </div>

                <!-- Total Combined -->
                <div class="p-3 sm:p-4 rounded-2xl bg-[var(--fc-card)] border border-blue-500/30 space-y-1.5 overflow-hidden">
                    <div class="flex items-center justify-between gap-1">
                        <span class="text-xs font-bold text-blue-500 truncate">💳 মোট সংগৃহীত</span>
                        <span class="size-6 rounded-xl bg-blue-500/15 text-blue-500 flex items-center justify-center text-[9px] font-black shrink-0">মোট</span>
                    </div>
                    <div class="text-base sm:text-xl font-black text-emerald-600 dark:text-emerald-400 truncate">
                        ৳{{ number_format($summary['completed_sales'], 0) }}
                    </div>
                    <p class="text-[10px] text-[var(--fc-text-muted)] truncate">আজকের সর্বমোট পেমেন্ট</p>
                </div>

                <!-- bKash -->
                <div class="p-3 sm:p-4 rounded-2xl bg-[var(--fc-card)] border border-pink-500/30 space-y-1.5 overflow-hidden">
                    <div class="flex items-center justify-between gap-1">
                        <span class="text-xs font-bold text-pink-500 truncate">📱 বিকাশ (bKash)</span>
                        <span class="size-6 rounded-lg bg-pink-500/15 text-pink-500 flex items-center justify-center text-[9px] font-black shrink-0">BK</span>
                    </div>
                    <div class="text-base sm:text-xl font-black text-[var(--fc-text)] truncate">
                        ৳{{ number_format($summary['payment_breakdown']['bkash'], 0) }}
                    </div>
                    <p class="text-[10px] text-[var(--fc-text-muted)] truncate">বিকাশ ওয়ালেটে জমা</p>
                </div>

                <!-- Nagad -->
                <div class="p-3 sm:p-4 rounded-2xl bg-[var(--fc-card)] border border-amber-500/30 space-y-1.5 overflow-hidden">
                    <div class="flex items-center justify-between gap-1">
                        <span class="text-xs font-bold text-amber-500 truncate">⚡ নগদ (Nagad)</span>
                        <span class="size-6 rounded-lg bg-amber-500/15 text-amber-500 flex items-center justify-center text-[9px] font-black shrink-0">NG</span>
                    </div>
                    <div class="text-base sm:text-xl font-black text-[var(--fc-text)] truncate">
                        ৳{{ number_format($summary['payment_breakdown']['nagad'], 0) }}
                    </div>
                    <p class="text-[10px] text-[var(--fc-text-muted)] truncate">নগদ ওয়ালেটে জমা</p>
                </div>
            </div>
        </div>

        <!-- =================================================================== -->
        <!-- TAB: WASTE LOGGING (খাবার অপচয় ও ক্ষতি) -->
        <!-- =================================================================== -->
        <div x-show="activeTab === 'waste'" class="space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-2 border-b border-[var(--fc-border)] gap-2">
                <div>
                    <h2 class="text-base font-black text-[var(--fc-text)]">🗑️ নষ্ট খাবারের হিসাব (Food Waste)</h2>
                    <p class="text-xs text-[var(--fc-text-muted)]">আজকে কত টাকার খাবার পুড়ে গেছে, নষ্ট হয়েছে বা ফেলে দিতে হয়েছে</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-3 py-1.5 rounded-xl bg-red-500/15 text-red-500 font-bold text-xs border border-red-500/30">
                        আজকের মোট ওয়েস্টেজ: ৳{{ number_format($summary['total_waste'], 0) }}
                    </span>
                </div>
            </div>

            <!-- Quick Add Waste Form -->
            <div class="p-4 rounded-2xl bg-[var(--fc-card)] border border-[var(--fc-border)] space-y-3">
                <h3 class="text-xs font-black text-[var(--fc-text)] flex items-center gap-1.5">
                    <span>➕</span>
                    <span>নতুন খাবার অপচয় / নষ্টের রেকর্ড যুক্ত করুন</span>
                </h3>

                <form action="{{ route('wastes.store') }}" method="POST" :class="deviceView === 'mobile' ? 'space-y-3' : 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5 items-end'">
                    @csrf
                    <div :class="deviceView === 'mobile' ? 'w-full' : ''">
                        <label class="block text-[10px] font-bold text-[var(--fc-text-muted)] mb-1">নষ্ট হওয়া খাবার:</label>
                        <select name="food_id" required class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-bg)] text-xs text-[var(--fc-text)] outline-none">
                            <option value="">খাবার নির্বাচন করুন...</option>
                            @foreach($foods as $f)
                                <option value="{{ $f->id }}">{{ $f->bengali_name ?? $f->name }} (৳{{ $f->selling_price }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div :class="deviceView === 'mobile' ? 'grid grid-cols-2 gap-2' : 'contents'">
                        <div>
                            <label class="block text-[10px] font-bold text-[var(--fc-text-muted)] mb-1">পরিমাণ (Quantity):</label>
                            <input type="number" name="quantity" min="1" value="1" required class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-bg)] text-xs text-[var(--fc-text)] outline-none" />
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold text-[var(--fc-text-muted)] mb-1">আনুমানিক ক্ষতি (৳):</label>
                            <input type="number" step="0.01" name="estimated_cost" placeholder="যেমন: 120" required class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-bg)] text-xs text-[var(--fc-text)] outline-none" />
                        </div>
                    </div>

                    <div :class="deviceView === 'mobile' ? 'w-full' : ''">
                        <label class="block text-[10px] font-bold text-[var(--fc-text-muted)] mb-1">নষ্ট হওয়ার কারণ:</label>
                        <input type="text" name="reason" placeholder="যেমন: পুড়ে গেছে, ফেলে দিতে হলো" required class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-bg)] text-xs text-[var(--fc-text)] outline-none" />
                    </div>

                    <div :class="deviceView === 'mobile' ? 'w-full pt-1' : ''">
                        <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-red-500 hover:bg-red-400 text-white font-black text-xs transition-colors shadow-xs flex items-center justify-center gap-1">
                            <span>➕ অপচয়ের হিসাব সেভ করুন</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- List of Today's Wastes -->
            @if($todayWastes->isNotEmpty())
                <div class="space-y-2">
                    @foreach($todayWastes as $w)
                        <div class="p-3 rounded-2xl bg-[var(--fc-card)] border border-[var(--fc-border)] flex items-center justify-between gap-2">
                            <div>
                                <h4 class="text-xs font-black text-[var(--fc-text)]">{{ $w->food?->bengali_name ?? $w->food?->name ?? 'অজ্ঞাত খাদ্য' }}</h4>
                                <p class="text-[10px] text-[var(--fc-text-muted)]">
                                    পরিমাণ: {{ $w->quantity }} {{ $w->food?->unit ?? 'টি' }} • কারণ: {{ $w->reason }}
                                </p>
                            </div>
                            <div class="text-end">
                                <span class="text-xs font-black text-red-500">-৳{{ number_format($w->estimated_cost, 0) }}</span>
                                <span class="text-[9px] text-[var(--fc-text-muted)] block">{{ $w->created_at->format('h:i A') }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="p-8 text-center rounded-2xl bg-[var(--fc-card)] border border-[var(--fc-border)]">
                    <p class="text-xs text-[var(--fc-text-muted)]">আজকে কোনো খাবার নষ্ট হওয়ার রেকর্ড নেই। চমৎকার কাজের ফল!</p>
                </div>
            @endif
        </div>

        <!-- =================================================================== -->
        <!-- TAB: CART SHIFT CLOSE (কার্ট ক্লোজ ও ডেলি সামারি) -->
        <!-- =================================================================== -->
        <div x-show="activeTab === 'shift_close'" class="space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-2 border-b border-[var(--fc-border)] gap-2">
                <div>
                    <h2 class="text-base font-black text-[var(--fc-text)]">🔒 কার্ট শিফট ক্লোজ ও ডেলি সামারি</h2>
                    <p class="text-xs text-[var(--fc-text-muted)]">দোকান বন্ধ করার সময় স্বয়ংক্রিয় হিসাব, কার্ট ভাড়া ও কার্ট বয়ের নীট ইনকাম</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-3 py-1.5 rounded-xl font-black text-xs {{ $isCartOpen ? 'bg-emerald-500/15 text-emerald-500' : 'bg-red-500/15 text-red-500' }}">
                        স্ট্যাটাস: {{ $isCartOpen ? 'খোলা (Open)' : 'বন্ধ (Closed)' }}
                    </span>
                </div>
            </div>

            <!-- Rental Model Settlement Banner -->
            <div class="p-4 rounded-2xl bg-gradient-to-br from-amber-500/15 via-[var(--fc-card)] to-transparent border border-amber-500/30 space-y-3">
                <div class="flex items-center gap-2">
                    <span class="text-lg">🤝</span>
                    <h3 class="text-xs font-black text-[var(--fc-text)]">ওনার ও কার্ট বয় রেন্টাল সেটেলমেন্ট (Daily Settlement)</h3>
                </div>

                <div :class="deviceView === 'mobile' ? 'grid grid-cols-2 gap-2 text-center text-xs' : 'grid grid-cols-2 sm:grid-cols-4 gap-2.5 text-center text-xs'">
                    <div class="p-3 rounded-xl bg-[var(--fc-bg)] border border-[var(--fc-border)]">
                        <span class="text-[10px] text-[var(--fc-text-muted)] block">সারাদিনের মোট বিক্রি</span>
                        <span class="text-sm font-black text-emerald-600 dark:text-emerald-400">৳{{ number_format($closingPreview['completed_sales'] ?? $closingPreview['total_sales'] ?? 0, 0) }}</span>
                    </div>
                    <div class="p-3 rounded-xl bg-[var(--fc-bg)] border border-[var(--fc-border)]">
                        <span class="text-[10px] text-[var(--fc-text-muted)] block">খরচ ও ওয়েস্টেজ বাদ</span>
                        <span class="text-sm font-black text-amber-500">৳{{ number_format(($closingPreview['cogs'] ?? 0) + ($closingPreview['total_expenses'] ?? $closingPreview['expenses'] ?? 0) + ($closingPreview['total_waste'] ?? $closingPreview['waste'] ?? 0), 0) }}</span>
                    </div>
                    <div class="p-3 rounded-xl bg-[var(--fc-bg)] border border-[var(--fc-border)]">
                        <span class="text-[10px] text-[var(--fc-text-muted)] block">ওনারের কার্ট ভাড়া</span>
                        <span class="text-sm font-black text-blue-500">৳{{ number_format($closingPreview['cart_rent'] ?? 0, 0) }}</span>
                    </div>
                    <div class="p-3 rounded-xl bg-emerald-500/20 border border-emerald-500/40">
                        <span class="text-[10px] text-emerald-600 dark:text-emerald-400 block font-bold">কার্ট বয়ের নীট ইনকাম</span>
                        <span class="text-sm font-black text-emerald-500">৳{{ number_format($closingPreview['cart_boy_net'] ?? $closingPreview['cart_boy_net_income'] ?? 0, 0) }}</span>
                    </div>
                </div>
            </div>

            <!-- Daily Closing Submission Form -->
            <div class="p-4 rounded-2xl bg-[var(--fc-card)] border border-[var(--fc-border)] space-y-3">
                <h3 class="text-xs font-black text-[var(--fc-text)]">অফিশিয়াল শিফট ক্লোজিং সম্পন্ন করুন:</h3>

                <form action="{{ route('closing.close') }}" method="POST" class="space-y-3">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-semibold text-[var(--fc-text-muted)] mb-1">ড্রয়ারে থাকা মোট ক্যাশ টাকা *</label>
                            <input
                                type="number"
                                step="0.01"
                                name="closing_cash"
                                value="{{ $closingPreview['expected_cash'] }}"
                                required
                                class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-bg)] text-xs text-[var(--fc-text)] outline-none focus:border-amber-500"
                            />
                            <p class="text-[10px] text-[var(--fc-text-muted)] mt-0.5">সিস্টেমের হিসেবে ক্যাশ থাকার কথা: ৳{{ number_format($closingPreview['expected_cash'], 0) }}</p>
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-[var(--fc-text-muted)] mb-1">ক্লোজিং মন্তব্য বা নোট (ঐচ্ছিক)</label>
                            <input
                                type="text"
                                name="closing_notes"
                                placeholder="যেমন: কোনো ত্রুটি নেই, সারাদিনের কাজ সুষ্ঠুভাবে শেষ"
                                class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-bg)] text-xs text-[var(--fc-text)] outline-none focus:border-amber-500"
                            />
                        </div>
                    </div>

                    <div class="flex items-center gap-2 pt-2">
                        <button
                            type="submit"
                            onclick="return confirm('আপনি কি নিশ্চিত যে আজকের কার্ট শিফট ক্লোজ করতে চান?')"
                            class="px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs shadow-md transition-all active:scale-98"
                        >
                            🔒 আজকের কার্ট শিফট ক্লোজ ও লক করুন
                        </button>

                        <a
                            href="{{ route('closing.index') }}"
                            class="px-4 py-2.5 rounded-xl bg-[var(--fc-bg)] hover:bg-[var(--fc-card)] border border-[var(--fc-border)] text-[var(--fc-text)] font-bold text-xs transition-colors"
                        >
                            📋 বিস্তারিত ক্লোজিং হিস্ট্রি দেখুন &rarr;
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- =================================================================== -->
        <!-- Fixed Mobile Bottom Navigation Bar for Cart Boy (Android Viewport)  -->
        <!-- =================================================================== -->
        <div class="fixed bottom-0 left-0 right-0 z-40 bg-[var(--fc-card)]/95 backdrop-blur-md border-t border-[var(--fc-border)] sm:hidden px-1.5 py-1 flex items-center justify-around shadow-2xl">
            <!-- Tab: POS -->
            <button
                type="button"
                @click="activeTab = 'pos'"
                :class="activeTab === 'pos' ? 'text-[var(--fc-primary)] font-black scale-105' : 'text-[var(--fc-text-muted)]'"
                class="flex flex-col items-center gap-0.5 text-[9px] transition-all py-1 flex-1"
            >
                <span class="text-base">⚡</span>
                <span>কাউন্টার POS</span>
            </button>

            <!-- Tab: Kitchen -->
            <button
                type="button"
                @click="activeTab = 'kitchen'"
                :class="activeTab === 'kitchen' ? 'text-[var(--fc-primary)] font-black scale-105' : 'text-[var(--fc-text-muted)]'"
                class="flex flex-col items-center gap-0.5 text-[9px] transition-all py-1 flex-1 relative"
            >
                <div class="relative">
                    <span class="text-base">🍳</span>
                    <span
                        x-show="liveOrdersList.length > 0"
                        x-text="liveOrdersList.length"
                        class="absolute -top-1 -right-2 size-3.5 rounded-full bg-amber-500 text-slate-950 font-black text-[8px] flex items-center justify-center animate-pulse"
                    ></span>
                </div>
                <span>কিচেন</span>
            </button>

            <!-- Tab: Sales Timeline & Item Breakdown -->
            <button
                type="button"
                @click="activeTab = 'timeline'"
                :class="activeTab === 'timeline' ? 'text-[var(--fc-primary)] font-black scale-105' : 'text-[var(--fc-text-muted)]'"
                class="flex flex-col items-center gap-0.5 text-[9px] transition-all py-1 flex-1"
            >
                <span class="text-base">📊</span>
                <span>বিক্রি ও আইটেম</span>
            </button>

            <!-- Tab: Parcel -->
            <button
                type="button"
                @click="activeTab = 'parcel'"
                :class="activeTab === 'parcel' ? 'text-[var(--fc-primary)] font-black scale-105' : 'text-[var(--fc-text-muted)]'"
                class="flex flex-col items-center gap-0.5 text-[9px] transition-all py-1 flex-1 relative"
            >
                <div class="relative">
                    <span class="text-base">🛍️</span>
                    <span
                        x-show="summaryData.parcel_orders > 0"
                        x-text="summaryData.parcel_orders"
                        class="absolute -top-1 -right-2 size-3.5 rounded-full bg-blue-500 text-white font-black text-[8px] flex items-center justify-center"
                    ></span>
                </div>
                <span>পার্সেল</span>
            </button>

            <!-- Tab: Shift Close -->
            <button
                type="button"
                @click="activeTab = 'shift_close'"
                :class="activeTab === 'shift_close' ? 'text-amber-500 font-black scale-105' : 'text-[var(--fc-text-muted)]'"
                class="flex flex-col items-center gap-0.5 text-[9px] transition-all py-1 flex-1"
            >
                <span class="text-base">🔒</span>
                <span>শিফট ক্লোজ</span>
            </button>
        </div>

        <!-- =================================================================== -->
        <!-- Counter QR Code Modal for Table/Cart Ordering                       -->
        <!-- =================================================================== -->
        <div
            x-show="qrModalOpen"
            x-transition
            class="fixed inset-0 z-50 bg-black/80 backdrop-blur-xs flex items-center justify-center p-4"
            x-cloak
        >
            <div
                @click.outside="qrModalOpen = false"
                class="w-full max-w-sm rounded-3xl bg-[var(--fc-card)] border border-[var(--fc-border)] p-5 text-center shadow-2xl space-y-3"
            >
                <div class="flex justify-between items-center pb-2 border-b border-[var(--fc-border)]">
                    <h3 class="text-sm font-black text-[var(--fc-text)] flex items-center gap-1.5">
                        <span>📱</span>
                        <span>কাউন্টার ও টেবিল কিউআর কোড</span>
                    </h3>
                    <button @click="qrModalOpen = false" class="size-7 rounded-full bg-[var(--fc-bg)] text-[var(--fc-text-muted)] hover:text-[var(--fc-text)] text-xs font-bold">&times;</button>
                </div>

                <p class="text-xs text-[var(--fc-text-muted)]">কাস্টমারকে মোবাইলে স্ক্যান করতে বলুন। স্ক্যান করলেই সরাসরি ডিজিটাল মেনু ওপেন হবে।</p>

                <div class="bg-white p-4 rounded-2xl inline-block shadow-inner">
                    <img
                        :src="'https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=' + encodeURIComponent('{{ route('home') }}')"
                        alt="Counter QR Code"
                        class="size-44 mx-auto"
                    />
                </div>

                <div class="text-xs text-[var(--fc-text)] font-bold">
                    {{ \App\Models\Setting::get('cart_name', 'ফুড কার্ট ডিজিটাল মেনু') }}
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <button
                        type="button"
                        @click="navigator.clipboard.writeText('{{ route('home') }}'); toast.message = 'মেনু লিংক কপি হয়েছে!'; toast.show = true; setTimeout(() => { toast.show = false; }, 3000);"
                        class="flex-1 py-2 rounded-xl bg-[var(--fc-bg)] hover:bg-[var(--fc-card)] border border-[var(--fc-border)] text-xs font-bold text-[var(--fc-text)] transition-colors"
                    >
                        📋 লিংক কপি
                    </button>
                    <button
                        type="button"
                        onclick="window.print()"
                        class="flex-1 py-2 rounded-xl bg-[var(--fc-primary)] text-[var(--fc-primary-text)] text-xs font-black transition-colors shadow-xs"
                    >
                        🖨️ প্রিন্ট কিউআর
                    </button>
                </div>
            </div>
        </div>

    </div>

    <!-- Alpine.js CartBoy Controller Script -->
    <script>
        function cartBoyWorkspace() {
            return {
                activeTab: 'pos',
                kitchenFilter: 'all',
                qrModalOpen: false,
                summaryData: {
                    completed_sales: {{ (float) ($summary['completed_sales'] ?? 0) }},
                    total_orders: {{ (int) ($summary['total_orders'] ?? 0) }},
                    cash: {{ (float) ($summary['payment_breakdown']['cash'] ?? 0) }},
                    digital: {{ (float) (($summary['payment_breakdown']['bkash'] ?? 0) + ($summary['payment_breakdown']['nagad'] ?? 0) + ($summary['payment_breakdown']['rocket'] ?? 0) + ($summary['payment_breakdown']['card'] ?? 0)) }},
                    parcel_orders: {{ (int) ($summary['parcel_orders'] ?? 0) }},
                    parcel_sales: {{ (float) ($summary['parcel_sales'] ?? 0) }},
                    total_waste: {{ (float) ($summary['total_waste'] ?? 0) }},
                    net_profit: {{ (float) ($summary['net_profit'] ?? 0) }}
                },
                searchQuery: '',
                selectedCategory: 'all',
                cart: [],
                mobileCartOpen: false,
                orderType: 'counter',
                paymentMethod: 'cash',
                targetStatus: 'preparing', // default: send to kitchen
                customerPhone: '',
                cashReceived: null,
                isSubmitting: false,
                pollingInterval: null,
                lastSeenOrderId: {{ $latestOrderId ?? 0 }},
                audioEnabled: localStorage.getItem('fc_cartboy_audio') !== 'false',
                audioCtx: null,
                newOrderAlert: {
                    show: false,
                    message: '',
                    orderId: null,
                    orderNumber: '',
                    customerName: '',
                    orderTypeBadge: '',
                    totalAmount: 0,
                    itemsSummary: ''
                },
                toast: { show: false, message: '' },
                foodsList: @json($foods),
                liveOrdersList: [
                    @foreach($liveOrders as $o)
                        {
                            id: {{ $o->id }},
                            order_number: '{{ $o->order_number }}',
                            customer_name: '{{ addslashes($o->customer?->name ?? 'গেস্ট কাস্টমার') }}',
                            created_time: '{{ $o->created_at->format('h:i A') }}',
                            time_diff: '{{ $o->created_at->diffForHumans() }}',
                            order_status: '{{ $o->order_status }}',
                            order_type: '{{ $o->order_type }}',
                            table_no: '{{ $o->table_no ?? '' }}',
                            payment_method: '{{ $o->payment_method }}',
                            payment_status: '{{ $o->payment_status }}',
                            status_bn: '{{ $o->order_status === 'pending' ? 'অপেক্ষারত' : ($o->order_status === 'preparing' ? 'রান্না হচ্ছে' : 'রেডি') }}',
                            total_amount: '{{ number_format($o->total_amount, 0) }}',
                            notes: '{{ addslashes($o->notes ?? '') }}',
                            items: [
                                @foreach($o->items as $item)
                                    { id: {{ $item->id }}, food_name: '{{ addslashes($item->food_name) }}', quantity: {{ $item->quantity }} },
                                @endforeach
                            ]
                        },
                    @endforeach
                ],
                allTodayOrdersList: [
                    @foreach($todayOrders as $to)
                        {
                            id: {{ $to->id }},
                            order_number: '{{ $to->order_number }}',
                            customer_name: '{{ addslashes($to->customer?->name ?? 'গেস্ট কাস্টমার') }}',
                            customer_phone: '{{ $to->customer?->phone ?? '' }}',
                            created_time: '{{ $to->created_at->format('h:i A') }}',
                            order_status: '{{ $to->order_status }}',
                            order_type: '{{ $to->order_type }}',
                            table_no: '{{ $to->table_no ?? '' }}',
                            status_bn: '{{ $to->order_status === 'pending' ? 'পেন্ডিং' : ($to->order_status === 'preparing' ? 'রান্না হচ্ছে' : ($to->order_status === 'ready' ? 'রেডি' : 'ডেলিভারি সম্পন্ন')) }}',
                            total_amount: '{{ number_format($to->total_amount, 0) }}',
                            payment_method: '{{ $to->payment_method }}',
                            items: [
                                @foreach($to->items as $titem)
                                    { id: {{ $titem->id }}, food_name: '{{ addslashes($titem->food_name) }}', quantity: {{ $titem->quantity }} },
                                @endforeach
                            ]
                        },
                    @endforeach
                ],

                init() {
                    // Set latest seen order ID
                    if (this.lastSeenOrderId === 0 && this.liveOrdersList.length > 0) {
                        this.lastSeenOrderId = Math.max(...this.liveOrdersList.map(o => o.id));
                    }

                    // Unlock audio on first user touch / click
                    const unlockAudio = () => {
                        this.getAudioContext();
                        if ("Notification" in window && Notification.permission === "default") {
                            Notification.requestPermission();
                        }
                        document.removeEventListener('click', unlockAudio);
                        document.removeEventListener('touchstart', unlockAudio);
                    };
                    document.addEventListener('click', unlockAudio, { once: true });
                    document.addEventListener('touchstart', unlockAudio, { once: true });

                    // Background polling every 2.5 seconds for instant real-time notifications
                    this.pollLiveOrders();
                    this.pollingInterval = setInterval(() => {
                        this.pollLiveOrders();
                    }, 2500);
                },

                getAudioContext() {
                    if (!this.audioCtx) {
                        const AudioCtx = window.AudioContext || window.webkitAudioContext;
                        if (AudioCtx) {
                            this.audioCtx = new AudioCtx();
                        }
                    }
                    if (this.audioCtx && this.audioCtx.state === 'suspended') {
                        this.audioCtx.resume();
                    }
                    return this.audioCtx;
                },

                toggleAudioAlert() {
                    this.audioEnabled = !this.audioEnabled;
                    localStorage.setItem('fc_cartboy_audio', this.audioEnabled ? 'true' : 'false');
                    if (this.audioEnabled) {
                        this.playAlertSound();
                        if ("Notification" in window && Notification.permission === "default") {
                            Notification.requestPermission();
                        }
                        this.toast.message = '🔊 অর্ডার নোটিফিকেশন সাউন্ড চালু করা হয়েছে!';
                    } else {
                        this.toast.message = '🔇 সাউন্ড বন্ধ করা হয়েছে।';
                    }
                    this.toast.show = true;
                    setTimeout(() => { this.toast.show = false; }, 2500);
                },

                // High-priority audio chime, mobile vibration, and browser notification
                playAlertSound() {
                    try {
                        const ctx = this.getAudioContext();
                        if (ctx) {
                            const playTone = (freq, start, dur, volume = 0.5) => {
                                const osc = ctx.createOscillator();
                                const gain = ctx.createGain();
                                osc.type = 'triangle';
                                osc.frequency.setValueAtTime(freq, ctx.currentTime + start);
                                gain.gain.setValueAtTime(volume, ctx.currentTime + start);
                                gain.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + start + dur);
                                osc.connect(gain);
                                gain.connect(ctx.destination);
                                osc.start(ctx.currentTime + start);
                                osc.stop(ctx.currentTime + start + dur);
                            };
                            // Pleasant attention-grabbing Ding-Dong chime
                            playTone(523.25, 0, 0.25, 0.5);    // C5
                            playTone(659.25, 0.15, 0.25, 0.5); // E5
                            playTone(783.99, 0.30, 0.35, 0.6); // G5
                            playTone(1046.50, 0.45, 0.6, 0.7); // C6
                        }
                    } catch(e) {
                        console.log('Audio alert err:', e);
                    }

                    // Vibrate mobile device
                    if (navigator.vibrate) {
                        try {
                            navigator.vibrate([300, 100, 300, 100, 400]);
                        } catch(e) {}
                    }

                    // Native Browser System Notification (Works when tab is minimized)
                    if ("Notification" in window && Notification.permission === "granted") {
                        try {
                            new Notification("🔔 নতুন খাবার অর্ডার এসেছে!", {
                                body: `${this.newOrderAlert.orderNumber} • ${this.newOrderAlert.customerName} (${this.newOrderAlert.orderTypeBadge}) • ৳${this.newOrderAlert.totalAmount}`,
                                icon: '{{ asset("images/foodcart-logo.svg") }}'
                            });
                        } catch(e) {}
                    }
                },

                async pollLiveOrders() {
                    try {
                        const res = await fetch('{{ route("cartboy.live-orders") }}');
                        const data = await res.json();
                        if (data.success) {
                            if (data.latest_order_id > this.lastSeenOrderId && this.lastSeenOrderId > 0) {
                                // A new order arrived!
                                const latest = data.latest_order;
                                if (latest) {
                                    this.newOrderAlert.orderId = latest.id;
                                    this.newOrderAlert.orderNumber = '#' + latest.order_number;
                                    this.newOrderAlert.customerName = latest.customer_name;
                                    this.newOrderAlert.orderTypeBadge = latest.table_no ? ('🪑 টেবিল ' + latest.table_no) : (latest.order_type === 'parcel' ? '🛍️ পার্সেল' : '🍽️ ডাইন-ইন');
                                    this.newOrderAlert.totalAmount = latest.total_amount;
                                    this.newOrderAlert.itemsSummary = latest.items_summary;
                                    this.newOrderAlert.message = `নতুন অর্ডার #${latest.order_number} এসেছে! (${this.newOrderAlert.orderTypeBadge})`;
                                } else {
                                    this.newOrderAlert.message = `নতুন অনলাইন অর্ডার এসেছে! (মোট ${data.pending_count}টি পেন্ডিং)`;
                                }
                                this.newOrderAlert.show = true;

                                if (this.audioEnabled) {
                                    this.playAlertSound();
                                }
                            }
                            this.lastSeenOrderId = Math.max(this.lastSeenOrderId, data.latest_order_id);
                            this.liveOrdersList = data.live_orders;
                            if (data.summary) {
                                this.summaryData = data.summary;
                            }
                        }
                    } catch (e) {
                        // Silent retry
                    }
                },

                get filteredFoods() {
                    return this.foodsList.filter(f => {
                        const matchCat = this.selectedCategory === 'all' || f.category_id === this.selectedCategory;
                        const matchSearch = !this.searchQuery.trim() ||
                            f.name.toLowerCase().includes(this.searchQuery.toLowerCase().trim()) ||
                            (f.bengali_name && f.bengali_name.includes(this.searchQuery.trim()));
                        return matchCat && matchSearch;
                    });
                },

                get filteredKitchenOrders() {
                    if (this.kitchenFilter === 'all') return this.liveOrdersList;
                    return this.liveOrdersList.filter(o => o.order_status === this.kitchenFilter);
                },

                addToCart(food) {
                    const existing = this.cart.find(i => i.id === food.id);
                    if (existing) {
                        existing.qty += 1;
                    } else {
                        this.cart.push({
                            id: food.id,
                            name: food.bengali_name || food.name,
                            price: Number(food.selling_price),
                            qty: 1
                        });
                    }
                },

                decreaseQty(foodId) {
                    const idx = this.cart.findIndex(i => i.id === foodId);
                    if (idx !== -1) {
                        if (this.cart[idx].qty > 1) {
                            this.cart[idx].qty -= 1;
                        } else {
                            this.cart.splice(idx, 1);
                        }
                    }
                },

                clearCart() {
                    this.cart = [];
                    this.cashReceived = null;
                },

                cartTotalPrice() {
                    return this.cart.reduce((s, i) => s + (i.price * i.qty), 0);
                },

                async submitPosOrder() {
                    if (this.cart.length === 0) return;
                    this.isSubmitting = true;

                    const payload = {
                        customer_phone: this.customerPhone,
                        payment_method: this.paymentMethod,
                        order_status: this.targetStatus, // 'preparing' or 'completed'
                        notes: `কাউন্টার স্টাফ অর্ডার [${this.orderType}]`,
                        items: this.cart.map(i => ({ food_id: i.id, quantity: i.qty }))
                    };

                    try {
                        const res = await fetch('{{ route("pos.checkout") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify(payload)
                        });

                        const data = await res.json();
                        if (data.success) {
                            this.cart = [];
                            this.customerPhone = '';
                            this.cashReceived = null;
                            this.mobileCartOpen = false;
                            this.toast.message = `✅ অর্ডার #${data.order.order_number} সফলভাবে সম্পন্ন হয়েছে!`;
                            this.toast.show = true;

                            // Refresh live orders right away
                            await this.pollLiveOrders();

                            setTimeout(() => {
                                this.toast.show = false;
                            }, 3000);
                        } else {
                            alert(data.message || 'অর্ডার করতে সমস্যা হয়েছে');
                        }
                    } catch (e) {
                        alert('সার্ভারে সমস্যা হয়েছে। আবার চেষ্টা করুন।');
                    } finally {
                        this.isSubmitting = false;
                    }
                },

                async updateStatus(orderId, newStatus) {
                    try {
                        const res = await fetch(`/cartboy/orders/${orderId}/status`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ order_status: newStatus })
                        });

                        const data = await res.json();
                        if (data.success) {
                            if (newStatus === 'completed') {
                                this.liveOrdersList = this.liveOrdersList.filter(o => o.id !== orderId);
                            } else {
                                const o = this.liveOrdersList.find(o => o.id === orderId);
                                if (o) {
                                    o.order_status = newStatus;
                                    o.status_bn = newStatus === 'preparing' ? 'রান্না হচ্ছে' : 'রেডি';
                                }
                            }

                            // Update in allTodayOrdersList
                            const to = this.allTodayOrdersList.find(o => o.id === orderId);
                            if (to) {
                                to.order_status = newStatus;
                                to.status_bn = newStatus === 'preparing' ? 'রান্না হচ্ছে' : (newStatus === 'ready' ? 'রেডি' : 'ডেলিভারি সম্পন্ন');
                            }

                            this.toast.message = data.message;
                            this.toast.show = true;
                            setTimeout(() => { this.toast.show = false; }, 3000);
                        } else {
                            alert('স্ট্যাটাস আপডেট ব্যর্থ হয়েছে');
                        }
                    } catch (e) {
                        alert('সার্ভার যোগাযোগ ত্রুটি');
                    }
                },

                async acknowledgeAndCook(orderId) {
                    if (!orderId) {
                        this.newOrderAlert.show = false;
                        this.activeTab = 'kitchen';
                        return;
                    }
                    this.newOrderAlert.show = false;
                    this.activeTab = 'kitchen';
                    await this.updateStatus(orderId, 'preparing');
                }
            };
        }
    </script>
</x-layouts::app>
