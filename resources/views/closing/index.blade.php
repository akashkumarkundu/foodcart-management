<x-layouts::app title="Daily Business Closing">
    <div class="space-y-6" x-data="{ cashCount: '{{ $preview['payment_breakdown']['cash'] ?? 0 }}', expectedCash: {{ $preview['payment_breakdown']['cash'] ?? 0 }} }">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-[var(--fc-border)]">
            <div>
                <h1 class="text-2xl font-black text-[var(--fc-text)]">Daily Business Closing (EOD)</h1>
                <p class="text-xs text-[var(--fc-text-muted)] mt-0.5">End-of-day reconciliation, cash drawer counting, and closing audit freeze</p>
            </div>

            <!-- Date Selector -->
            <form method="GET" action="{{ route('closing.index') }}" class="flex items-center gap-2">
                <input
                    type="date"
                    name="date"
                    value="{{ $selectedDate->toDateString() }}"
                    onchange="this.form.submit()"
                    class="px-3 py-1.5 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs text-[var(--fc-text)] focus:border-[var(--fc-primary)] outline-none font-bold"
                />
            </form>
        </div>

        <!-- Closing Status Banner -->
        @if($preview['is_already_closed'])
            <div class="p-4 rounded-xl border border-emerald-500/30 bg-emerald-500/10 text-emerald-800 dark:text-emerald-200 flex items-center justify-between text-xs shadow-xs">
                <div class="flex items-center gap-2.5">
                    <flux:icon name="lock-closed" class="size-5 text-emerald-600 shrink-0" />
                    <div>
                        <p class="font-bold">Business Day is Officially Closed for {{ $selectedDate->format('d M Y') }}</p>
                        <p class="text-[11px] opacity-80">Closed at {{ $preview['existing_report']->closed_at?->format('h:i A') }} by {{ $preview['existing_report']->closer?->name ?? 'Owner' }}.</p>
                    </div>
                </div>

                @if(auth()->user()->isOwner())
                    <form method="POST" action="{{ route('closing.reopen', $preview['existing_report']) }}" onsubmit="return confirm('Reopen this closed day for editing?');">
                        @csrf
                        <button type="submit" class="px-3 py-1 rounded-lg border border-emerald-600/40 bg-white dark:bg-zinc-800 font-bold text-[11px] text-emerald-700 dark:text-emerald-300 hover:bg-emerald-50">
                            Reopen Day
                        </button>
                    </form>
                @endif
            </div>
        @else
            <div class="p-4 rounded-xl border border-amber-500/30 bg-amber-500/10 text-amber-800 dark:text-amber-200 flex items-center gap-2.5 text-xs shadow-xs">
                <flux:icon name="clock" class="size-5 text-amber-600 shrink-0" />
                <div>
                    <p class="font-bold">Day is currently OPEN for {{ $selectedDate->format('d M Y') }}</p>
                    <p class="text-[11px] opacity-80">Review your final sales, tally physical cash in drawer, and click 'Close Day' to freeze the books.</p>
                </div>
            </div>
        @endif

        <!-- Summary Grid -->
        <div :class="deviceView === 'mobile' ? 'space-y-4' : 'grid grid-cols-1 lg:grid-cols-12 gap-6'">
            <!-- Left: Financial Breakdown (7 Cols on desktop, full width on mobile) -->
            <div :class="deviceView === 'mobile' ? 'w-full' : 'lg:col-span-7'" class="fc-card p-4 sm:p-6 shadow-xs space-y-4">
                <div class="border-b border-[var(--fc-border)] pb-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
                    <h2 class="text-base font-black text-[var(--fc-text)]">
                        Closing Figures for {{ $selectedDate->format('d F Y') }}
                    </h2>
                    <span class="text-xs font-semibold text-[var(--fc-text-muted)] shrink-0">
                        {{ $preview['total_orders'] }} Orders • {{ $preview['total_customers'] }} Customers
                    </span>
                </div>

                <div class="space-y-3 text-xs">
                    <!-- Sales Total -->
                    <div class="flex justify-between items-center py-2.5 border-b border-[var(--fc-border)] gap-2">
                        <span class="font-bold text-[var(--fc-text)] text-sm truncate">Total Completed Sales</span>
                        <span class="font-black text-base text-emerald-600 dark:text-emerald-400 shrink-0">
                            ৳{{ number_format($preview['completed_sales'], 2) }}
                        </span>
                    </div>

                    <!-- Payment Methods Breakdown -->
                    <div class="pl-3 space-y-2 border-l-2 border-emerald-500/40 text-[var(--fc-text-muted)] text-xs">
                        <div class="flex justify-between items-center gap-2">
                            <span class="truncate">Cash in Drawer:</span>
                            <span class="font-bold text-[var(--fc-text)] shrink-0">৳{{ number_format($preview['payment_breakdown']['cash'], 2) }}</span>
                        </div>
                        <div class="flex justify-between items-center gap-2">
                            <span class="truncate">bKash Payments:</span>
                            <span class="font-bold text-pink-600 shrink-0">৳{{ number_format($preview['payment_breakdown']['bkash'], 2) }}</span>
                        </div>
                        <div class="flex justify-between items-center gap-2">
                            <span class="truncate">Nagad Payments:</span>
                            <span class="font-bold text-orange-600 shrink-0">৳{{ number_format($preview['payment_breakdown']['nagad'], 2) }}</span>
                        </div>
                        <div class="flex justify-between items-center gap-2">
                            <span class="truncate">Rocket Payments:</span>
                            <span class="font-bold text-purple-600 shrink-0">৳{{ number_format($preview['payment_breakdown']['rocket'], 2) }}</span>
                        </div>
                        <div class="flex justify-between items-center gap-2">
                            <span class="truncate">Card Payments:</span>
                            <span class="font-bold text-blue-600 shrink-0">৳{{ number_format($preview['payment_breakdown']['card'], 2) }}</span>
                        </div>
                    </div>

                    <!-- Parcel vs Dine-In Summary -->
                    <div class="pt-2 border-t border-[var(--fc-border)] space-y-1.5">
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-[var(--fc-text-muted)] flex items-center gap-1">
                                <span>🛍️</span> <span>Parcel / Takeaway Orders:</span>
                            </span>
                            <span class="font-bold text-blue-600 dark:text-blue-400">
                                {{ $preview['parcel_orders'] ?? 0 }}টি (৳{{ number_format($preview['parcel_sales'] ?? 0, 2) }})
                            </span>
                        </div>
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-[var(--fc-text-muted)] flex items-center gap-1">
                                <span>🪑</span> <span>Dine-In Orders:</span>
                            </span>
                            <span class="font-bold text-[var(--fc-text)]">
                                {{ $preview['dine_in_orders'] ?? 0 }}টি (৳{{ number_format($preview['dine_in_sales'] ?? 0, 2) }})
                            </span>
                        </div>
                    </div>

                    <!-- Outflow Deductions -->
                    <div class="pt-2 border-t border-[var(--fc-border)] space-y-2">
                        <div class="flex justify-between items-center gap-2">
                            <span class="text-[var(--fc-text-muted)] truncate">Raw Material Cost (COGS):</span>
                            <span class="font-bold text-amber-600 shrink-0">- ৳{{ number_format($preview['cogs'], 2) }}</span>
                        </div>
                        <div class="flex justify-between items-center gap-2">
                            <span class="text-[var(--fc-text-muted)] truncate">Total Operating Expenses:</span>
                            <span class="font-bold text-amber-600 shrink-0">- ৳{{ number_format($preview['total_expenses'], 2) }}</span>
                        </div>
                        <div class="flex justify-between items-center gap-2">
                            <span class="text-[var(--fc-text-muted)] truncate">Total Food Waste Loss:</span>
                            <span class="font-bold text-red-500 shrink-0">- ৳{{ number_format($preview['total_waste'], 2) }}</span>
                        </div>
                    </div>

                    <!-- Net Profit & Cart Rent Settlement -->
                    <div class="pt-3 border-t-2 border-[var(--fc-border)] space-y-2">
                        <div class="flex justify-between items-center gap-2">
                            <div>
                                <span class="font-black text-sm text-[var(--fc-text)]">Calculated Net Business Profit</span>
                                <p class="text-[10px] text-[var(--fc-text-muted)]">Sales - COGS - Expenses - Waste</p>
                            </div>
                            <div class="text-end shrink-0">
                                <span class="text-xl font-black text-emerald-600 dark:text-emerald-400">
                                    ৳{{ number_format($preview['net_profit'], 2) }}
                                </span>
                                <p class="text-[11px] font-bold text-emerald-600">{{ $preview['profit_margin'] }}% Profit Margin</p>
                            </div>
                        </div>

                        <!-- Owner Rent vs Cart Boy Split -->
                        <div class="p-3 rounded-xl bg-amber-500/10 border border-amber-500/30 space-y-1.5 text-xs">
                            <div class="flex justify-between items-center font-bold">
                                <span class="text-amber-700 dark:text-amber-300">👑 Owner Cart Daily Rent:</span>
                                <span class="text-amber-600">৳{{ number_format($preview['cart_rent'], 2) }}</span>
                            </div>
                            <div class="flex justify-between items-center font-bold">
                                <span class="text-emerald-700 dark:text-emerald-300">🧑‍🍳 Cart Boy Net Take-Home:</span>
                                <span class="text-emerald-600 dark:text-emerald-400">৳{{ number_format($preview['cart_boy_net'], 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Cash Count Reconciliation & Close Action (5 Cols on desktop, full width on mobile) -->
            <div :class="deviceView === 'mobile' ? 'w-full' : 'lg:col-span-5'" class="fc-card p-4 sm:p-6 shadow-xs space-y-4">
                <h2 class="text-base font-black text-[var(--fc-text)] border-b border-[var(--fc-border)] pb-3">
                    Cash Drawer Audit
                </h2>

                @if(!$preview['is_already_closed'])
                    <form method="POST" action="{{ route('closing.close') }}" class="space-y-4">
                        @csrf
                        <input type="hidden" name="date" value="{{ $selectedDate->toDateString() }}" />

                        <div>
                            <label class="block text-xs font-semibold text-[var(--fc-text-muted)] mb-1">
                                Physical Cash Count in Cash Drawer (৳)
                            </label>
                            <input
                                type="number"
                                step="0.5"
                                name="physical_cash"
                                x-model="cashCount"
                                placeholder="Count actual cash in drawer"
                                class="w-full px-3 py-2.5 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-base font-black text-[var(--fc-text)] focus:border-[var(--fc-primary)] outline-none"
                            />
                            <div class="flex items-center justify-between text-xs mt-2 font-semibold gap-2">
                                <span class="text-[var(--fc-text-muted)] truncate">Expected Cash:</span>
                                <span class="font-bold shrink-0">৳<span x-text="expectedCash.toFixed(2)"></span></span>
                            </div>
                            <div class="flex items-center justify-between text-xs mt-1.5 font-bold gap-2">
                                <span class="text-[var(--fc-text-muted)] truncate">Drawer Variance:</span>
                                <span
                                    class="shrink-0"
                                    :class="(parseFloat(cashCount || 0) - expectedCash) >= 0 ? 'text-emerald-600' : 'text-red-500'"
                                    x-text="'৳' + (parseFloat(cashCount || 0) - expectedCash).toFixed(2)"
                                ></span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[var(--fc-text-muted)] mb-1">Closing Notes / Shift Comments</label>
                            <textarea name="notes" rows="3" placeholder="e.g. All kitchen appliances turned off, gas regulator locked..." class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs text-[var(--fc-text)] focus:border-[var(--fc-primary)] outline-none"></textarea>
                        </div>

                        <button
                            type="submit"
                            onclick="return confirm('Freeze and close today\'s business? This will generate an official closing report.');"
                            class="w-full py-3 rounded-xl bg-emerald-600 text-white font-black text-sm shadow-md hover:bg-emerald-700 transition-all flex items-center justify-center gap-2"
                        >
                            <flux:icon name="lock-closed" class="size-5 shrink-0" />
                            <span>Close Today's Business</span>
                        </button>
                    </form>
                @else
                    <div class="space-y-3 text-xs">
                        <div class="p-4 rounded-xl bg-[var(--fc-bg)] border border-[var(--fc-border)]">
                            <h4 class="font-bold text-[var(--fc-text)] mb-2">Audit Notes Recorded at Closing:</h4>
                            <p class="text-[var(--fc-text-muted)] italic leading-relaxed">
                                {{ $preview['existing_report']->notes ?: 'No audit notes provided.' }}
                            </p>
                        </div>

                        <p class="text-[11px] text-[var(--fc-text-muted)] text-center">
                            Records for this day are immutable to ensure integrity.
                        </p>
                    </div>
                @endif
            </div>
        </div>

        <!-- 2-Column Section: Item-Wise Sales Breakdown & Timeline -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Left: Item-Wise Sales Breakdown -->
            <div class="lg:col-span-6 fc-card p-4 sm:p-6 shadow-xs space-y-3">
                <div class="flex items-center justify-between border-b border-[var(--fc-border)] pb-3">
                    <h3 class="font-black text-sm text-[var(--fc-text)]">📦 Item-Wise Sales Breakdown</h3>
                    <span class="text-xs text-[var(--fc-text-muted)]">Qty & Revenue</span>
                </div>

                @if(!empty($preview['item_wise_sales']))
                    <div class="space-y-2">
                        @foreach($preview['item_wise_sales'] as $item)
                            <div class="p-2.5 rounded-xl bg-[var(--fc-bg)] border border-[var(--fc-border)] flex items-center justify-between text-xs">
                                <div>
                                    <span class="font-bold text-[var(--fc-text)]">{{ data_get($item, 'food_name') }}</span>
                                    <span class="text-[11px] text-[var(--fc-text-muted)] block">Qty Sold: {{ data_get($item, 'quantity') ?? data_get($item, 'total_quantity') }}</span>
                                </div>
                                <div class="text-end">
                                    <span class="font-black text-emerald-600 dark:text-emerald-400 block">৳{{ number_format((float) (data_get($item, 'revenue') ?? data_get($item, 'total_revenue')), 2) }}</span>
                                    <span class="text-[10px] text-emerald-500">Profit: ৳{{ number_format((float) (data_get($item, 'profit') ?? data_get($item, 'total_profit')), 2) }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs text-[var(--fc-text-muted)] text-center py-6">No item sales recorded for this date.</p>
                @endif
            </div>

            <!-- Right: Chronological Sales Timeline -->
            <div class="lg:col-span-6 fc-card p-4 sm:p-6 shadow-xs space-y-3">
                <div class="flex items-center justify-between border-b border-[var(--fc-border)] pb-3">
                    <h3 class="font-black text-sm text-[var(--fc-text)]">⏱️ Chronological Sales Timeline</h3>
                    <span class="text-xs text-[var(--fc-text-muted)]">Exact Time & Order</span>
                </div>

                @if(!empty($preview['sales_timeline']))
                    <div class="space-y-2 max-h-96 overflow-y-auto pr-1">
                        @foreach($preview['sales_timeline'] as $sale)
                            <div class="p-2.5 rounded-xl bg-[var(--fc-bg)] border border-[var(--fc-border)] flex items-center justify-between text-xs gap-2">
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-1 rounded bg-[var(--fc-card)] font-bold text-[10px] text-[var(--fc-primary)] border border-[var(--fc-border)]">
                                        {{ Carbon\Carbon::parse(data_get($sale, 'time') ?? data_get($sale, 'created_at'))->format('h:i A') }}
                                    </span>
                                    <div>
                                        <span class="font-bold text-[var(--fc-text)]">{{ data_get($sale, 'food_name') }}</span>
                                        <span class="text-[10px] text-[var(--fc-text-muted)] block">
                                            {{ data_get($sale, 'quantity') }} × ৳{{ number_format((float) data_get($sale, 'unit_price'), 0) }} •
                                            <span class="font-semibold">{{ data_get($sale, 'order_type_bn', 'বসে খাওয়া') }}</span> •
                                            <span class="uppercase">{{ data_get($sale, 'payment_method') }}</span>
                                        </span>
                                    </div>
                                </div>
                                <div class="text-end shrink-0">
                                    <span class="font-black text-emerald-600 dark:text-emerald-400 block">+৳{{ number_format((float) data_get($sale, 'subtotal'), 0) }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs text-[var(--fc-text-muted)] text-center py-6">No sales timeline available for this date.</p>
                @endif
            </div>
        </div>

        <!-- Past Closing Reports History Table -->
        <div class="fc-card overflow-hidden shadow-xs">
            <div class="p-4 border-b border-[var(--fc-border)]">
                <h2 class="font-bold text-sm text-[var(--fc-text)]">Daily Closing Archive</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-start">
                    <thead class="bg-[var(--fc-bg)] border-b border-[var(--fc-border)] text-[var(--fc-text-muted)] uppercase tracking-wider font-semibold">
                        <tr>
                            <th class="py-3 px-4 text-start">Date</th>
                            <th class="py-3 px-4 text-center">Orders</th>
                            <th class="py-3 px-4 text-end">Total Sales (৳)</th>
                            <th class="py-3 px-4 text-end">Expenses (৳)</th>
                            <th class="py-3 px-4 text-end">Waste (৳)</th>
                            <th class="py-3 px-4 text-end">Net Profit (৳)</th>
                            <th class="py-3 px-4 text-center">Margin</th>
                            <th class="py-3 px-4 text-start">Closed By</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--fc-border)]">
                        @forelse($pastReports as $report)
                            <tr class="hover:bg-[var(--fc-bg)]/40 transition-colors">
                                <td class="py-3 px-4 font-bold text-[var(--fc-text)]">
                                    {{ $report->report_date->format('d M Y') }}
                                </td>
                                <td class="py-3 px-4 text-center">{{ $report->total_orders }}</td>
                                <td class="py-3 px-4 text-end font-bold text-[var(--fc-text)]">
                                    ৳{{ number_format($report->total_sales, 2) }}
                                </td>
                                <td class="py-3 px-4 text-end text-amber-600">
                                    ৳{{ number_format($report->total_expenses, 2) }}
                                </td>
                                <td class="py-3 px-4 text-end text-red-500">
                                    ৳{{ number_format($report->total_waste, 2) }}
                                </td>
                                <td class="py-3 px-4 text-end font-black text-emerald-600">
                                    ৳{{ number_format($report->net_profit, 2) }}
                                </td>
                                <td class="py-3 px-4 text-center font-semibold text-emerald-600">
                                    {{ $report->profit_margin }}%
                                </td>
                                <td class="py-3 px-4 text-[var(--fc-text-muted)]">
                                    {{ $report->closer?->name ?? 'System' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-8 text-center text-xs text-[var(--fc-text-muted)]">No past closing reports yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-[var(--fc-border)]">
                {{ $pastReports->links() }}
            </div>
        </div>
    </div>
</x-layouts::app>
