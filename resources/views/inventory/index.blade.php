<x-layouts::app title="Inventory & Stock Management">
    <div class="space-y-6" x-data="{ adjustModal: false, selectedFood: null, newStock: '' }">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-[var(--fc-border)]">
            <div>
                <h1 class="text-2xl font-black text-[var(--fc-text)]">Inventory & Stock Control</h1>
                <p class="text-xs text-[var(--fc-text-muted)] mt-0.5">Real-time food stock tracking, auto-deduction on orders, and low stock warnings</p>
            </div>

            @if(auth()->user()->isOwner())
                <div class="flex items-center gap-2">
                    <a href="{{ route('purchases.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[var(--fc-primary)] text-[var(--fc-primary-text)] font-bold text-xs shadow-sm hover:opacity-95">
                        <flux:icon name="truck" class="size-4" />
                        <span>Restock / Purchase</span>
                    </a>
                </div>
            @endif
        </div>

        <!-- Inventory KPI Badges -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="fc-card p-4 shadow-xs flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-[var(--fc-text-muted)]">Total Catalog Items</span>
                    <p class="text-2xl font-black text-[var(--fc-text)] mt-1">{{ $totalItemsCount }}</p>
                </div>
                <div class="p-2.5 rounded-xl bg-blue-500/10 text-blue-500">
                    <flux:icon name="archive-box" class="size-6" />
                </div>
            </div>

            <div class="fc-card p-4 shadow-xs flex items-center justify-between border-amber-500/30 bg-amber-500/5">
                <div>
                    <span class="text-xs font-bold text-amber-700 dark:text-amber-300">Low Stock Alert Items</span>
                    <p class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-1">{{ $lowStockCount }}</p>
                </div>
                <div class="p-2.5 rounded-xl bg-amber-500/20 text-amber-600">
                    <flux:icon name="exclamation-triangle" class="size-6" />
                </div>
            </div>

            <div class="fc-card p-4 shadow-xs flex items-center justify-between border-red-500/30 bg-red-500/5">
                <div>
                    <span class="text-xs font-bold text-red-700 dark:text-red-300">Out of Stock Items</span>
                    <p class="text-2xl font-black text-red-600 dark:text-red-400 mt-1">{{ $outOfStockCount }}</p>
                </div>
                <div class="p-2.5 rounded-xl bg-red-500/20 text-red-600">
                    <flux:icon name="x-circle" class="size-6" />
                </div>
            </div>
        </div>

        <!-- Stock Table -->
        <div class="fc-card overflow-hidden shadow-xs">
            <div class="p-4 border-b border-[var(--fc-border)] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <h2 class="font-bold text-sm text-[var(--fc-text)]">Current Food Stock Table</h2>
                <div class="flex items-center gap-2">
                    <a href="{{ route('inventory.index', ['low_stock' => 1]) }}" class="px-3 py-1.5 rounded-lg border border-amber-500/30 bg-amber-500/10 text-amber-700 dark:text-amber-300 text-xs font-semibold hover:bg-amber-500/20">
                        Show Low Stock Only
                    </a>
                    @if(request('low_stock'))
                        <a href="{{ route('inventory.index') }}" class="text-xs text-[var(--fc-text-muted)] hover:underline">
                            Clear Filter
                        </a>
                    @endif
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-start">
                    <thead class="bg-[var(--fc-bg)] border-b border-[var(--fc-border)] text-[var(--fc-text-muted)] uppercase tracking-wider font-semibold">
                        <tr>
                            <th class="py-3 px-4 text-start">Item</th>
                            <th class="py-3 px-4 text-start">Category</th>
                            <th class="py-3 px-4 text-center">Unit</th>
                            <th class="py-3 px-4 text-center">Current Stock</th>
                            <th class="py-3 px-4 text-center">Min Threshold</th>
                            <th class="py-3 px-4 text-center">Stock Health</th>
                            <th class="py-3 px-4 text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--fc-border)]">
                        @forelse($foods as $food)
                            <tr class="hover:bg-[var(--fc-bg)]/40 transition-colors">
                                <td class="py-3 px-4 font-bold text-[var(--fc-text)]">
                                    {{ $food->name }}
                                    @if($food->bengali_name)
                                        <div class="text-[11px] font-normal text-[var(--fc-text-muted)]">{{ $food->bengali_name }}</div>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-[var(--fc-text-muted)]">
                                    {{ $food->category?->name }}
                                </td>
                                <td class="py-3 px-4 text-center font-medium text-[var(--fc-text-muted)]">
                                    {{ $food->unit }}
                                </td>
                                <td class="py-3 px-4 text-center font-black text-sm {{ $food->is_low_stock ? 'text-red-500' : 'text-emerald-600 dark:text-emerald-400' }}">
                                    {{ $food->current_stock }}
                                </td>
                                <td class="py-3 px-4 text-center text-[var(--fc-text-muted)]">
                                    {{ $food->min_stock }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @if($food->current_stock <= 0)
                                        <span class="px-2 py-0.5 rounded-full font-bold text-[10px] bg-red-500/10 text-red-600">OUT OF STOCK</span>
                                    @elseif($food->is_low_stock)
                                        <span class="px-2 py-0.5 rounded-full font-bold text-[10px] bg-amber-500/10 text-amber-600">LOW STOCK</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full font-bold text-[10px] bg-emerald-500/10 text-emerald-600">HEALTHY</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-end">
                                    <button
                                        type="button"
                                        @click="selectedFood = {{ json_encode($food) }}; newStock = '{{ $food->current_stock }}'; adjustModal = true;"
                                        class="px-2.5 py-1 rounded-lg border border-[var(--fc-border)] hover:border-[var(--fc-primary)] text-xs font-semibold text-[var(--fc-text)]"
                                    >
                                        Adjust
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-6 text-center text-xs text-[var(--fc-text-muted)]">No inventory records found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-[var(--fc-border)]">
                {{ $foods->links() }}
            </div>
        </div>

        <!-- Recent Inventory Transactions Ledger -->
        <div class="fc-card p-5 shadow-xs">
            <h2 class="font-bold text-sm text-[var(--fc-text)] mb-3">Recent Stock Transaction Log (Auto Deductions & Additions)</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-start">
                    <thead class="bg-[var(--fc-bg)] border-b border-[var(--fc-border)] text-[var(--fc-text-muted)] uppercase tracking-wider font-semibold">
                        <tr>
                            <th class="py-2.5 px-3 text-start">Date & Time</th>
                            <th class="py-2.5 px-3 text-start">Item</th>
                            <th class="py-2.5 px-3 text-start">Type</th>
                            <th class="py-2.5 px-3 text-center">Change Qty</th>
                            <th class="py-2.5 px-3 text-center">Closing Stock</th>
                            <th class="py-2.5 px-3 text-start">Notes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--fc-border)]">
                        @foreach($recentTransactions as $tx)
                            <tr class="hover:bg-[var(--fc-bg)]/40 transition-colors">
                                <td class="py-2.5 px-3 text-[var(--fc-text-muted)]">{{ $tx->created_at->format('d M Y, h:i A') }}</td>
                                <td class="py-2.5 px-3 font-bold text-[var(--fc-text)]">{{ $tx->food?->name }}</td>
                                <td class="py-2.5 px-3">
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded font-bold uppercase text-[10px] {{ $tx->type === 'sale' ? 'bg-blue-500/10 text-blue-600' : ($tx->type === 'purchase' ? 'bg-emerald-500/10 text-emerald-600' : ($tx->type === 'waste' ? 'bg-red-500/10 text-red-600' : 'bg-amber-500/10 text-amber-600')) }}">
                                        {{ $tx->type }}
                                    </span>
                                </td>
                                <td class="py-2.5 px-3 text-center font-bold {{ $tx->quantity < 0 ? 'text-red-500' : 'text-emerald-600' }}">
                                    {{ $tx->quantity > 0 ? '+' : '' }}{{ $tx->quantity }}
                                </td>
                                <td class="py-2.5 px-3 text-center font-black">{{ $tx->closing_stock }}</td>
                                <td class="py-2.5 px-3 text-[var(--fc-text-muted)] truncate max-w-xs">{{ $tx->notes ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Adjust Stock Modal -->
        <div x-show="adjustModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/60 backdrop-blur-xs" @click="adjustModal = false"></div>
            <div class="relative w-full max-w-sm fc-card p-6 shadow-2xl z-10 space-y-4">
                <div class="flex items-center justify-between border-b border-[var(--fc-border)] pb-3">
                    <h3 class="font-bold text-base text-[var(--fc-text)]">Adjust Stock Count</h3>
                    <button type="button" @click="adjustModal = false" class="text-[var(--fc-text-muted)]">
                        <flux:icon name="x-mark" class="size-5" />
                    </button>
                </div>

                <template x-if="selectedFood">
                    <form :action="'/inventory/' + selectedFood.id + '/adjust'" method="POST" class="space-y-3">
                        @csrf
                        <div>
                            <p class="font-bold text-xs text-[var(--fc-text)]" x-text="selectedFood.name"></p>
                            <p class="text-[11px] text-[var(--fc-text-muted)]" x-text="'Current Recorded: ' + selectedFood.current_stock + ' ' + selectedFood.unit"></p>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[var(--fc-text)] mb-1">New Physical Stock Count *</label>
                            <input type="number" step="1" name="current_stock" x-model="newStock" required class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs text-[var(--fc-text)] focus:border-[var(--fc-primary)] outline-none font-bold text-lg" />
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[var(--fc-text)] mb-1">Audit Reason / Notes</label>
                            <input type="text" name="notes" placeholder="e.g. End of night count, damaged pack" class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs text-[var(--fc-text)] focus:border-[var(--fc-primary)] outline-none" />
                        </div>

                        <div class="pt-2 flex justify-end gap-2">
                            <button type="button" @click="adjustModal = false" class="px-3 py-2 rounded-xl border border-[var(--fc-border)] text-xs font-bold text-[var(--fc-text-muted)]">
                                Cancel
                            </button>
                            <button type="submit" class="px-4 py-2 rounded-xl bg-[var(--fc-primary)] text-[var(--fc-primary-text)] text-xs font-bold shadow-sm">
                                Save Audit
                            </button>
                        </div>
                    </form>
                </template>
            </div>
        </div>
    </div>
</x-layouts::app>
