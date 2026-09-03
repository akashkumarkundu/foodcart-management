<x-layouts::app title="Purchases & Restocking">
    <div class="space-y-6" x-data="{ newPurchaseModal: false, items: [{ food_id: '', item_name: '', quantity: 1, unit: 'kg', unit_price: 0 }] }">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-[var(--fc-border)]">
            <div>
                <h1 class="text-2xl font-black text-[var(--fc-text)]">Purchases & Restocking</h1>
                <p class="text-xs text-[var(--fc-text-muted)] mt-0.5">Record raw materials, meat, spices, beverages, and packaging orders</p>
            </div>

            <button
                type="button"
                @click="newPurchaseModal = true"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[var(--fc-primary)] text-[var(--fc-primary-text)] font-bold text-xs shadow-sm hover:opacity-95"
            >
                <flux:icon name="plus" class="size-4" />
                <span>Record New Purchase</span>
            </button>
        </div>

        <!-- Purchases Table -->
        <div class="fc-card overflow-hidden shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-start">
                    <thead class="bg-[var(--fc-bg)] border-b border-[var(--fc-border)] text-[var(--fc-text-muted)] uppercase tracking-wider font-semibold">
                        <tr>
                            <th class="py-3 px-4 text-start">PO Number</th>
                            <th class="py-3 px-4 text-start">Date</th>
                            <th class="py-3 px-4 text-start">Supplier</th>
                            <th class="py-3 px-4 text-start">Items</th>
                            <th class="py-3 px-4 text-end">Total Cost (৳)</th>
                            <th class="py-3 px-4 text-end">Paid (৳)</th>
                            <th class="py-3 px-4 text-end">Due (৳)</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--fc-border)]">
                        @forelse($purchases as $purchase)
                            <tr class="hover:bg-[var(--fc-bg)]/40 transition-colors">
                                <td class="py-3 px-4 font-bold text-[var(--fc-primary)]">
                                    <a href="{{ route('purchases.show', $purchase) }}">{{ $purchase->purchase_number }}</a>
                                </td>
                                <td class="py-3 px-4 text-[var(--fc-text-muted)]">
                                    {{ $purchase->purchase_date->format('d M Y') }}
                                </td>
                                <td class="py-3 px-4 font-bold text-[var(--fc-text)]">
                                    {{ $purchase->supplier->name }}
                                </td>
                                <td class="py-3 px-4 text-[var(--fc-text-muted)] max-w-xs truncate">
                                    {{ $purchase->items->pluck('item_name')->implode(', ') }}
                                </td>
                                <td class="py-3 px-4 text-end font-bold text-[var(--fc-text)]">
                                    ৳{{ number_format($purchase->total_amount, 2) }}
                                </td>
                                <td class="py-3 px-4 text-end font-medium text-emerald-600">
                                    ৳{{ number_format($purchase->paid_amount, 2) }}
                                </td>
                                <td class="py-3 px-4 text-end font-bold {{ $purchase->due_amount > 0 ? 'text-red-500' : 'text-[var(--fc-text-muted)]' }}">
                                    ৳{{ number_format($purchase->due_amount, 2) }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded font-bold uppercase text-[10px] {{ $purchase->payment_status === 'paid' ? 'bg-emerald-500/10 text-emerald-600' : ($purchase->payment_status === 'partial' ? 'bg-amber-500/10 text-amber-600' : 'bg-red-500/10 text-red-600') }}">
                                        {{ $purchase->payment_status }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-end">
                                    <a href="{{ route('purchases.show', $purchase) }}" class="p-1.5 rounded border border-[var(--fc-border)] text-[var(--fc-text)] hover:text-[var(--fc-primary)]">
                                        <flux:icon name="eye" class="size-3.5 inline" />
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-8 text-center text-xs text-[var(--fc-text-muted)]">No purchase records found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-[var(--fc-border)]">
                {{ $purchases->links() }}
            </div>
        </div>

        <!-- New Purchase Modal -->
        <div x-show="newPurchaseModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/60 backdrop-blur-xs" @click="newPurchaseModal = false"></div>
            <div class="relative w-full max-w-2xl fc-card p-6 shadow-2xl z-10 max-h-[90vh] overflow-y-auto space-y-4">
                <div class="flex items-center justify-between border-b border-[var(--fc-border)] pb-3">
                    <h3 class="font-bold text-base text-[var(--fc-text)]">Record New Stock Purchase</h3>
                    <button type="button" @click="newPurchaseModal = false" class="text-[var(--fc-text-muted)]">
                        <flux:icon name="x-mark" class="size-5" />
                    </button>
                </div>

                <form method="POST" action="{{ route('purchases.store') }}" class="space-y-4">
                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-[var(--fc-text)] mb-1">Supplier *</label>
                            <select name="supplier_id" required class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs text-[var(--fc-text)] focus:border-[var(--fc-primary)] outline-none">
                                <option value="">Select Supplier</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}">{{ $supplier->name }} ({{ $supplier->phone }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[var(--fc-text)] mb-1">Purchase Date *</label>
                            <input type="date" name="purchase_date" value="{{ date('Y-m-d') }}" required class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs text-[var(--fc-text)] focus:border-[var(--fc-primary)] outline-none" />
                        </div>
                    </div>

                    <!-- Items Repeater -->
                    <div class="space-y-2 pt-2 border-t border-[var(--fc-border)]">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-bold text-[var(--fc-text)]">Purchased Items & Ingredients</label>
                            <button type="button" @click="items.push({ food_id: '', item_name: '', quantity: 1, unit: 'kg', unit_price: 0 })" class="text-xs font-bold text-[var(--fc-primary)] hover:underline">
                                + Add Another Item
                            </button>
                        </div>

                        <template x-for="(item, idx) in items" :key="idx">
                            <div class="p-3 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-bg)] grid grid-cols-1 sm:grid-cols-12 gap-2 items-center">
                                <div class="sm:col-span-4">
                                    <input type="text" :name="'items[' + idx + '][item_name]'" x-model="item.item_name" placeholder="Item (e.g. মুরগির মাংস, বাসমতি চাল)" required class="w-full px-2.5 py-1.5 rounded-lg border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs" />
                                </div>
                                <div class="sm:col-span-3">
                                    <select :name="'items[' + idx + '][food_id]'" x-model="item.food_id" class="w-full px-2 py-1.5 rounded-lg border border-[var(--fc-border)] bg-[var(--fc-card)] text-[11px]">
                                        <option value="">Link to Menu Item (Optional)</option>
                                        @foreach($foods as $f)
                                            <option value="{{ $f->id }}">{{ $f->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="sm:col-span-2">
                                    <input type="number" step="0.1" :name="'items[' + idx + '][quantity]'" x-model="item.quantity" placeholder="Qty" required class="w-full px-2 py-1.5 rounded-lg border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs text-center" />
                                </div>
                                <div class="sm:col-span-2">
                                    <input type="number" step="0.1" :name="'items[' + idx + '][unit_price]'" x-model="item.unit_price" placeholder="৳ Price" required class="w-full px-2 py-1.5 rounded-lg border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs text-end" />
                                </div>
                                <div class="sm:col-span-1 text-center">
                                    <input type="hidden" :name="'items[' + idx + '][unit]'" value="kg" />
                                    <button type="button" x-show="items.length > 1" @click="items.splice(idx, 1)" class="text-red-500 p-1">
                                        <flux:icon name="trash" class="size-4" />
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Payment details -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2 border-t border-[var(--fc-border)]">
                        <div>
                            <label class="block text-xs font-semibold text-[var(--fc-text)] mb-1">Payment Method</label>
                            <select name="payment_method" class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs">
                                <option value="cash">Cash</option>
                                <option value="bkash">bKash</option>
                                <option value="nagad">Nagad</option>
                                <option value="card">Bank / Card</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[var(--fc-text)] mb-1">Paid Amount (৳)</label>
                            <input type="number" step="0.01" name="paid_amount" placeholder="Leave blank if fully paid" class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs" />
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[var(--fc-text)] mb-1">Status</label>
                            <select name="payment_status" class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs">
                                <option value="paid">Fully Paid</option>
                                <option value="partial">Partial Payment</option>
                                <option value="due">Full Due / Credit</option>
                            </select>
                        </div>
                    </div>

                    <div class="pt-3 flex justify-end gap-3 border-t border-[var(--fc-border)]">
                        <button type="button" @click="newPurchaseModal = false" class="px-4 py-2 rounded-xl border border-[var(--fc-border)] text-xs font-bold text-[var(--fc-text-muted)]">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-[var(--fc-primary)] text-[var(--fc-primary-text)] font-bold text-xs shadow-md">
                            Record Purchase
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts::app>
