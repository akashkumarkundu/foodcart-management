<x-layouts::app title="Payments Management">
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-[var(--fc-border)]">
            <div>
                <h1 class="text-2xl font-black text-[var(--fc-text)]">Payments & Collections Ledger</h1>
                <p class="text-xs text-[var(--fc-text-muted)] mt-0.5">Real-time payment tracking across bKash, Nagad, Rocket, Card, and Cash</p>
            </div>
        </div>

        <!-- Payment Breakdown Cards -->
        <div :class="deviceView === 'mobile' ? 'grid grid-cols-2 gap-2' : 'grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3'">
            <div class="fc-card p-3.5 shadow-xs">
                <span class="text-[11px] font-semibold text-[var(--fc-text-muted)]">Total Collected</span>
                <p class="text-lg sm:text-xl font-black text-[var(--fc-text)] mt-1">৳{{ number_format($summary['total'], 2) }}</p>
            </div>

            <div class="fc-card p-3.5 shadow-xs border-emerald-500/30">
                <span class="text-[11px] font-bold text-emerald-600">Cash</span>
                <p class="text-lg sm:text-xl font-black text-emerald-600 mt-1">৳{{ number_format($summary['cash'], 2) }}</p>
            </div>

            <div class="fc-card p-3.5 shadow-xs border-pink-500/30">
                <span class="text-[11px] font-bold text-pink-600">bKash</span>
                <p class="text-lg sm:text-xl font-black text-pink-600 mt-1">৳{{ number_format($summary['bkash'], 2) }}</p>
            </div>

            <div class="fc-card p-3.5 shadow-xs border-orange-500/30">
                <span class="text-[11px] font-bold text-orange-600">Nagad</span>
                <p class="text-lg sm:text-xl font-black text-orange-600 mt-1">৳{{ number_format($summary['nagad'], 2) }}</p>
            </div>

            <div class="fc-card p-3.5 shadow-xs border-purple-500/30">
                <span class="text-[11px] font-bold text-purple-600">Rocket</span>
                <p class="text-lg sm:text-xl font-black text-purple-600 mt-1">৳{{ number_format($summary['rocket'], 2) }}</p>
            </div>

            <div class="fc-card p-3.5 shadow-xs border-blue-500/30">
                <span class="text-[11px] font-bold text-blue-600">Debit / Card</span>
                <p class="text-lg sm:text-xl font-black text-blue-600 mt-1">৳{{ number_format($summary['card'], 2) }}</p>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="fc-card p-3 sm:p-4 shadow-xs">
            <form method="GET" action="{{ route('payments.index') }}" :class="deviceView === 'mobile' ? 'space-y-2.5' : 'grid grid-cols-1 sm:grid-cols-4 gap-3'">
                <div :class="deviceView === 'mobile' ? 'w-full' : ''">
                    <label class="block text-[11px] font-semibold text-[var(--fc-text-muted)] mb-1">Search Transaction</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Txn ID, Ref, Order #..." class="w-full px-3 py-1.5 rounded-lg border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs text-[var(--fc-text)] outline-none" />
                </div>
                <div :class="deviceView === 'mobile' ? 'grid grid-cols-2 gap-2' : 'contents'">
                    <div>
                        <label class="block text-[11px] font-semibold text-[var(--fc-text-muted)] mb-1">Payment Method</label>
                        <select name="method" class="w-full px-3 py-1.5 rounded-lg border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs text-[var(--fc-text)] outline-none">
                            <option value="">All Channels</option>
                            <option value="cash" {{ request('method') === 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="bkash" {{ request('method') === 'bkash' ? 'selected' : '' }}>bKash</option>
                            <option value="nagad" {{ request('method') === 'nagad' ? 'selected' : '' }}>Nagad</option>
                            <option value="rocket" {{ request('method') === 'rocket' ? 'selected' : '' }}>Rocket</option>
                            <option value="card" {{ request('method') === 'card' ? 'selected' : '' }}>Card</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-[var(--fc-text-muted)] mb-1">Date</label>
                        <input type="date" name="date" value="{{ request('date') }}" class="w-full px-3 py-1.5 rounded-lg border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs text-[var(--fc-text)] outline-none" />
                    </div>
                </div>
                <div :class="deviceView === 'mobile' ? 'grid grid-cols-2 gap-2 pt-1' : 'flex items-end gap-2'">
                    <button type="submit" class="w-full py-2 px-3 rounded-lg bg-[var(--fc-primary)] text-[var(--fc-primary-text)] font-bold text-xs text-center">Filter</button>
                    <a href="{{ route('payments.index') }}" class="w-full py-2 px-3 rounded-lg border border-[var(--fc-border)] text-xs font-semibold text-[var(--fc-text-muted)] text-center hover:bg-[var(--fc-bg)]">Reset</a>
                </div>
            </form>
        </div>

        <!-- Payments Table -->
        <div class="fc-card overflow-hidden shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-start">
                    <thead class="bg-[var(--fc-bg)] border-b border-[var(--fc-border)] text-[var(--fc-text-muted)] uppercase tracking-wider font-semibold">
                        <tr>
                            <th class="py-3 px-4 text-start">Date & Time</th>
                            <th class="py-3 px-4 text-start">Order Number</th>
                            <th class="py-3 px-4 text-start">Customer</th>
                            <th class="py-3 px-4 text-start">Channel</th>
                            <th class="py-3 px-4 text-start">Transaction ID / Ref</th>
                            <th class="py-3 px-4 text-end">Amount (৳)</th>
                            <th class="py-3 px-4 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--fc-border)]">
                        @forelse($payments as $payment)
                            <tr class="hover:bg-[var(--fc-bg)]/40 transition-colors">
                                <td class="py-3 px-4 text-[var(--fc-text-muted)]">
                                    {{ $payment->payment_date->format('d M Y, h:i A') }}
                                </td>
                                <td class="py-3 px-4 font-bold text-[var(--fc-primary)]">
                                    <a href="{{ route('orders.show', $payment->order) }}">{{ $payment->order->order_number }}</a>
                                </td>
                                <td class="py-3 px-4 font-medium text-[var(--fc-text)]">
                                    {{ $payment->customer?->name ?? 'Guest Customer' }}
                                </td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded font-bold uppercase text-[10px] {{ $payment->payment_method === 'bkash' ? 'bg-pink-500/10 text-pink-600' : ($payment->payment_method === 'nagad' ? 'bg-orange-500/10 text-orange-600' : 'bg-emerald-500/10 text-emerald-600') }}">
                                        {{ $payment->method_label }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 font-mono text-[var(--fc-text-muted)]">
                                    {{ $payment->transaction_id ?? $payment->reference ?? '-' }}
                                </td>
                                <td class="py-3 px-4 text-end font-black text-sm text-[var(--fc-text)]">
                                    ৳{{ number_format($payment->amount, 2) }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-emerald-500/10 text-emerald-600">
                                        {{ $payment->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-6 text-center text-xs text-[var(--fc-text-muted)]">No payments found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-[var(--fc-border)]">
                {{ $payments->links() }}
            </div>
        </div>
    </div>
</x-layouts::app>
