<x-layouts::app title="Food Waste Management">
    <div class="space-y-6" x-data="{ newWasteModal: false }">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-[var(--fc-border)]">
            <div>
                <h1 class="text-2xl font-black text-[var(--fc-text)]">Food Waste & Spoilage Control</h1>
                <p class="text-xs text-[var(--fc-text-muted)] mt-0.5">Track burned, expired, or overproduced food to stop kitchen cost leakage</p>
            </div>

            <button
                type="button"
                @click="newWasteModal = true"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-red-600 text-white font-bold text-xs shadow-sm hover:bg-red-700 transition-colors"
            >
                <flux:icon name="plus" class="size-4" />
                <span>Log Food Waste</span>
            </button>
        </div>

        <!-- High Waste Warning Banner -->
        @if($isWasteHigh)
            <div class="p-4 rounded-xl border border-red-500/30 bg-red-500/10 text-red-700 dark:text-red-300 flex items-center justify-between text-xs">
                <div class="flex items-center gap-2">
                    <flux:icon name="exclamation-triangle" class="size-5 text-red-500 shrink-0" />
                    <span><strong>⚠️ Critical Waste Alert:</strong> Today's food waste cost has crossed ৳1,500. Review preparation batches immediately!</span>
                </div>
            </div>
        @endif

        <!-- Waste Cost Metrics -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="fc-card p-4 shadow-xs">
                <span class="text-xs font-semibold text-[var(--fc-text-muted)]">Today's Waste Cost</span>
                <p class="text-xl sm:text-2xl font-black text-red-600 dark:text-red-400 mt-1">৳{{ number_format($todayWasteCost, 2) }}</p>
            </div>

            <div class="fc-card p-4 shadow-xs">
                <span class="text-xs font-semibold text-[var(--fc-text-muted)]">Past 7 Days Waste</span>
                <p class="text-xl sm:text-2xl font-black text-red-600 dark:text-red-400 mt-1">৳{{ number_format($weeklyWasteCost, 2) }}</p>
            </div>

            <div class="fc-card p-4 shadow-xs">
                <span class="text-xs font-semibold text-[var(--fc-text-muted)]">This Month's Waste</span>
                <p class="text-xl sm:text-2xl font-black text-red-600 dark:text-red-400 mt-1">৳{{ number_format($monthlyWasteCost, 2) }}</p>
            </div>

            <div class="fc-card p-4 shadow-xs">
                <span class="text-xs font-semibold text-[var(--fc-text-muted)]">This Year's Waste</span>
                <p class="text-xl sm:text-2xl font-black text-red-600 dark:text-red-400 mt-1">৳{{ number_format($yearlyWasteCost, 2) }}</p>
            </div>
        </div>

        <!-- Waste Analytics Breakdown -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Highest Wasted Foods -->
            <div class="fc-card p-5 shadow-xs">
                <h2 class="font-bold text-sm text-[var(--fc-text)] mb-3">Highest Wasted Food Items (Cost Leakage)</h2>
                <div class="space-y-3">
                    @forelse($highestWastedFoods as $item)
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-semibold text-[var(--fc-text)]">{{ $item->name }}</span>
                            <span class="font-bold text-red-500">
                                {{ $item->total_qty }} lost • ৳{{ number_format($item->total_cost, 2) }}
                            </span>
                        </div>
                    @empty
                        <p class="text-xs text-[var(--fc-text-muted)] py-4 text-center">No recorded waste items.</p>
                    @endforelse
                </div>
            </div>

            <!-- Waste Reasons Breakdown -->
            <div class="fc-card p-5 shadow-xs">
                <h2 class="font-bold text-sm text-[var(--fc-text)] mb-3">Waste Cost by Root Cause</h2>
                <div class="space-y-3">
                    @forelse($wasteByReasons as $reason)
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-semibold capitalize text-[var(--fc-text)]">{{ $reason->reason }}</span>
                            <span class="font-bold text-[var(--fc-text)]">
                                {{ $reason->total_records }} incidents • ৳{{ number_format($reason->total_cost, 2) }}
                            </span>
                        </div>
                    @empty
                        <p class="text-xs text-[var(--fc-text-muted)] py-4 text-center">No waste records available.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Logged Wastes Table -->
        <div class="fc-card overflow-hidden shadow-xs">
            <div class="p-4 border-b border-[var(--fc-border)]">
                <h2 class="font-bold text-sm text-[var(--fc-text)]">Waste History Log (Auto Stock Deductions)</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-start">
                    <thead class="bg-[var(--fc-bg)] border-b border-[var(--fc-border)] text-[var(--fc-text-muted)] uppercase tracking-wider font-semibold">
                        <tr>
                            <th class="py-3 px-4 text-start">Date</th>
                            <th class="py-3 px-4 text-start">Food Item</th>
                            <th class="py-3 px-4 text-center">Quantity Lost</th>
                            <th class="py-3 px-4 text-start">Reason</th>
                            <th class="py-3 px-4 text-end">Est. Loss Cost (৳)</th>
                            <th class="py-3 px-4 text-start">Notes</th>
                            <th class="py-3 px-4 text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--fc-border)]">
                        @forelse($wastes as $waste)
                            <tr class="hover:bg-[var(--fc-bg)]/40 transition-colors">
                                <td class="py-3 px-4 text-[var(--fc-text-muted)]">
                                    {{ $waste->date->format('d M Y') }}
                                </td>
                                <td class="py-3 px-4 font-bold text-[var(--fc-text)]">
                                    {{ $waste->food?->name }}
                                </td>
                                <td class="py-3 px-4 text-center font-bold text-red-500">
                                    {{ $waste->quantity }} {{ $waste->unit }}
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded-full font-bold uppercase text-[10px] bg-red-500/10 text-red-600">
                                        {{ $waste->reason_label }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-end font-black text-red-600">
                                    ৳{{ number_format($waste->estimated_cost, 2) }}
                                </td>
                                <td class="py-3 px-4 text-[var(--fc-text-muted)] truncate max-w-xs">
                                    {{ $waste->notes ?? '-' }}
                                </td>
                                <td class="py-3 px-4 text-end">
                                    <form method="POST" action="{{ route('wastes.destroy', $waste) }}" onsubmit="return confirm('Remove waste log?');" class="inline">
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
                                <td colspan="7" class="py-8 text-center text-xs text-[var(--fc-text-muted)]">No food waste recorded. Great kitchen efficiency!</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-[var(--fc-border)]">
                {{ $wastes->links() }}
            </div>
        </div>

        <!-- Add Waste Modal -->
        <div x-show="newWasteModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/60 backdrop-blur-xs" @click="newWasteModal = false"></div>
            <div class="relative w-full max-w-md fc-card p-6 shadow-2xl z-10 space-y-4">
                <div class="flex items-center justify-between border-b border-[var(--fc-border)] pb-3">
                    <h3 class="font-bold text-base text-[var(--fc-text)]">Log Food Waste Incident</h3>
                    <button type="button" @click="newWasteModal = false" class="text-[var(--fc-text-muted)]">
                        <flux:icon name="x-mark" class="size-5" />
                    </button>
                </div>

                <form method="POST" action="{{ route('wastes.store') }}" class="space-y-3">
                    @csrf

                    <div>
                        <label class="block text-xs font-semibold text-[var(--fc-text)] mb-1">Food Item *</label>
                        <select name="food_id" required class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs focus:border-[var(--fc-primary)] outline-none">
                            <option value="">Select Wasted Food</option>
                            @foreach($foods as $f)
                                <option value="{{ $f->id }}">{{ $f->name }} ({{ $f->bengali_name }}) - Current Stock: {{ $f->current_stock }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-semibold text-[var(--fc-text)] mb-1">Quantity Lost *</label>
                            <input type="number" step="0.5" name="quantity" required placeholder="e.g. 2 or 5" class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs font-bold focus:border-[var(--fc-primary)] outline-none" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[var(--fc-text)] mb-1">Unit</label>
                            <input type="text" name="unit" value="plate" placeholder="plate, pcs, cup" class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[var(--fc-text)] mb-1">Reason for Waste *</label>
                        <select name="reason" required class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs font-medium">
                            <option value="overproduction">Overproduction (Unsold at end of shift)</option>
                            <option value="burned">Burned / Cooking Mishap</option>
                            <option value="expired">Expired / Past Date</option>
                            <option value="spoiled">Spoiled / Sour Taste</option>
                            <option value="damaged">Damaged during prep or transport</option>
                            <option value="customer_return">Customer Return</option>
                            <option value="other">Other Reason</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[var(--fc-text)] mb-1">Date *</label>
                        <input type="date" name="date" value="{{ date('Y-m-d') }}" required class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs" />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[var(--fc-text)] mb-1">Notes / Action Taken</label>
                        <input type="text" name="notes" placeholder="e.g. Left on high flame, oil overheat" class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs" />
                    </div>

                    <div class="pt-3 flex justify-end gap-2 border-t border-[var(--fc-border)]">
                        <button type="button" @click="newWasteModal = false" class="px-4 py-2 rounded-xl border border-[var(--fc-border)] text-xs font-bold text-[var(--fc-text-muted)]">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-red-600 text-white font-bold text-xs shadow-md hover:bg-red-700">
                            Log Waste & Deduct Stock
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts::app>
