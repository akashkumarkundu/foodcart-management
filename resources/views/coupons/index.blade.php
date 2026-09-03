<x-layouts::app title="Coupons Management">
    <div class="space-y-6" x-data="{ newCouponModal: false }">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-[var(--fc-border)]">
            <div>
                <h1 class="text-2xl font-black text-[var(--fc-text)]">Discount Coupons</h1>
                <p class="text-xs text-[var(--fc-text-muted)] mt-0.5">Create percentage and fixed discount coupons for POS checkout</p>
            </div>

            <button
                type="button"
                @click="newCouponModal = true"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[var(--fc-primary)] text-[var(--fc-primary-text)] font-bold text-xs shadow-sm hover:opacity-95"
            >
                <flux:icon name="plus" class="size-4" />
                <span>Create Coupon</span>
            </button>
        </div>

        <!-- Coupons Table -->
        <div class="fc-card overflow-hidden shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-start">
                    <thead class="bg-[var(--fc-bg)] border-b border-[var(--fc-border)] text-[var(--fc-text-muted)] uppercase tracking-wider font-semibold">
                        <tr>
                            <th class="py-3 px-4 text-start">Coupon Code</th>
                            <th class="py-3 px-4 text-start">Discount</th>
                            <th class="py-3 px-4 text-start">Min Order</th>
                            <th class="py-3 px-4 text-start">Max Discount</th>
                            <th class="py-3 px-4 text-center">Times Used</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--fc-border)]">
                        @forelse($coupons as $coupon)
                            <tr class="hover:bg-[var(--fc-bg)]/40 transition-colors">
                                <td class="py-3 px-4 font-black text-sm tracking-wider font-mono text-[var(--fc-primary)]">
                                    {{ $coupon->code }}
                                    @if($coupon->description)
                                        <div class="text-[10px] font-normal text-[var(--fc-text-muted)] font-sans">{{ $coupon->description }}</div>
                                    @endif
                                </td>
                                <td class="py-3 px-4 font-bold text-[var(--fc-text)]">
                                    {{ $coupon->discount_type === 'percentage' ? $coupon->discount_value . '%' : '৳' . number_format($coupon->discount_value, 2) }}
                                </td>
                                <td class="py-3 px-4 text-[var(--fc-text-muted)]">
                                    ৳{{ number_format($coupon->min_order_amount, 2) }}
                                </td>
                                <td class="py-3 px-4 text-[var(--fc-text-muted)]">
                                    {{ $coupon->max_discount_amount ? '৳' . number_format($coupon->max_discount_amount, 2) : 'No limit' }}
                                </td>
                                <td class="py-3 px-4 text-center font-bold">
                                    {{ $coupon->times_used }} {{ $coupon->usage_limit ? '/ ' . $coupon->usage_limit : '' }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span class="px-2 py-0.5 rounded-full font-bold text-[10px] uppercase {{ $coupon->is_active ? 'bg-emerald-500/10 text-emerald-600' : 'bg-zinc-500/10 text-zinc-500' }}">
                                        {{ $coupon->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-end space-x-1">
                                    <form method="POST" action="{{ route('coupons.toggle', $coupon) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="px-2 py-1 rounded border border-[var(--fc-border)] text-xs font-semibold text-[var(--fc-text-muted)] hover:text-[var(--fc-text)]">
                                            {{ $coupon->is_active ? 'Pause' : 'Activate' }}
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('coupons.destroy', $coupon) }}" onsubmit="return confirm('Delete coupon?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1 rounded text-[var(--fc-text-muted)] hover:text-red-500">
                                            <flux:icon name="trash" class="size-3.5" />
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-6 text-center text-xs text-[var(--fc-text-muted)]">No coupons created yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add Coupon Modal -->
        <div x-show="newCouponModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/60 backdrop-blur-xs" @click="newCouponModal = false"></div>
            <div class="relative w-full max-w-md fc-card p-6 shadow-2xl z-10 space-y-4">
                <div class="flex items-center justify-between border-b border-[var(--fc-border)] pb-3">
                    <h3 class="font-bold text-base text-[var(--fc-text)]">Create Promotional Coupon</h3>
                    <button type="button" @click="newCouponModal = false" class="text-[var(--fc-text-muted)]">
                        <flux:icon name="x-mark" class="size-5" />
                    </button>
                </div>

                <form method="POST" action="{{ route('coupons.store') }}" class="space-y-3">
                    @csrf

                    <div>
                        <label class="block text-xs font-semibold text-[var(--fc-text)] mb-1">Coupon Code *</label>
                        <input type="text" name="code" required placeholder="e.g. FOOD50, BURGER20" class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs font-mono font-bold uppercase focus:border-[var(--fc-primary)] outline-none" />
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-semibold text-[var(--fc-text)] mb-1">Discount Type *</label>
                            <select name="discount_type" class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs">
                                <option value="fixed">Fixed Amount (৳)</option>
                                <option value="percentage">Percentage (%)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[var(--fc-text)] mb-1">Discount Value *</label>
                            <input type="number" step="0.5" name="discount_value" required placeholder="e.g. 50 or 15" class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs font-bold" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-semibold text-[var(--fc-text)] mb-1">Minimum Order (৳)</label>
                            <input type="number" step="1" name="min_order_amount" value="0" class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[var(--fc-text)] mb-1">Max Cap (৳)</label>
                            <input type="number" step="1" name="max_discount_amount" placeholder="Optional" class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[var(--fc-text)] mb-1">Description</label>
                        <input type="text" name="description" placeholder="e.g. ৳50 discount on minimum order ৳500" class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs" />
                    </div>

                    <div class="pt-3 flex justify-end gap-2 border-t border-[var(--fc-border)]">
                        <button type="button" @click="newCouponModal = false" class="px-4 py-2 rounded-xl border border-[var(--fc-border)] text-xs font-bold text-[var(--fc-text-muted)]">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-[var(--fc-primary)] text-[var(--fc-primary-text)] font-bold text-xs shadow-md">
                            Save Coupon
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts::app>
