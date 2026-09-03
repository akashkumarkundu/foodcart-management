<x-layouts::app :title="$customer->name">
    <div class="space-y-6">
        <div class="flex items-center gap-3 pb-2 border-b border-[var(--fc-border)]">
            <a href="{{ route('customers.index') }}" class="p-2 rounded-xl border border-[var(--fc-border)] text-[var(--fc-text-muted)] hover:bg-[var(--fc-bg)]">
                <flux:icon name="arrow-left" class="size-4" />
            </a>
            <div>
                <h1 class="text-2xl font-black text-[var(--fc-text)]">{{ $customer->name }}</h1>
                <p class="text-xs text-[var(--fc-text-muted)] mt-0.5">{{ $customer->phone }} • Registered Customer Profile</p>
            </div>
        </div>

        <!-- Metrics -->
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
            <div class="fc-card p-4 shadow-xs">
                <span class="text-xs font-semibold text-[var(--fc-text-muted)]">Total Orders</span>
                <p class="text-2xl font-black text-[var(--fc-text)] mt-1">{{ $customer->total_orders }}</p>
            </div>
            <div class="fc-card p-4 shadow-xs">
                <span class="text-xs font-semibold text-[var(--fc-text-muted)]">Total Spend</span>
                <p class="text-2xl font-black text-emerald-600 mt-1">৳{{ number_format($customer->total_spent, 2) }}</p>
            </div>
            <div class="fc-card p-4 shadow-xs">
                <span class="text-xs font-semibold text-[var(--fc-text-muted)]">Average Order Value</span>
                <p class="text-2xl font-black text-[var(--fc-text)] mt-1">৳{{ number_format($customer->average_order_value, 2) }}</p>
            </div>
            <div class="fc-card p-4 shadow-xs border-amber-500/30 bg-amber-500/5">
                <span class="text-xs font-bold text-amber-700 dark:text-amber-300">Loyalty Points</span>
                <p class="text-2xl font-black text-amber-500 mt-1">{{ $customer->loyalty_points }}</p>
            </div>
        </div>

        @if($favoriteFood)
            <div class="p-4 rounded-xl border border-emerald-500/30 bg-emerald-500/10 text-emerald-800 dark:text-emerald-200 flex items-center justify-between text-xs">
                <div class="flex items-center gap-2">
                    <flux:icon name="heart" class="size-5 text-emerald-600" />
                    <span><strong>Customer's Favorite Dish:</strong> {{ $favoriteFood->food_name }} (Ordered {{ $favoriteFood->total_ordered }} times)</span>
                </div>
            </div>
        @endif

        <!-- Customer Order History -->
        <div class="fc-card overflow-hidden shadow-xs">
            <div class="p-4 border-b border-[var(--fc-border)]">
                <h2 class="font-bold text-sm text-[var(--fc-text)]">Order History</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-start">
                    <thead class="bg-[var(--fc-bg)] border-b border-[var(--fc-border)] text-[var(--fc-text-muted)] uppercase tracking-wider font-semibold">
                        <tr>
                            <th class="py-3 px-4 text-start">Order #</th>
                            <th class="py-3 px-4 text-start">Date</th>
                            <th class="py-3 px-4 text-start">Items</th>
                            <th class="py-3 px-4 text-start">Payment</th>
                            <th class="py-3 px-4 text-end">Total Amount (৳)</th>
                            <th class="py-3 px-4 text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--fc-border)]">
                        @forelse($customer->orders as $order)
                            <tr class="hover:bg-[var(--fc-bg)]/40 transition-colors">
                                <td class="py-3 px-4 font-bold text-[var(--fc-primary)]">
                                    <a href="{{ route('orders.show', $order) }}">{{ $order->order_number }}</a>
                                </td>
                                <td class="py-3 px-4 text-[var(--fc-text-muted)]">{{ $order->created_at->format('d M Y, h:i A') }}</td>
                                <td class="py-3 px-4 text-[var(--fc-text-muted)]">{{ $order->items->pluck('food_name')->implode(', ') }}</td>
                                <td class="py-3 px-4 uppercase font-bold text-[10px]">{{ $order->payment_method }}</td>
                                <td class="py-3 px-4 text-end font-black text-emerald-600">৳{{ number_format($order->total_amount, 2) }}</td>
                                <td class="py-3 px-4 text-end">
                                    <a href="{{ route('orders.invoice', $order) }}" target="_blank" class="p-1 rounded text-[var(--fc-primary)]" title="Print Invoice">
                                        <flux:icon name="printer" class="size-3.5 inline" />
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-6 text-center text-xs text-[var(--fc-text-muted)]">No orders for this customer yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts::app>
