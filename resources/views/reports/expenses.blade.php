<x-layouts::app title="Expenses Reports">
    <div class="space-y-6">
        <!-- Top Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-[var(--fc-border)]">
            <div>
                <h1 class="text-2xl font-black text-[var(--fc-text)]">Expense Audit Reports</h1>
                <p class="text-xs text-[var(--fc-text-muted)] mt-0.5">Categorized breakdown of kitchen overhead and vendor payouts</p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('reports.export', ['type' => 'expenses', 'range' => request('range', 'this_month')]) }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs font-bold text-[var(--fc-text)] hover:bg-[var(--fc-bg)] shadow-xs">
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
            <a href="{{ route('reports.sales') }}" class="px-3 py-1.5 rounded-lg text-[var(--fc-text-muted)] hover:text-[var(--fc-text)] hover:bg-[var(--fc-card)]">
                Sales Report
            </a>
            <a href="{{ route('reports.expenses') }}" class="px-3 py-1.5 rounded-lg bg-[var(--fc-primary)] text-[var(--fc-primary-text)]">
                Expenses Report
            </a>
            <a href="{{ route('reports.waste') }}" class="px-3 py-1.5 rounded-lg text-[var(--fc-text-muted)] hover:text-[var(--fc-text)] hover:bg-[var(--fc-card)]">
                Food Waste Report
            </a>
            <a href="{{ route('reports.inventory') }}" class="px-3 py-1.5 rounded-lg text-[var(--fc-text-muted)] hover:text-[var(--fc-text)] hover:bg-[var(--fc-card)]">
                Inventory Valuation
            </a>
        </div>

        <!-- Filter -->
        <div class="fc-card p-4 shadow-xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <form method="GET" action="{{ route('reports.expenses') }}" class="flex items-center gap-2">
                <select name="range" onchange="this.form.submit()" class="px-3 py-1.5 rounded-lg border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs text-[var(--fc-text)] font-semibold outline-none">
                    <option value="today" {{ request('range') === 'today' ? 'selected' : '' }}>Today</option>
                    <option value="yesterday" {{ request('range') === 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                    <option value="last_7_days" {{ request('range') === 'last_7_days' ? 'selected' : '' }}>Last 7 Days</option>
                    <option value="this_month" {{ request('range', 'this_month') === 'this_month' ? 'selected' : '' }}>This Month</option>
                    <option value="this_year" {{ request('range') === 'this_year' ? 'selected' : '' }}>This Year</option>
                </select>
            </form>

            <span class="text-xs font-bold text-[var(--fc-text)]">
                Total Period Expense: <span class="text-red-500 font-black text-sm">৳{{ number_format($totalExpenses, 2) }}</span>
            </span>
        </div>

        <!-- Table -->
        <div class="fc-card overflow-hidden shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-start">
                    <thead class="bg-[var(--fc-bg)] border-b border-[var(--fc-border)] text-[var(--fc-text-muted)] uppercase tracking-wider font-semibold">
                        <tr>
                            <th class="py-3 px-4 text-start">Date</th>
                            <th class="py-3 px-4 text-start">Category</th>
                            <th class="py-3 px-4 text-start">Description</th>
                            <th class="py-3 px-4 text-start">Method</th>
                            <th class="py-3 px-4 text-start">Reference #</th>
                            <th class="py-3 px-4 text-end">Amount (৳)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--fc-border)]">
                        @forelse($expenses as $expense)
                            <tr class="hover:bg-[var(--fc-bg)]/40 transition-colors">
                                <td class="py-3 px-4 text-[var(--fc-text-muted)]">{{ $expense->date->format('d M Y') }}</td>
                                <td class="py-3 px-4 font-bold text-[var(--fc-text)]">{{ $expense->category?->name ?? 'General' }}</td>
                                <td class="py-3 px-4 text-[var(--fc-text)]">{{ $expense->description }}</td>
                                <td class="py-3 px-4 uppercase font-bold text-[10px]">{{ $expense->payment_method }}</td>
                                <td class="py-3 px-4 text-[var(--fc-text-muted)]">{{ $expense->reference ?? '-' }}</td>
                                <td class="py-3 px-4 text-end font-black text-red-500">৳{{ number_format($expense->amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-xs text-[var(--fc-text-muted)]">No expenses recorded in this timeframe.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-[var(--fc-border)]">
                {{ $expenses->links() }}
            </div>
        </div>
    </div>
</x-layouts::app>
