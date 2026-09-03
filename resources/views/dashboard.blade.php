<x-layouts::app title="Owner Dashboard">
    <div x-data="ownerDashboardWorkspace()" class="space-y-4 sm:space-y-5">

        <!-- Top Header & 1-Click Role Switcher -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 border-b border-[var(--fc-border)]">
            <div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30">
                        <span class="size-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        লাইভ ফুড কার্ট
                    </span>
                    <span class="text-xs text-[var(--fc-text-muted)]">{{ now()->translatedFormat('l, d F Y') }}</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-black text-[var(--fc-text)] mt-0.5">
                    মালিক ড্যাশবোর্ড (ওনার প্যানেল)
                </h1>
            </div>

            <!-- Role Switcher & Direct Links -->
            <div class="flex items-center gap-2">
                <a href="{{ route('home') }}" target="_blank" class="px-3 py-1.5 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs font-bold text-emerald-500 hover:bg-[var(--fc-bg)] transition-colors flex items-center gap-1.5 shadow-xs">
                    <span>🌐 কাস্টমার মেনু</span>
                    <span class="text-[10px]">&nearr;</span>
                </a>

                <a href="{{ route('cartboy.index') }}" class="px-3.5 py-1.5 rounded-xl bg-amber-500 text-slate-950 text-xs font-black shadow-sm hover:bg-amber-400 transition-all flex items-center gap-1.5">
                    <span>🧑‍🍳 কার্টবয় কাউন্টার (POS)</span>
                </a>
            </div>
        </div>

        <!-- HERO CARD: TODAY'S FINANCIAL SUMMARY (Street-Food Friendly Bengali) -->
        <div class="fc-card p-4 sm:p-5 rounded-3xl shadow-sm border border-[var(--fc-border)] space-y-4 bg-gradient-to-b from-[var(--fc-card)] to-[var(--fc-bg)]">
            <div class="flex items-center justify-between pb-3 border-b border-[var(--fc-border)]/70">
                <div class="flex items-center gap-2">
                    <span class="text-lg">💰</span>
                    <div>
                        <h2 class="text-sm sm:text-base font-black text-[var(--fc-text)]">আজকের সার্বিক হিসাব (Today's Summary)</h2>
                        <p class="text-[11px] text-[var(--fc-text-muted)]">মোট বিক্রি থেকে খরচ ও অপচয় বাদ দিয়ে নিট লাভ</p>
                    </div>
                </div>
                <span class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 px-2.5 py-1 rounded-full border border-emerald-500/20">
                    {{ $todayMetrics['total_orders'] }}টি অর্ডার সম্পন্ন
                </span>
            </div>

            <!-- 4 Key Numbers Grid -->
            <div :class="deviceView === 'mobile' ? 'grid grid-cols-2 gap-2.5' : 'grid grid-cols-2 lg:grid-cols-4 gap-3'">
                <!-- 1. Total Sales / Income -->
                <div class="p-3.5 sm:p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 flex flex-col justify-between">
                    <span class="text-xs font-bold text-emerald-700 dark:text-emerald-300">💵 মোট বিক্রি / ইনকাম</span>
                    <div class="mt-2">
                        <div class="text-xl sm:text-2xl font-black text-emerald-600 dark:text-emerald-400 leading-tight">
                            ৳{{ number_format($todayMetrics['completed_sales'], 0) }}
                        </div>
                        <p class="text-[11px] text-[var(--fc-text-muted)] mt-0.5">{{ $todayMetrics['total_orders'] }}টি কাস্টমার অর্ডার</p>
                    </div>
                </div>

                <!-- 2. Food Waste Cost -->
                <div class="p-3.5 sm:p-4 rounded-2xl bg-red-500/10 border border-red-500/30 flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-red-700 dark:text-red-300">🗑️ নষ্ট খাবার (Waste)</span>
                        <button type="button" @click="wasteModalOpen = true" class="text-[11px] font-bold text-red-500 hover:underline">+ এন্ট্রি</button>
                    </div>
                    <div class="mt-2">
                        <div class="text-xl sm:text-2xl font-black text-red-500 leading-tight">
                            ৳{{ number_format($todayMetrics['total_waste'], 0) }}
                        </div>
                        <p class="text-[11px] text-[var(--fc-text-muted)] mt-0.5">রান্নাঘরের অপচয়</p>
                    </div>
                </div>

                <!-- 3. Expenses -->
                <div class="p-3.5 sm:p-4 rounded-2xl bg-amber-500/10 border border-amber-500/30 flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-amber-700 dark:text-amber-300">💳 আজকের খরচ</span>
                        <button type="button" @click="expenseModalOpen = true" class="text-[11px] font-bold text-amber-500 hover:underline">+ খরচ</button>
                    </div>
                    <div class="mt-2">
                        <div class="text-xl sm:text-2xl font-black text-amber-500 leading-tight">
                            ৳{{ number_format($todayMetrics['total_expenses'], 0) }}
                        </div>
                        <p class="text-[11px] text-[var(--fc-text-muted)] mt-0.5">গ্যাস, বক্সেস, দোকান খরচ</p>
                    </div>
                </div>

                <!-- 4. Net Profit -->
                <div class="p-3.5 sm:p-4 rounded-2xl bg-sky-500/10 border border-sky-500/30 flex flex-col justify-between">
                    <span class="text-xs font-bold text-sky-700 dark:text-sky-300">📈 খাঁটি নিট লাভ (Profit)</span>
                    <div class="mt-2">
                        <div class="text-xl sm:text-2xl font-black text-sky-600 dark:text-sky-400 leading-tight">
                            ৳{{ number_format($todayMetrics['net_profit'], 0) }}
                        </div>
                        <p class="text-[11px] font-bold text-emerald-500 mt-0.5">{{ $todayMetrics['profit_margin'] }}% নিট প্রফিট মার্জিন</p>
                    </div>
                </div>
            </div>

            <!-- Payment Breakdown Chips: Cash vs Digital (bKash/Nagad) -->
            <div :class="deviceView === 'mobile' ? 'grid grid-cols-2 gap-2.5 text-xs' : 'grid grid-cols-2 lg:grid-cols-4 gap-2.5 text-xs'" class="pt-2.5 border-t border-[var(--fc-border)]/70">
                <div class="p-3 rounded-2xl bg-[var(--fc-bg)] border border-[var(--fc-border)] flex flex-col justify-between gap-1 shadow-2xs">
                    <div class="flex items-center gap-1.5 text-[11px] text-[var(--fc-text-muted)] font-semibold truncate">
                        <span class="text-sm shrink-0">💵</span>
                        <span class="truncate">নগদ ক্যাশ</span>
                    </div>
                    <strong class="font-black text-sm sm:text-base text-emerald-600 dark:text-emerald-400 mt-0.5">৳{{ number_format($todayMetrics['payment_breakdown']['cash'], 0) }}</strong>
                </div>

                <div class="p-3 rounded-2xl bg-[var(--fc-bg)] border border-[var(--fc-border)] flex flex-col justify-between gap-1 shadow-2xs">
                    <div class="flex items-center gap-1.5 text-[11px] text-[var(--fc-text-muted)] font-semibold truncate">
                        <span class="text-sm shrink-0">📱</span>
                        <span class="truncate">বিকাশ (bKash)</span>
                    </div>
                    <strong class="font-black text-sm sm:text-base text-pink-600 dark:text-pink-400 mt-0.5">৳{{ number_format($todayMetrics['payment_breakdown']['bkash'], 0) }}</strong>
                </div>

                <div class="p-3 rounded-2xl bg-[var(--fc-bg)] border border-[var(--fc-border)] flex flex-col justify-between gap-1 shadow-2xs">
                    <div class="flex items-center gap-1.5 text-[11px] text-[var(--fc-text-muted)] font-semibold truncate">
                        <span class="text-sm shrink-0">⚡</span>
                        <span class="truncate">নগদ (Nagad)</span>
                    </div>
                    <strong class="font-black text-sm sm:text-base text-orange-600 dark:text-orange-400 mt-0.5">৳{{ number_format($todayMetrics['payment_breakdown']['nagad'], 0) }}</strong>
                </div>

                <div class="p-3 rounded-2xl bg-[var(--fc-bg)] border border-[var(--fc-border)] flex flex-col justify-between gap-1 shadow-2xs">
                    <div class="flex items-center gap-1.5 text-[11px] text-[var(--fc-text-muted)] font-semibold truncate">
                        <span class="text-sm shrink-0">💳</span>
                        <span class="truncate">রকেট / কার্ড</span>
                    </div>
                    <strong class="font-black text-sm sm:text-base text-purple-600 dark:text-purple-400 mt-0.5">৳{{ number_format($todayMetrics['payment_breakdown']['rocket'] + $todayMetrics['payment_breakdown']['card'], 0) }}</strong>
                </div>
            </div>

            <!-- 4 Quick Action Speed Buttons (Opens Modals directly on Dashboard) -->
            <div :class="deviceView === 'mobile' ? 'grid grid-cols-2 gap-2.5' : 'grid grid-cols-2 lg:grid-cols-4 gap-2.5'" class="pt-2">
                <button
                    type="button"
                    @click="priceModalOpen = true"
                    class="py-3 px-2.5 rounded-2xl bg-[var(--fc-card)] hover:bg-amber-500/10 border border-amber-500/40 text-amber-500 font-bold text-xs flex items-center justify-center gap-2 shadow-2xs transition-all active:scale-95 text-center"
                >
                    <span class="text-base shrink-0">💰</span>
                    <span class="truncate font-bold text-xs">খাবারের দাম</span>
                </button>

                <button
                    type="button"
                    @click="wasteModalOpen = true"
                    class="py-3 px-2.5 rounded-2xl bg-[var(--fc-card)] hover:bg-red-500/10 border border-red-500/40 text-red-500 font-bold text-xs flex items-center justify-center gap-2 shadow-2xs transition-all active:scale-95 text-center"
                >
                    <span class="text-base shrink-0">🗑️</span>
                    <span class="truncate font-bold text-xs">নষ্ট খাবার এন্ট্রি</span>
                </button>

                <button
                    type="button"
                    @click="expenseModalOpen = true"
                    class="py-3 px-2.5 rounded-2xl bg-[var(--fc-card)] hover:bg-blue-500/10 border border-blue-500/40 text-blue-500 font-bold text-xs flex items-center justify-center gap-2 shadow-2xs transition-all active:scale-95 text-center"
                >
                    <span class="text-base shrink-0">💸</span>
                    <span class="truncate font-bold text-xs">খরচ এন্ট্রি</span>
                </button>

                <a
                    href="{{ route('closing.index') }}"
                    class="py-3 px-2.5 rounded-2xl bg-[var(--fc-card)] hover:bg-sky-500/10 border border-sky-500/40 text-sky-500 font-bold text-xs flex items-center justify-center gap-2 shadow-2xs transition-all active:scale-95 text-center"
                >
                    <span class="text-base shrink-0">🔒</span>
                    <span class="truncate font-bold text-xs">হিসাব ক্লোজ</span>
                </a>
            </div>
        </div>

        <!-- 2-COLUMN SECTION: RECENT ORDERS (Left) + WASTE & ALERTS (Right) -->
        <div :class="deviceView === 'mobile' ? 'space-y-4' : 'grid grid-cols-1 lg:grid-cols-12 gap-4 items-start'">

            <!-- Left: Today's Orders (7 Cols on desktop, full width on mobile) -->
            <div :class="deviceView === 'mobile' ? 'fc-card p-3 sm:p-4 rounded-3xl shadow-xs border border-[var(--fc-border)] space-y-3' : 'lg:col-span-7 fc-card p-4 sm:p-5 rounded-3xl shadow-xs border border-[var(--fc-border)] space-y-3'">
                <div class="flex items-center justify-between pb-2.5 border-b border-[var(--fc-border)]">
                    <div class="flex items-center gap-2">
                        <span class="text-base">📋</span>
                        <h3 class="font-black text-sm text-[var(--fc-text)]">আজকের সাম্প্রতিক অর্ডারসমূহ</h3>
                    </div>
                    <a href="{{ route('orders.index') }}" class="text-xs font-bold text-[var(--fc-primary)] hover:underline">
                        সব দেখুন &rarr;
                    </a>
                </div>

                <div class="space-y-2 max-h-96 overflow-y-auto pr-1">
                    @forelse($recentOrders as $order)
                        <div class="p-3 rounded-2xl bg-[var(--fc-bg)] border border-[var(--fc-border)] flex items-center justify-between gap-2 text-xs">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('orders.show', $order) }}" class="font-black text-[var(--fc-primary)] hover:underline">
                                        #{{ $order->order_number }}
                                    </a>
                                    <span class="text-[10px] text-[var(--fc-text-muted)]">{{ $order->created_at->format('h:i A') }}</span>
                                    <span class="px-2 py-0.5 rounded-full font-bold text-[9px] uppercase {{ $order->order_status === 'completed' ? 'bg-emerald-500/15 text-emerald-600' : 'bg-amber-500/15 text-amber-600' }}">
                                        {{ $order->order_status === 'completed' ? 'সম্পন্ন' : ($order->order_status === 'ready' ? 'রেডি' : 'রান্না হচ্ছে') }}
                                    </span>
                                </div>
                                <p class="text-[11px] text-[var(--fc-text-muted)]">
                                    {{ $order->customer?->name ?? 'গেস্ট' }} • {{ $order->items->count() }}টি আইটেম •
                                    <span class="uppercase font-bold text-[10px]">{{ $order->payment_method }}</span>
                                </p>
                            </div>

                            <div class="text-end shrink-0">
                                <span class="font-black text-sm text-[var(--fc-text)]">৳{{ number_format($order->total_amount, 0) }}</span>
                                <a href="{{ route('orders.invoice', $order) }}" target="_blank" class="block text-[10px] text-[var(--fc-primary)] font-bold hover:underline mt-0.5">
                                    রসিদ প্রিন্ট
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center text-xs text-[var(--fc-text-muted)]">
                            <p>আজকে এখনও কোনো অর্ডার সম্পন্ন হয়নি।</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Right: Food Waste & Stock Status (5 Cols on desktop, full width on mobile) -->
            <div :class="deviceView === 'mobile' ? 'space-y-4' : 'lg:col-span-5 space-y-4'">
                <!-- Food Waste Today -->
                <div class="fc-card p-4 rounded-3xl shadow-xs border border-[var(--fc-border)] space-y-3">
                    <div class="flex items-center justify-between pb-2 border-b border-[var(--fc-border)]">
                        <div class="flex items-center gap-2">
                            <span class="text-base">🗑️</span>
                            <h3 class="font-black text-sm text-[var(--fc-text)]">নষ্ট খাবারের হিসাব</h3>
                        </div>
                        <button type="button" @click="wasteModalOpen = true" class="text-xs font-bold text-red-500 hover:underline">
                            + নতুন এন্ট্রি
                        </button>
                    </div>

                    <div class="space-y-2">
                        @forelse($recentWastes as $w)
                            <div class="p-2.5 rounded-xl bg-[var(--fc-bg)] border border-[var(--fc-border)] flex items-center justify-between text-xs">
                                <div>
                                    <p class="font-bold text-[var(--fc-text)]">{{ $w->food?->bengali_name ?? $w->food?->name ?? 'খাবার' }}</p>
                                    <p class="text-[10px] text-[var(--fc-text-muted)]">{{ $w->quantity }} {{ $w->unit }} • <span class="text-amber-500">{{ $w->reason }}</span></p>
                                </div>
                                <span class="font-black text-sm text-red-500">৳{{ number_format($w->estimated_cost, 0) }}</span>
                            </div>
                        @empty
                            <div class="py-4 text-center text-xs text-[var(--fc-text-muted)]">
                                <p>✅ আজকে কোনো খাবার নষ্টের রেকর্ড নেই।</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Low Stock Alert -->
                <div class="fc-card p-4 rounded-3xl shadow-xs border border-[var(--fc-border)] space-y-3">
                    <div class="flex items-center justify-between pb-2 border-b border-[var(--fc-border)]">
                        <div class="flex items-center gap-2">
                            <span class="text-base">⚠️</span>
                            <h3 class="font-black text-sm text-[var(--fc-text)]">স্টক শেষ হওয়ার সতর্কতা</h3>
                        </div>
                        <a href="{{ route('inventory.index') }}" class="text-xs font-bold text-amber-500 hover:underline">
                            স্টক &rarr;
                        </a>
                    </div>

                    <div class="space-y-2">
                        @forelse($lowStockFoods as $food)
                            <div class="p-2.5 rounded-xl border border-amber-500/20 bg-amber-500/5 flex items-center justify-between text-xs">
                                <div>
                                    <p class="font-bold text-[var(--fc-text)]">{{ $food->bengali_name ?? $food->name }}</p>
                                    <p class="text-[10px] text-[var(--fc-text-muted)]">{{ $food->name }}</p>
                                </div>
                                <span class="px-2 py-0.5 rounded font-black text-amber-500 bg-amber-500/10 text-xs">
                                    {{ $food->current_stock }} {{ $food->unit }} বাকি
                                </span>
                            </div>
                        @empty
                            <div class="py-4 text-center text-xs text-[var(--fc-text-muted)]">
                                <p>✅ সব খাবারের স্টক পর্যাপ্ত রয়েছে।</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>

        <!-- =================================================================== -->
        <!-- QUICK MODAL 1: QUICK EXPENSE ENTRY -->
        <!-- =================================================================== -->
        <div x-cloak x-show="expenseModalOpen" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-xs" @click="expenseModalOpen = false"></div>
            <div class="relative min-h-screen flex items-center justify-center p-4">
                <div class="relative w-full max-w-md rounded-3xl bg-[var(--fc-card)] border border-[var(--fc-border)] p-6 shadow-2xl text-[var(--fc-text)] space-y-4">
                    <div class="flex items-center justify-between pb-2 border-b border-[var(--fc-border)]">
                        <div class="flex items-center gap-2">
                            <span class="text-lg">💸</span>
                            <h3 class="text-base font-black">দ্রুত খরচ এন্ট্রি (Quick Expense)</h3>
                        </div>
                        <button type="button" @click="expenseModalOpen = false" class="text-xl font-bold text-[var(--fc-text-muted)] hover:text-[var(--fc-text)]">&times;</button>
                    </div>

                    <form method="POST" action="{{ route('expenses.store') }}" class="space-y-3 text-xs">
                        @csrf
                        <input type="hidden" name="date" value="{{ date('Y-m-d') }}">

                        <div>
                            <label class="block font-bold mb-1">খরচের ক্যাটাগরি *</label>
                            <select name="expense_category_id" required class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-bg)] text-xs text-[var(--fc-text)] outline-none">
                                @foreach($expenseCategories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block font-bold mb-1">টাকার পরিমাণ (৳) *</label>
                            <input type="number" step="0.01" name="amount" required placeholder="যেমন: 500" class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-bg)] text-xs text-[var(--fc-text)] outline-none" />
                        </div>

                        <div>
                            <label class="block font-bold mb-1">খরচের বিবরণ *</label>
                            <input type="text" name="description" required placeholder="যেমন: গ্যাস সিলিন্ডার, বরফ, মশলা, টিস্যু..." class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-bg)] text-xs text-[var(--fc-text)] outline-none" />
                        </div>

                        <div>
                            <label class="block font-bold mb-1">পেমেন্ট মাধ্যম</label>
                            <select name="payment_method" class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-bg)] text-xs text-[var(--fc-text)] outline-none">
                                <option value="cash">নগদ ক্যাশ</option>
                                <option value="bkash">বিকাশ</option>
                                <option value="nagad">নগদ</option>
                                <option value="bank">ব্যাংক</option>
                            </select>
                        </div>

                        <div class="pt-2 flex gap-2">
                            <button type="submit" class="flex-1 py-2.5 rounded-xl bg-blue-500 hover:bg-blue-400 text-white font-black text-xs transition-colors">
                                ✅ খরচ সংরক্ষণ করুন
                            </button>
                            <button type="button" @click="expenseModalOpen = false" class="px-4 py-2.5 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-bg)] text-xs font-bold">
                                বাতিল
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- =================================================================== -->
        <!-- QUICK MODAL 2: QUICK WASTE ENTRY -->
        <!-- =================================================================== -->
        <div x-cloak x-show="wasteModalOpen" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-xs" @click="wasteModalOpen = false"></div>
            <div class="relative min-h-screen flex items-center justify-center p-4">
                <div class="relative w-full max-w-md rounded-3xl bg-[var(--fc-card)] border border-[var(--fc-border)] p-6 shadow-2xl text-[var(--fc-text)] space-y-4">
                    <div class="flex items-center justify-between pb-2 border-b border-[var(--fc-border)]">
                        <div class="flex items-center gap-2">
                            <span class="text-lg">🗑️</span>
                            <h3 class="text-base font-black">নষ্ট খাবার এন্ট্রি (Log Waste)</h3>
                        </div>
                        <button type="button" @click="wasteModalOpen = false" class="text-xl font-bold text-[var(--fc-text-muted)] hover:text-[var(--fc-text)]">&times;</button>
                    </div>

                    <form method="POST" action="{{ route('wastes.store') }}" class="space-y-3 text-xs">
                        @csrf
                        <input type="hidden" name="date" value="{{ date('Y-m-d') }}">

                        <div>
                            <label class="block font-bold mb-1">নষ্ট হওয়া খাবার *</label>
                            <select name="food_id" required class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-bg)] text-xs text-[var(--fc-text)] outline-none">
                                @foreach($activeFoods as $food)
                                    <option value="{{ $food->id }}">{{ $food->bengali_name ?? $food->name }} ({{ $food->unit }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block font-bold mb-1">পরিমাণ (Quantity) *</label>
                            <input type="number" step="1" min="1" name="quantity" required placeholder="যেমন: 2" class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-bg)] text-xs text-[var(--fc-text)] outline-none" />
                        </div>

                        <div>
                            <label class="block font-bold mb-1">নষ্ট হওয়ার কারণ *</label>
                            <select name="reason" required class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-bg)] text-xs text-[var(--fc-text)] outline-none">
                                <option value="নষ্ট/পচে গেছে">নষ্ট / পচে গেছে (Spoiled)</option>
                                <option value="অতিরিক্ত রান্না">অতিরিক্ত রান্না (Overcooked/Burnt)</option>
                                <option value="মেয়াদোত্তীর্ণ">মেয়াদোত্তীর্ণ (Expired)</option>
                                <option value="ভেঙে/পড়ে গেছে">ভেঙে / পড়ে গেছে (Dropped/Spilled)</option>
                                <option value="অন্যান্য">অন্যান্য (Other)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-bold mb-1">নোট / মন্তব্য (ঐচ্ছিক)</label>
                            <input type="text" name="notes" placeholder="সংক্ষিপ্ত নোট..." class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-bg)] text-xs text-[var(--fc-text)] outline-none" />
                        </div>

                        <div class="pt-2 flex gap-2">
                            <button type="submit" class="flex-1 py-2.5 rounded-xl bg-red-500 hover:bg-red-400 text-white font-black text-xs transition-colors">
                                ✅ নষ্ট রেকর্ড করুন
                            </button>
                            <button type="button" @click="wasteModalOpen = false" class="px-4 py-2.5 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-bg)] text-xs font-bold">
                                বাতিল
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- =================================================================== -->
        <!-- QUICK MODAL 3: QUICK FOOD PRICE CUSTOMIZATION -->
        <!-- =================================================================== -->
        <div x-cloak x-show="priceModalOpen" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-xs" @click="priceModalOpen = false"></div>
            <div class="relative min-h-screen flex items-center justify-center p-4">
                <div class="relative w-full max-w-md rounded-3xl bg-[var(--fc-card)] border border-[var(--fc-border)] p-6 shadow-2xl text-[var(--fc-text)] space-y-4">
                    <div class="flex items-center justify-between pb-2 border-b border-[var(--fc-border)]">
                        <div class="flex items-center gap-2">
                            <span class="text-lg">💰</span>
                            <h3 class="text-base font-black">খাবারের দাম দ্রুত পরিবর্তন</h3>
                        </div>
                        <button type="button" @click="priceModalOpen = false" class="text-xl font-bold text-[var(--fc-text-muted)] hover:text-[var(--fc-text)]">&times;</button>
                    </div>

                    <div class="space-y-3 text-xs">
                        <div>
                            <label class="block font-bold mb-1">খাবার নির্বাচন করুন</label>
                            <select x-model="selectedFoodId" @change="onFoodSelect()" class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-bg)] text-xs text-[var(--fc-text)] outline-none">
                                <option value="">-- খাবার বেছে নিন --</option>
                                <template x-for="food in foodsList" :key="food.id">
                                    <option :value="food.id" x-text="(food.bengali_name || food.name) + ' (বর্তমান: ৳' + Number(food.selling_price).toFixed(0) + ')'"></option>
                                </template>
                            </select>
                        </div>

                        <template x-if="selectedFood">
                            <div class="space-y-3 pt-2">
                                <div class="p-3 rounded-2xl bg-[var(--fc-bg)] border border-[var(--fc-border)] space-y-1">
                                    <p class="font-bold text-sm" x-text="selectedFood.bengali_name || selectedFood.name"></p>
                                    <p class="text-[11px] text-[var(--fc-text-muted)]">ক্যাটাগরি: <span x-text="selectedFood.category?.name || 'খাবার'"></span> • একক: <span x-text="selectedFood.unit"></span></p>
                                </div>

                                <div>
                                    <label class="block font-bold mb-1">নতুন বিক্রয় মূল্য (Selling Price) *</label>
                                    <input type="number" step="1" x-model.number="newSellingPrice" class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-bg)] text-sm font-bold text-emerald-500 outline-none" />
                                </div>

                                <div>
                                    <label class="block font-bold mb-1">প্রস্তুত খরচ (Cost Price)</label>
                                    <input type="number" step="1" x-model.number="newCostPrice" class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-bg)] text-xs text-[var(--fc-text)] outline-none" />
                                </div>

                                <div class="pt-2 flex gap-2">
                                    <button
                                        type="button"
                                        @click="savePrice()"
                                        :disabled="isSavingPrice"
                                        class="flex-1 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 disabled:opacity-50 text-slate-950 font-black text-xs transition-colors"
                                    >
                                        <span x-show="!isSavingPrice">✅ নতুন দাম সংরক্ষণ করুন</span>
                                        <span x-show="isSavingPrice">সংরক্ষণ হচ্ছে...</span>
                                    </button>
                                    <button type="button" @click="priceModalOpen = false" class="px-4 py-2.5 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-bg)] text-xs font-bold">
                                        বাতিল
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script>
        function ownerDashboardWorkspace() {
            return {
                deviceView: localStorage.getItem('fc_device_view') || 'mobile',
                expenseModalOpen: false,
                wasteModalOpen: false,
                priceModalOpen: false,
                foodsList: @json($activeFoods),
                selectedFoodId: '',
                selectedFood: null,
                newSellingPrice: 0,
                newCostPrice: 0,
                isSavingPrice: false,

                init() {
                    const urlParams = new URLSearchParams(window.location.search);
                    if (urlParams.has('view')) {
                        this.deviceView = urlParams.get('view');
                    }
                    window.addEventListener('storage', (e) => {
                        if (e.key === 'fc_device_view') {
                            this.deviceView = e.newValue || 'mobile';
                        }
                    });
                    window.addEventListener('device-view-changed', (e) => {
                        this.deviceView = e.detail;
                    });
                },

                onFoodSelect() {
                    this.selectedFood = this.foodsList.find(f => f.id == this.selectedFoodId) || null;
                    if (this.selectedFood) {
                        this.newSellingPrice = Number(this.selectedFood.selling_price);
                        this.newCostPrice = Number(this.selectedFood.cost_price || 0);
                    }
                },

                async savePrice() {
                    if (!this.selectedFood || !this.newSellingPrice) return;
                    this.isSavingPrice = true;

                    try {
                        const res = await fetch(`/foods/${this.selectedFood.id}/price`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                selling_price: this.newSellingPrice,
                                cost_price: this.newCostPrice
                            })
                        });

                        const data = await res.json();
                        if (data.success) {
                            this.selectedFood.selling_price = this.newSellingPrice;
                            this.selectedFood.cost_price = this.newCostPrice;
                            alert('খাবারের নতুন দাম সফলভাবে আপডেট হয়েছে!');
                            this.priceModalOpen = false;
                            location.reload();
                        } else {
                            alert(data.message || 'দাম আপডেট ব্যর্থ হয়েছে');
                        }
                    } catch (e) {
                        alert('সার্ভার যোগাযোগ ত্রুটি');
                    } finally {
                        this.isSavingPrice = false;
                    }
                }
            };
        }
    </script>
</x-layouts::app>
