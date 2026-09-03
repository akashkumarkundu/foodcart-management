<x-layouts::app :title="'PO ' . $purchase->purchase_number">
    <div class="max-w-3xl mx-auto space-y-6">
        <div class="flex items-center gap-3 pb-2 border-b border-[var(--fc-border)]">
            <a href="{{ route('purchases.index') }}" class="p-2 rounded-xl border border-[var(--fc-border)] text-[var(--fc-text-muted)] hover:bg-[var(--fc-bg)]">
                <flux:icon name="arrow-left" class="size-4" />
            </a>
            <div>
                <h1 class="text-2xl font-black text-[var(--fc-text)]">Purchase Order {{ $purchase->purchase_number }}</h1>
                <p class="text-xs text-[var(--fc-text-muted)] mt-0.5">{{ $purchase->purchase_date->format('d F Y') }}</p>
            </div>
        </div>

        <div class="fc-card p-6 shadow-xs space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pb-4 border-b border-[var(--fc-border)] text-xs">
                <div>
                    <h3 class="font-bold text-[var(--fc-text)]">Supplier Information</h3>
                    <p class="font-semibold text-sm mt-1">{{ $purchase->supplier->name }}</p>
                    <p class="text-[var(--fc-text-muted)]">Phone: {{ $purchase->supplier->phone }}</p>
                    @if($purchase->supplier->address)
                        <p class="text-[var(--fc-text-muted)]">{{ $purchase->supplier->address }}</p>
                    @endif
                </div>

                <div class="text-end">
                    <h3 class="font-bold text-[var(--fc-text)]">Payment Status</h3>
                    <span class="inline-block mt-1 px-2.5 py-1 rounded font-black text-xs uppercase {{ $purchase->payment_status === 'paid' ? 'bg-emerald-500/10 text-emerald-600' : 'bg-red-500/10 text-red-600' }}">
                        {{ $purchase->payment_status }}
                    </span>
                    <p class="text-[var(--fc-text-muted)] mt-1">Method: {{ strtoupper($purchase->payment_method) }}</p>
                </div>
            </div>

            <!-- Items -->
            <table class="w-full text-xs text-start">
                <thead class="border-b border-[var(--fc-border)] text-[var(--fc-text-muted)] uppercase tracking-wider font-semibold">
                    <tr>
                        <th class="py-2 text-start">Item</th>
                        <th class="py-2 text-center">Quantity</th>
                        <th class="py-2 text-end">Unit Price (৳)</th>
                        <th class="py-2 text-end">Total Price (৳)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[var(--fc-border)]">
                    @foreach($purchase->items as $item)
                        <tr>
                            <td class="py-2.5 font-bold text-[var(--fc-text)]">{{ $item->item_name }}</td>
                            <td class="py-2.5 text-center">{{ $item->quantity }} {{ $item->unit }}</td>
                            <td class="py-2.5 text-end">৳{{ number_format($item->unit_price, 2) }}</td>
                            <td class="py-2.5 text-end font-bold">৳{{ number_format($item->total_price, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Financials -->
            <div class="pt-4 border-t border-[var(--fc-border)] space-y-1.5 text-xs text-end">
                <p class="text-[var(--fc-text-muted)]">Total Amount: <strong class="text-sm font-black text-[var(--fc-text)]">৳{{ number_format($purchase->total_amount, 2) }}</strong></p>
                <p class="text-emerald-600 font-semibold">Paid Amount: ৳{{ number_format($purchase->paid_amount, 2) }}</p>
                @if($purchase->due_amount > 0)
                    <p class="text-red-500 font-bold">Outstanding Due: ৳{{ number_format($purchase->due_amount, 2) }}</p>
                @endif
            </div>
        </div>
    </div>
</x-layouts::app>
