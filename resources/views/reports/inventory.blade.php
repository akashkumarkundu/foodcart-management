<x-layouts::app title="Inventory Valuation Report">
    <div class="space-y-6">
        <!-- Top Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-[var(--fc-border)]">
            <div>
                <h1 class="text-2xl font-black text-[var(--fc-text)]">Inventory Valuation Report</h1>
                <p class="text-xs text-[var(--fc-text-muted)] mt-0.5">Asset valuation of items currently in cart storage based on unit cost price</p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('reports.export', ['type' => 'inventory']) }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs font-bold text-[var(--fc-text)] hover:bg-[var(--fc-bg)] shadow-xs">
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
            <a href="{{ route('reports.expenses') }}" class="px-3 py-1.5 rounded-lg text-[var(--fc-text-muted)] hover:text-[var(--fc-text)] hover:bg-[var(--fc-card)]">
                Expenses Report
            </a>
            <a href="{{ route('reports.waste') }}" class="px-3 py-1.5 rounded-lg text-[var(--fc-text-muted)] hover:text-[var(--fc-text)] hover:bg-[var(--fc-card)]">
                Food Waste Report
            </a>
            <a href="{{ route('reports.inventory') }}" class="px-3 py-1.5 rounded-lg bg-[var(--fc-primary)] text-[var(--fc-primary-text)]">
                Inventory Valuation
            </a>
        </div>

        <!-- Summary -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="fc-card p-4 shadow-xs">
                <span class="text-xs font-semibold text-[var(--fc-text-muted)]">Total Storage Capital Valuation (COGS)</span>
                <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1">৳{{ number_format($totalValuation, 2) }}</p>
            </div>

            <div class="fc-card p-4 shadow-xs border-amber-500/30 bg-amber-500/5">
                <span class="text-xs font-bold text-amber-700 dark:text-amber-300">Items Needing Immediate Restocking</span>
                <p class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-1">{{ $lowStockCount }} items</p>
            </div>
        </div>

        <!-- Table -->
        <div class="fc-card overflow-hidden shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-start">
                    <thead class="bg-[var(--fc-bg)] border-b border-[var(--fc-border)] text-[var(--fc-text-muted)] uppercase tracking-wider font-semibold">
                        <tr>
                            <th class="py-3 px-4 text-start">Item Name</th>
                            <th class="py-3 px-4 text-start">Category</th>
                            <th class="py-3 px-4 text-center">In Stock</th>
                            <th class="py-3 px-4 text-end">Cost Price (৳)</th>
                            <th class="py-3 px-4 text-end">Selling Price (৳)</th>
                            <th class="py-3 px-4 text-end">Stock Asset Value (৳)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--fc-border)]">
                        @foreach($foods as $food)
                            <tr class="hover:bg-[var(--fc-bg)]/40 transition-colors">
                                <td class="py-3 px-4 font-bold text-[var(--fc-text)]">
                                    {{ $food->name }}
                                    @if($food->bengali_name)
                                        <span class="text-[11px] font-normal text-[var(--fc-text-muted)]">({{ $food->bengali_name }})</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-[var(--fc-text-muted)]">{{ $food->category?->name }}</td>
                                <td class="py-3 px-4 text-center font-bold {{ $food->is_low_stock ? 'text-red-500' : '' }}">
                                    {{ $food->current_stock }} {{ $food->unit }}
                                </td>
                                <td class="py-3 px-4 text-end text-[var(--fc-text-muted)]">৳{{ number_format($food->cost_price, 2) }}</td>
                                <td class="py-3 px-4 text-end font-semibold">৳{{ number_format($food->selling_price, 2) }}</td>
                                <td class="py-3 px-4 text-end font-black text-emerald-600">
                                    ৳{{ number_format($food->current_stock * $food->cost_price, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts::app>
