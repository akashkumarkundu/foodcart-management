<x-layouts::app title="Profit & Loss Statement">
    <div class="space-y-6">
        <!-- Top Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-[var(--fc-border)]">
            <div>
                <h1 class="text-2xl font-black text-[var(--fc-text)]">Profit & Loss (P&L) Statement</h1>
                <p class="text-xs text-[var(--fc-text-muted)] mt-0.5">Real-world commercial statement: Net Profit = Completed Sales - Expenses - Waste Cost</p>
            </div>

            <!-- Period Switcher Buttons -->
            <div class="flex items-center p-1 rounded-xl bg-[var(--fc-card)] border border-[var(--fc-border)] gap-1 text-xs">
                <a href="{{ route('profit-loss.index', ['period' => 'daily']) }}" class="px-3 py-1.5 rounded-lg font-semibold transition-colors {{ $period === 'daily' ? 'bg-[var(--fc-primary)] text-[var(--fc-primary-text)]' : 'text-[var(--fc-text-muted)] hover:text-[var(--fc-text)]' }}">
                    Daily
                </a>
                <a href="{{ route('profit-loss.index', ['period' => 'weekly']) }}" class="px-3 py-1.5 rounded-lg font-semibold transition-colors {{ $period === 'weekly' ? 'bg-[var(--fc-primary)] text-[var(--fc-primary-text)]' : 'text-[var(--fc-text-muted)] hover:text-[var(--fc-text)]' }}">
                    Weekly
                </a>
                <a href="{{ route('profit-loss.index', ['period' => 'monthly']) }}" class="px-3 py-1.5 rounded-lg font-semibold transition-colors {{ $period === 'monthly' ? 'bg-[var(--fc-primary)] text-[var(--fc-primary-text)]' : 'text-[var(--fc-text-muted)] hover:text-[var(--fc-text)]' }}">
                    Monthly
                </a>
                <a href="{{ route('profit-loss.index', ['period' => 'yearly']) }}" class="px-3 py-1.5 rounded-lg font-semibold transition-colors {{ $period === 'yearly' ? 'bg-[var(--fc-primary)] text-[var(--fc-primary-text)]' : 'text-[var(--fc-text-muted)] hover:text-[var(--fc-text)]' }}">
                    Yearly
                </a>
            </div>
        </div>

        <!-- Period Summary Header -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Completed Sales -->
            <div class="fc-card p-5 shadow-xs">
                <div class="flex items-center justify-between text-[var(--fc-text-muted)] text-xs mb-1">
                    <span>{{ $periodLabel }} Sales</span>
                    <span class="text-[11px] font-bold {{ $salesGrowth >= 0 ? 'text-emerald-600' : 'text-red-500' }}">
                        {{ $salesGrowth >= 0 ? '+' : '' }}{{ $salesGrowth }}%
                    </span>
                </div>
                <p class="text-2xl font-black text-[var(--fc-text)]">৳{{ number_format($currentSummary['completed_sales'], 2) }}</p>
                <p class="text-[11px] text-[var(--fc-text-muted)] mt-1">vs ৳{{ number_format($prevSummary['completed_sales'], 2) }} ({{ $prevPeriodLabel }})</p>
            </div>

            <!-- Expenses -->
            <div class="fc-card p-5 shadow-xs">
                <div class="text-[var(--fc-text-muted)] text-xs mb-1">
                    <span>Operational Expenses</span>
                </div>
                <p class="text-2xl font-black text-amber-600 dark:text-amber-400">৳{{ number_format($currentSummary['total_expenses'], 2) }}</p>
                <p class="text-[11px] text-[var(--fc-text-muted)] mt-1">Rent, gas, salaries, etc.</p>
            </div>

            <!-- Waste Cost -->
            <div class="fc-card p-5 shadow-xs">
                <div class="text-[var(--fc-text-muted)] text-xs mb-1">
                    <span>Food Waste Cost</span>
                </div>
                <p class="text-2xl font-black text-red-600 dark:text-red-400">৳{{ number_format($currentSummary['total_waste'], 2) }}</p>
                <p class="text-[11px] text-[var(--fc-text-muted)] mt-1">Kitchen loss & burn</p>
            </div>

            <!-- Net Profit -->
            <div class="fc-card p-5 shadow-xs border-emerald-500/40 bg-emerald-500/5">
                <div class="flex items-center justify-between text-xs mb-1">
                    <span class="font-bold text-emerald-800 dark:text-emerald-200">Net Profit</span>
                    <span class="text-[11px] font-black {{ $profitGrowth >= 0 ? 'text-emerald-600' : 'text-red-500' }}">
                        {{ $profitGrowth >= 0 ? '+' : '' }}{{ $profitGrowth }}%
                    </span>
                </div>
                <p class="text-2xl font-black text-[var(--fc-text)]">৳{{ number_format($currentSummary['net_profit'], 2) }}</p>
                <p class="text-[11px] font-bold text-emerald-600 mt-1">{{ $currentSummary['profit_margin'] }}% Net Margin</p>
            </div>
        </div>

        <!-- Detailed Statement Table & Monthly Chart -->
        <div :class="deviceView === 'mobile' ? 'space-y-4' : 'grid grid-cols-1 lg:grid-cols-12 gap-6'">
            <!-- Formal P&L Statement Table (7 Cols on desktop, full width on mobile) -->
            <div :class="deviceView === 'mobile' ? 'w-full' : 'lg:col-span-7'" class="fc-card p-4 sm:p-6 shadow-xs space-y-4">
                <h2 class="text-base font-black text-[var(--fc-text)] border-b border-[var(--fc-border)] pb-3">
                    Detailed Financial Statement ({{ $periodLabel }})
                </h2>

                <div class="space-y-2 text-xs divide-y divide-[var(--fc-border)]">
                    <!-- Revenue Section -->
                    <div class="pt-2">
                        <div class="flex justify-between font-bold text-[var(--fc-text)]">
                            <span>A. REVENUE & SALES</span>
                            <span></span>
                        </div>
                        <div class="flex justify-between pl-4 py-1 text-[var(--fc-text-muted)]">
                            <span>Gross Menu Sales</span>
                            <span>৳{{ number_format($currentSummary['gross_sales'], 2) }}</span>
                        </div>
                        <div class="flex justify-between pl-4 py-1 text-emerald-600 font-medium">
                            <span>Less: Promotional Discounts & Coupons</span>
                            <span>- ৳{{ number_format($currentSummary['total_discount'], 2) }}</span>
                        </div>
                        <div class="flex justify-between pl-4 py-1.5 font-bold text-[var(--fc-text)] border-t border-[var(--fc-border)]/60">
                            <span>Net Completed Sales Revenue</span>
                            <span class="text-sm font-black">৳{{ number_format($currentSummary['completed_sales'], 2) }}</span>
                        </div>
                    </div>

                    <!-- Deductions Section -->
                    <div class="pt-3">
                        <div class="flex justify-between font-bold text-[var(--fc-text)]">
                            <span>B. OPERATIONAL DEDUCTIONS</span>
                            <span></span>
                        </div>
                        <div class="flex justify-between pl-4 py-1 text-[var(--fc-text-muted)]">
                            <span>Stall Operating Expenses (Gas, Packaging, Salary, Rent)</span>
                            <span>৳{{ number_format($currentSummary['total_expenses'], 2) }}</span>
                        </div>
                        <div class="flex justify-between pl-4 py-1 text-[var(--fc-text-muted)]">
                            <span>Food Waste & Spoilage Cost</span>
                            <span>৳{{ number_format($currentSummary['total_waste'], 2) }}</span>
                        </div>
                        <div class="flex justify-between pl-4 py-1.5 font-bold text-amber-600 border-t border-[var(--fc-border)]/60">
                            <span>Total Business Outflow</span>
                            <span class="font-black">৳{{ number_format($currentSummary['total_expenses'] + $currentSummary['total_waste'], 2) }}</span>
                        </div>
                    </div>

                    <!-- Net Profit Outcome -->
                    <div class="pt-3">
                        <div class="flex justify-between font-black text-base text-[var(--fc-text)] py-2">
                            <span>NET PROFIT (A - B)</span>
                            <span class="text-emerald-600 dark:text-emerald-400 text-lg font-black">
                                ৳{{ number_format($currentSummary['net_profit'], 2) }}
                            </span>
                        </div>
                        <div class="flex justify-between text-xs text-[var(--fc-text-muted)] pl-4">
                            <span>Net Profit Margin Percentage</span>
                            <span class="font-bold text-emerald-600">{{ $currentSummary['profit_margin'] }}%</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Trend Visualizer (5 Cols on desktop, full width on mobile) -->
            <div :class="deviceView === 'mobile' ? 'w-full' : 'lg:col-span-5'" class="fc-card p-4 sm:p-6 shadow-xs space-y-4">
                <h2 class="text-base font-black text-[var(--fc-text)] border-b border-[var(--fc-border)] pb-3">
                    Monthly P&L Comparison ({{ now()->year }})
                </h2>

                <div class="h-64 sm:h-72">
                    <canvas id="monthlyPlChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('monthlyPlChart');
            if (ctx) {
                const monthlyData = @json($monthlyTrend);
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: monthlyData.map(m => m.month),
                        datasets: [
                            {
                                label: 'Sales (৳)',
                                data: monthlyData.map(m => m.sales),
                                backgroundColor: '#10b981',
                                borderRadius: 4,
                            },
                            {
                                label: 'Profit (৳)',
                                data: monthlyData.map(m => m.profit),
                                backgroundColor: '#3b82f6',
                                borderRadius: 4,
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'top', labels: { font: { family: 'Plus Jakarta Sans', size: 10 } } }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { callback: v => '৳' + v.toLocaleString(), font: { size: 9 } }
                            },
                            x: { ticks: { font: { size: 9 } } }
                        }
                    }
                });
            }
        });
    </script>
</x-layouts::app>
