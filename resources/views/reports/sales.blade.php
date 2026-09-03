<x-layouts::app title="Sales Reports">
    <div class="space-y-6">
        <!-- Top Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-[var(--fc-border)]">
            <div>
                <h1 class="text-2xl font-black text-[var(--fc-text)]">Sales Intelligence Reports</h1>
                <p class="text-xs text-[var(--fc-text-muted)] mt-0.5">Filter by date ranges, examine revenue trends, and export to CSV</p>
            </div>

            <!-- Export Actions -->
            <div class="flex items-center gap-2">
                <a href="{{ route('reports.export', ['type' => 'sales', 'range' => request('range', 'this_month')]) }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs font-bold text-[var(--fc-text)] hover:bg-[var(--fc-bg)] shadow-xs">
                    <flux:icon name="arrow-down-tray" class="size-4 text-emerald-500" />
                    <span>Export CSV</span>
                </a>

                <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-[var(--fc-primary)] text-[var(--fc-primary-text)] text-xs font-bold shadow-xs">
                    <flux:icon name="printer" class="size-4" />
                    <span>Print Report</span>
                </button>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="flex items-center gap-2 border-b border-[var(--fc-border)] pb-2 overflow-x-auto text-xs font-bold">
            <a href="{{ route('reports.sales') }}" class="px-3 py-1.5 rounded-lg bg-[var(--fc-primary)] text-[var(--fc-primary-text)]">
                Sales Report
            </a>
            <a href="{{ route('reports.expenses') }}" class="px-3 py-1.5 rounded-lg text-[var(--fc-text-muted)] hover:text-[var(--fc-text)] hover:bg-[var(--fc-card)]">
                Expenses Report
            </a>
            <a href="{{ route('reports.waste') }}" class="px-3 py-1.5 rounded-lg text-[var(--fc-text-muted)] hover:text-[var(--fc-text)] hover:bg-[var(--fc-card)]">
                Food Waste Report
            </a>
            <a href="{{ route('reports.inventory') }}" class="px-3 py-1.5 rounded-lg text-[var(--fc-text-muted)] hover:text-[var(--fc-text)] hover:bg-[var(--fc-card)]">
                Inventory Valuation
            </a>
        </div>

        <!-- Date Range Filter -->
        <div class="fc-card p-4 shadow-xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <form method="GET" action="{{ route('reports.sales') }}" class="flex items-center gap-2">
                <select name="range" onchange="this.form.submit()" class="px-3 py-1.5 rounded-lg border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs text-[var(--fc-text)] font-semibold outline-none">
                    <option value="today" {{ request('range') === 'today' ? 'selected' : '' }}>Today</option>
                    <option value="yesterday" {{ request('range') === 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                    <option value="last_7_days" {{ request('range') === 'last_7_days' ? 'selected' : '' }}>Last 7 Days</option>
                    <option value="this_month" {{ request('range', 'this_month') === 'this_month' ? 'selected' : '' }}>This Month</option>
                    <option value="this_year" {{ request('range') === 'this_year' ? 'selected' : '' }}>This Year</option>
                </select>
            </form>

            <span class="text-xs font-semibold text-[var(--fc-text-muted)]">
                Period: <strong>{{ $dates['label'] }}</strong> ({{ $dates['start']->format('d M Y') }} - {{ $dates['end']->format('d M Y') }})
            </span>
        </div>

        <!-- Sales KPI Summary -->
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
            <div class="fc-card p-4 shadow-xs">
                <span class="text-xs font-semibold text-[var(--fc-text-muted)]">Completed Orders</span>
                <p class="text-2xl font-black text-[var(--fc-text)] mt-1">{{ $summary['total_orders'] }}</p>
            </div>

            <div class="fc-card p-4 shadow-xs">
                <span class="text-xs font-semibold text-[var(--fc-text-muted)]">Gross Revenue</span>
                <p class="text-2xl font-black text-[var(--fc-text)] mt-1">৳{{ number_format($summary['gross_sales'], 2) }}</p>
            </div>

            <div class="fc-card p-4 shadow-xs">
                <span class="text-xs font-semibold text-[var(--fc-text-muted)]">Total Discounts</span>
                <p class="text-2xl font-black text-amber-500 mt-1">৳{{ number_format($summary['total_discount'], 2) }}</p>
            </div>

            <div class="fc-card p-4 shadow-xs border-emerald-500/30 bg-emerald-500/5">
                <span class="text-xs font-bold text-emerald-800 dark:text-emerald-200">Net Sales Revenue</span>
                <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1">৳{{ number_format($summary['completed_sales'], 2) }}</p>
            </div>
        </div>

        <!-- Orders Table -->
        <div class="fc-card overflow-hidden shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-start">
                    <thead class="bg-[var(--fc-bg)] border-b border-[var(--fc-border)] text-[var(--fc-text-muted)] uppercase tracking-wider font-semibold">
                        <tr>
                            <th class="py-3 px-4 text-start">Order #</th>
                            <th class="py-3 px-4 text-start">Date & Time</th>
                            <th class="py-3 px-4 text-start">Customer</th>
                            <th class="py-3 px-4 text-start">Channel</th>
                            <th class="py-3 px-4 text-end">Subtotal (৳)</th>
                            <th class="py-3 px-4 text-end">Discount (৳)</th>
                            <th class="py-3 px-4 text-end">Net Total (৳)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--fc-border)]">
                        @forelse($orders as $order)
                            <tr class="hover:bg-[var(--fc-bg)]/40 transition-colors">
                                <td class="py-3 px-4 font-bold text-[var(--fc-primary)]">
                                    <a href="{{ route('orders.show', $order) }}">{{ $order->order_number }}</a>
                                </td>
                                <td class="py-3 px-4 text-[var(--fc-text-muted)]">
                                    {{ $order->created_at->format('d M Y, h:i A') }}
                                </td>
                                <td class="py-3 px-4 font-medium text-[var(--fc-text)]">
                                    {{ $order->customer?->name ?? 'Guest' }}
                                </td>
                                <td class="py-3 px-4 uppercase font-bold text-[10px]">
                                    {{ $order->payment_method }}
                                </td>
                                <td class="py-3 px-4 text-end font-semibold text-[var(--fc-text)]">
                                    ৳{{ number_format($order->subtotal, 2) }}
                                </td>
                                <td class="py-3 px-4 text-end text-emerald-600">
                                    - ৳{{ number_format($order->discount_amount, 2) }}
                                </td>
                                <td class="py-3 px-4 text-end font-black text-emerald-600">
                                    ৳{{ number_format($order->total_amount, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-xs text-[var(--fc-text-muted)]">No sales found for this period.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-[var(--fc-border)]">
                {{ $orders->links() }}
            </div>
        </div>
    </div>
</x-layouts::app>
