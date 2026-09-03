<x-layouts::app title="Orders Management">
    <div class="space-y-6">
        <!-- Top Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-[var(--fc-border)]">
            <div>
                <h1 class="text-2xl font-black text-[var(--fc-text)]">Orders Management</h1>
                <p class="text-xs text-[var(--fc-text-muted)] mt-0.5">Track live food cart orders, preparation stages, and invoices</p>
            </div>

            <a href="{{ route('pos.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[var(--fc-primary)] text-[var(--fc-primary-text)] font-bold text-xs shadow-sm hover:opacity-95">
                <flux:icon name="plus" class="size-4" />
                <span>New POS Order</span>
            </a>
        </div>

        <!-- Filters Bar -->
        <div class="fc-card p-3 sm:p-4 shadow-xs">
            <form method="GET" action="{{ route('orders.index') }}" :class="deviceView === 'mobile' ? 'space-y-2.5' : 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3'">
                <!-- Search -->
                <div :class="deviceView === 'mobile' ? 'w-full' : ''">
                    <label class="block text-[11px] font-semibold text-[var(--fc-text-muted)] mb-1">Search</label>
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Order # or Customer..."
                        class="w-full px-3 py-1.5 rounded-lg border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs text-[var(--fc-text)] focus:border-[var(--fc-primary)] outline-none"
                    />
                </div>

                <!-- Status & Payment Method on mobile -->
                <div :class="deviceView === 'mobile' ? 'grid grid-cols-2 gap-2' : 'contents'">
                    <!-- Status Filter -->
                    <div>
                        <label class="block text-[11px] font-semibold text-[var(--fc-text-muted)] mb-1">Status</label>
                        <select name="status" class="w-full px-3 py-1.5 rounded-lg border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs text-[var(--fc-text)] focus:border-[var(--fc-primary)] outline-none">
                            <option value="">All Statuses</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="preparing" {{ request('status') === 'preparing' ? 'selected' : '' }}>Preparing</option>
                            <option value="ready" {{ request('status') === 'ready' ? 'selected' : '' }}>Ready</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>

                    <!-- Payment Method -->
                    <div>
                        <label class="block text-[11px] font-semibold text-[var(--fc-text-muted)] mb-1">Payment Method</label>
                        <select name="payment_method" class="w-full px-3 py-1.5 rounded-lg border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs text-[var(--fc-text)] focus:border-[var(--fc-primary)] outline-none">
                            <option value="">All Methods</option>
                            <option value="cash" {{ request('payment_method') === 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="bkash" {{ request('payment_method') === 'bkash' ? 'selected' : '' }}>bKash</option>
                            <option value="nagad" {{ request('payment_method') === 'nagad' ? 'selected' : '' }}>Nagad</option>
                            <option value="rocket" {{ request('payment_method') === 'rocket' ? 'selected' : '' }}>Rocket</option>
                            <option value="card" {{ request('payment_method') === 'card' ? 'selected' : '' }}>Card</option>
                        </select>
                    </div>
                </div>

                <!-- Date -->
                <div :class="deviceView === 'mobile' ? 'w-full' : ''">
                    <label class="block text-[11px] font-semibold text-[var(--fc-text-muted)] mb-1">Date</label>
                    <input
                        type="date"
                        name="date"
                        value="{{ request('date') }}"
                        class="w-full px-3 py-1.5 rounded-lg border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs text-[var(--fc-text)] focus:border-[var(--fc-primary)] outline-none"
                    />
                </div>

                <!-- Submit & Reset -->
                <div :class="deviceView === 'mobile' ? 'grid grid-cols-2 gap-2 pt-1' : 'flex items-end gap-2'">
                    <button type="submit" class="w-full py-2 px-3 rounded-lg bg-[var(--fc-primary)] text-[var(--fc-primary-text)] font-bold text-xs shadow-xs text-center">
                        Filter
                    </button>
                    <a href="{{ route('orders.index') }}" class="w-full py-2 px-3 rounded-lg border border-[var(--fc-border)] text-xs font-semibold text-[var(--fc-text-muted)] hover:bg-[var(--fc-bg)] text-center">
                        Reset
                    </a>
                </div>
            </form>
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
                            <th class="py-3 px-4 text-start">Items Ordered</th>
                            <th class="py-3 px-4 text-start">Payment</th>
                            <th class="py-3 px-4 text-start">Status</th>
                            <th class="py-3 px-4 text-end">Total (৳)</th>
                            <th class="py-3 px-4 text-end">Actions</th>
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
                                    {{ $order->customer?->name ?? 'Guest Customer' }}
                                    @if($order->customer?->phone)
                                        <div class="text-[10px] text-[var(--fc-text-muted)]">{{ $order->customer->phone }}</div>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-[var(--fc-text-muted)] max-w-xs truncate">
                                    {{ $order->items->pluck('food_name')->implode(', ') }}
                                </td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded font-bold uppercase text-[10px] {{ $order->payment_method === 'bkash' ? 'bg-pink-500/10 text-pink-600' : ($order->payment_method === 'nagad' ? 'bg-orange-500/10 text-orange-600' : 'bg-emerald-500/10 text-emerald-600') }}">
                                        {{ $order->payment_method }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded font-bold text-[10px] uppercase {{ $order->order_status === 'completed' ? 'bg-emerald-500/10 text-emerald-600' : ($order->order_status === 'cancelled' ? 'bg-red-500/10 text-red-600' : 'bg-amber-500/10 text-amber-600') }}">
                                        {{ $order->order_status }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-end font-black text-sm text-[var(--fc-text)]">
                                    ৳{{ number_format($order->total_amount, 2) }}
                                </td>
                                <td class="py-3 px-4 text-end space-x-1">
                                    <a href="{{ route('orders.show', $order) }}" class="p-1.5 rounded-lg border border-[var(--fc-border)] hover:bg-[var(--fc-bg)] inline-block text-[var(--fc-text)]" title="View Details">
                                        <flux:icon name="eye" class="size-3.5" />
                                    </a>
                                    <a href="{{ route('orders.invoice', $order) }}" target="_blank" class="p-1.5 rounded-lg border border-[var(--fc-border)] hover:bg-[var(--fc-bg)] inline-block text-[var(--fc-primary)]" title="Print Receipt">
                                        <flux:icon name="printer" class="size-3.5" />
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-8 text-center text-xs text-[var(--fc-text-muted)]">
                                    No orders match your filter criteria.
                                </td>
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
