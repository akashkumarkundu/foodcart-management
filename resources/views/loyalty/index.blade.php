<x-layouts::app title="Loyalty Program">
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-[var(--fc-border)]">
            <div>
                <h1 class="text-2xl font-black text-[var(--fc-text)]">Customer Loyalty Program</h1>
                <p class="text-xs text-[var(--fc-text-muted)] mt-0.5">Reward regular street food patrons with automated points and discounts</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Rule Configuration (1 Col) -->
            <div class="fc-card p-5 shadow-xs space-y-4">
                <h2 class="font-bold text-sm text-[var(--fc-text)] border-b border-[var(--fc-border)] pb-2">Loyalty Earning Rule</h2>
                <form method="POST" action="{{ route('loyalty.update-ratio') }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-[var(--fc-text)] mb-1">Spend to Earn 1 Point (৳) *</label>
                        <input
                            type="number"
                            name="loyalty_points_ratio"
                            value="{{ $ratio }}"
                            required
                            min="10"
                            class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-base font-bold text-[var(--fc-text)] focus:border-[var(--fc-primary)] outline-none"
                        />
                        <p class="text-[11px] text-[var(--fc-text-muted)] mt-1">Default: ৳100 spent gives 1 loyalty point.</p>
                    </div>

                    <button type="submit" class="w-full py-2.5 rounded-xl bg-[var(--fc-primary)] text-[var(--fc-primary-text)] font-bold text-xs shadow-md">
                        Update Earning Rule
                    </button>
                </form>

                <div class="pt-3 border-t border-[var(--fc-border)] space-y-2 text-xs">
                    <div class="flex justify-between">
                        <span class="text-[var(--fc-text-muted)]">Total Points Earned:</span>
                        <span class="font-bold text-emerald-600">{{ number_format($totalEarned) }} pts</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[var(--fc-text-muted)]">Points Redeemed:</span>
                        <span class="font-bold text-amber-500">{{ number_format($totalRedeemed) }} pts</span>
                    </div>
                </div>
            </div>

            <!-- Top Loyal Patrons (2 Cols) -->
            <div class="lg:col-span-2 fc-card overflow-hidden shadow-xs">
                <div class="p-4 border-b border-[var(--fc-border)]">
                    <h2 class="font-bold text-sm text-[var(--fc-text)]">Top Loyal Patrons</h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-start">
                        <thead class="bg-[var(--fc-bg)] border-b border-[var(--fc-border)] text-[var(--fc-text-muted)] uppercase tracking-wider font-semibold">
                            <tr>
                                <th class="py-3 px-4 text-start">Customer</th>
                                <th class="py-3 px-4 text-start">Phone</th>
                                <th class="py-3 px-4 text-center">Orders</th>
                                <th class="py-3 px-4 text-end">Total Spend</th>
                                <th class="py-3 px-4 text-center">Loyalty Balance</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[var(--fc-border)]">
                            @foreach($topLoyalCustomers as $patron)
                                <tr class="hover:bg-[var(--fc-bg)]/40 transition-colors">
                                    <td class="py-3 px-4 font-bold text-[var(--fc-text)]">{{ $patron->name }}</td>
                                    <td class="py-3 px-4 text-[var(--fc-text-muted)]">{{ $patron->phone }}</td>
                                    <td class="py-3 px-4 text-center font-bold">{{ $patron->total_orders }}</td>
                                    <td class="py-3 px-4 text-end font-bold text-emerald-600">৳{{ number_format($patron->total_spent, 2) }}</td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full font-black text-xs bg-amber-500/10 text-amber-600">
                                            <flux:icon name="star" class="size-3.5 text-amber-500" />
                                            {{ $patron->loyalty_points }} pts
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Points Ledger -->
        <div class="fc-card overflow-hidden shadow-xs">
            <div class="p-4 border-b border-[var(--fc-border)]">
                <h2 class="font-bold text-sm text-[var(--fc-text)]">Recent Loyalty Points Ledger</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-start">
                    <thead class="bg-[var(--fc-bg)] border-b border-[var(--fc-border)] text-[var(--fc-text-muted)] uppercase tracking-wider font-semibold">
                        <tr>
                            <th class="py-3 px-4 text-start">Date</th>
                            <th class="py-3 px-4 text-start">Customer</th>
                            <th class="py-3 px-4 text-start">Order</th>
                            <th class="py-3 px-4 text-center">Points</th>
                            <th class="py-3 px-4 text-start">Type</th>
                            <th class="py-3 px-4 text-start">Description</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--fc-border)]">
                        @forelse($transactions as $tx)
                            <tr class="hover:bg-[var(--fc-bg)]/40 transition-colors">
                                <td class="py-3 px-4 text-[var(--fc-text-muted)]">{{ $tx->created_at->format('d M Y, h:i A') }}</td>
                                <td class="py-3 px-4 font-bold text-[var(--fc-text)]">{{ $tx->customer->name }}</td>
                                <td class="py-3 px-4 text-[var(--fc-primary)] font-semibold">{{ $tx->order?->order_number ?? '-' }}</td>
                                <td class="py-3 px-4 text-center font-black {{ $tx->points > 0 ? 'text-emerald-600' : 'text-red-500' }}">
                                    {{ $tx->points > 0 ? '+' : '' }}{{ $tx->points }}
                                </td>
                                <td class="py-3 px-4 uppercase font-bold text-[10px]">{{ $tx->type }}</td>
                                <td class="py-3 px-4 text-[var(--fc-text-muted)]">{{ $tx->description }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-6 text-center text-xs text-[var(--fc-text-muted)]">No loyalty transactions recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-[var(--fc-border)]">
                {{ $transactions->links() }}
            </div>
        </div>
    </div>
</x-layouts::app>
