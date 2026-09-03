<x-layouts::app title="Smart Business Insights">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-[var(--fc-border)]">
            <div class="flex items-center gap-3">
                <div class="size-10 rounded-2xl bg-amber-500/10 text-amber-500 flex items-center justify-center shadow-xs">
                    <flux:icon name="sparkles" class="size-6" />
                </div>
                <div>
                    <h1 class="text-2xl font-black text-[var(--fc-text)]">Smart Business Insights</h1>
                    <p class="text-xs text-[var(--fc-text-muted)] mt-0.5">Automated rule-based intelligence analyzing your sales, rush hours, margins, and food waste</p>
                </div>
            </div>
        </div>

        <!-- Generated Insights Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @forelse($insights as $insight)
                <div class="fc-card p-5 shadow-xs flex flex-col justify-between space-y-4 border-l-4 {{ $insight['type'] === 'success' ? 'border-l-emerald-500' : ($insight['type'] === 'warning' ? 'border-l-amber-500' : ($insight['type'] === 'danger' ? 'border-l-red-500' : 'border-l-blue-500')) }}">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[11px] font-bold uppercase tracking-wider px-2.5 py-0.5 rounded-full bg-[var(--fc-bg)] border border-[var(--fc-border)] text-[var(--fc-text-muted)]">
                                {{ $insight['category'] }}
                            </span>
                            <span class="text-[10px] text-[var(--fc-text-muted)] font-medium">Real-Time Metric</span>
                        </div>

                        <h3 class="text-base font-extrabold text-[var(--fc-text)] mb-1">{{ $insight['title'] }}</h3>
                        <p class="text-xs text-[var(--fc-text-muted)] leading-relaxed">{{ $insight['description'] }}</p>
                    </div>

                    <div class="p-3 rounded-xl bg-[var(--fc-bg)] border border-[var(--fc-border)] text-xs font-semibold text-emerald-700 dark:text-emerald-300 flex items-start gap-2">
                        <flux:icon name="light-bulb" class="size-4 shrink-0 text-emerald-500 mt-0.5" />
                        <div>
                            <span class="font-bold text-[var(--fc-text)]">Recommended Action:</span>
                            <p class="mt-0.5 font-normal">{{ $insight['action'] }}</p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-12 text-center fc-card p-6">
                    <flux:icon name="sparkles" class="size-10 text-[var(--fc-text-muted)] mx-auto mb-2 opacity-40" />
                    <p class="text-sm font-semibold text-[var(--fc-text-muted)]">Gathering sales data to generate smart recommendations.</p>
                </div>
            @endforelse
        </div>

        <!-- Top Selling Foods by Volume & Profit -->
        <div class="fc-card p-6 shadow-xs">
            <h2 class="text-base font-bold text-[var(--fc-text)] mb-4">Item Performance Matrix (Volume vs Margin)</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-start">
                    <thead class="bg-[var(--fc-bg)] border-b border-[var(--fc-border)] text-[var(--fc-text-muted)] uppercase tracking-wider font-semibold">
                        <tr>
                            <th class="py-3 px-4 text-start">Food Item</th>
                            <th class="py-3 px-4 text-center">Units Sold</th>
                            <th class="py-3 px-4 text-end">Total Revenue (৳)</th>
                            <th class="py-3 px-4 text-end">Net Contribution Profit (৳)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--fc-border)]">
                        @foreach($topFoods as $food)
                            <tr class="hover:bg-[var(--fc-bg)]/40 transition-colors">
                                <td class="py-3 px-4 font-bold text-[var(--fc-text)]">{{ $food->food_name }}</td>
                                <td class="py-3 px-4 text-center font-bold">{{ $food->total_qty }}</td>
                                <td class="py-3 px-4 text-end font-semibold">৳{{ number_format($food->total_revenue, 2) }}</td>
                                <td class="py-3 px-4 text-end font-black text-emerald-600">৳{{ number_format($food->total_profit, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts::app>
