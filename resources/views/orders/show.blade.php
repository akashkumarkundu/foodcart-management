<x-layouts::app :title="'Order ' . $order->order_number">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-[var(--fc-border)]">
            <div class="flex items-center gap-3">
                <a href="{{ route('orders.index') }}" class="p-2 rounded-xl border border-[var(--fc-border)] text-[var(--fc-text-muted)] hover:bg-[var(--fc-bg)]">
                    <flux:icon name="arrow-left" class="size-4" />
                </a>
                <div>
                    <h1 class="text-2xl font-black text-[var(--fc-text)]">Order {{ $order->order_number }}</h1>
                    <p class="text-xs text-[var(--fc-text-muted)] mt-0.5">{{ $order->created_at->format('d F Y, h:i A') }}</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('orders.invoice', $order) }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[var(--fc-primary)] text-[var(--fc-primary-text)] font-bold text-xs shadow-sm hover:opacity-95">
                    <flux:icon name="printer" class="size-4" />
                    <span>Print Bill / Receipt</span>
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left: Order Items & Breakdown (2 Cols) -->
            <div class="lg:col-span-2 space-y-6">
                <div class="fc-card p-5 shadow-xs">
                    <h2 class="font-bold text-sm text-[var(--fc-text)] mb-3">Ordered Items</h2>
                    <div class="divide-y divide-[var(--fc-border)]">
                        @foreach($order->items as $item)
                            <div class="py-3 flex items-center justify-between">
                                <div>
                                    <h4 class="font-bold text-xs text-[var(--fc-text)]">{{ $item->food_name }}</h4>
                                    <p class="text-[11px] text-[var(--fc-text-muted)]">৳{{ number_format($item->unit_price, 2) }} × {{ $item->quantity }}</p>
                                </div>
                                <div class="text-end">
                                    <span class="font-black text-xs text-[var(--fc-text)]">৳{{ number_format($item->subtotal, 2) }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Totals Summary -->
                    <div class="pt-4 mt-2 border-t border-[var(--fc-border)] space-y-2 text-xs">
                        <div class="flex justify-between text-[var(--fc-text-muted)]">
                            <span>Subtotal:</span>
                            <span class="font-bold">৳{{ number_format($order->subtotal, 2) }}</span>
                        </div>
                        @if($order->discount_amount > 0)
                            <div class="flex justify-between text-emerald-600 font-semibold">
                                <span>Discount:</span>
                                <span>- ৳{{ number_format($order->discount_amount, 2) }}</span>
                            </div>
                        @endif
                        @if($order->tax_amount > 0)
                            <div class="flex justify-between text-[var(--fc-text-muted)]">
                                <span>Tax:</span>
                                <span>+ ৳{{ number_format($order->tax_amount, 2) }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between text-base font-black text-[var(--fc-text)] pt-2 border-t border-[var(--fc-border)]">
                            <span>Total Amount:</span>
                            <span class="text-emerald-600">৳{{ number_format($order->total_amount, 2) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Status Update Form -->
                <div class="fc-card p-5 shadow-xs">
                    <h2 class="font-bold text-sm text-[var(--fc-text)] mb-3">Update Order Status</h2>
                    <form method="POST" action="{{ route('orders.status', $order) }}" class="flex items-center gap-3">
                        @csrf
                        @method('PATCH')
                        <select name="order_status" class="px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs text-[var(--fc-text)] focus:border-[var(--fc-primary)] outline-none font-semibold">
                            <option value="pending" {{ $order->order_status === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="preparing" {{ $order->order_status === 'preparing' ? 'selected' : '' }}>Preparing</option>
                            <option value="ready" {{ $order->order_status === 'ready' ? 'selected' : '' }}>Ready</option>
                            <option value="completed" {{ $order->order_status === 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ $order->order_status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-[var(--fc-primary)] text-[var(--fc-primary-text)] font-bold text-xs shadow-xs">
                            Update Status
                        </button>
                    </form>
                </div>
            </div>

            <!-- Right: Customer & Payment Details (1 Col) -->
            <div class="space-y-6">
                <!-- Customer Card -->
                <div class="fc-card p-5 shadow-xs space-y-3">
                    <h2 class="font-bold text-sm text-[var(--fc-text)] border-b border-[var(--fc-border)] pb-2">Customer Details</h2>
                    @if($order->customer)
                        <div>
                            <p class="font-bold text-xs text-[var(--fc-text)]">{{ $order->customer->name }}</p>
                            <p class="text-xs text-[var(--fc-text-muted)]">{{ $order->customer->phone }}</p>
                            @if($order->customer->email)
                                <p class="text-xs text-[var(--fc-text-muted)]">{{ $order->customer->email }}</p>
                            @endif
                        </div>
                        <a href="{{ route('customers.show', $order->customer) }}" class="inline-block text-xs font-semibold text-[var(--fc-primary)] hover:underline">
                            View Customer Profile &rarr;
                        </a>
                    @else
                        <p class="text-xs text-[var(--fc-text-muted)]">Guest Customer (No account saved)</p>
                    @endif
                </div>

                <!-- Payment Details Card -->
                <div class="fc-card p-5 shadow-xs space-y-3">
                    <h2 class="font-bold text-sm text-[var(--fc-text)] border-b border-[var(--fc-border)] pb-2">Payment Record</h2>
                    <div class="space-y-2 text-xs">
                        <div class="flex justify-between">
                            <span class="text-[var(--fc-text-muted)]">Payment Mode:</span>
                            <span class="font-bold uppercase text-[var(--fc-text)]">{{ $order->payment_method }}</span>
                        </div>
                        @if($order->latestPayment?->transaction_id)
                            <div class="flex justify-between">
                                <span class="text-[var(--fc-text-muted)]">Transaction ID:</span>
                                <span class="font-mono font-bold">{{ $order->latestPayment->transaction_id }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between">
                            <span class="text-[var(--fc-text-muted)]">Payment Status:</span>
                            <span class="font-bold text-emerald-600 uppercase">{{ $order->payment_status }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts::app>
