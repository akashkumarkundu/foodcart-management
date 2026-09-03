<x-layouts::app title="Add New Food Item">
    <div class="max-w-2xl mx-auto space-y-6">
        <div class="flex items-center gap-3 pb-2 border-b border-[var(--fc-border)]">
            <a href="{{ route('foods.index') }}" class="p-2 rounded-xl border border-[var(--fc-border)] text-[var(--fc-text-muted)] hover:bg-[var(--fc-bg)]">
                <flux:icon name="arrow-left" class="size-4" />
            </a>
            <div>
                <h1 class="text-2xl font-black text-[var(--fc-text)]">Add New Food Item</h1>
                <p class="text-xs text-[var(--fc-text-muted)] mt-0.5">Register a food or beverage to your food cart menu</p>
            </div>
        </div>

        <div class="fc-card p-6 shadow-xs">
            <form method="POST" action="{{ route('foods.store') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf

                <!-- Category & Names -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-[var(--fc-text)] mb-1">Menu Category *</label>
                        <select name="category_id" required class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs text-[var(--fc-text)] focus:border-[var(--fc-primary)] outline-none">
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }} @if($category->bengali_name) ({{ $category->bengali_name }}) @endif
                                </option>
                            @endforeach
                        </select>
                        @error('category_id') <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[var(--fc-text)] mb-1">Food Name (English) *</label>
                        <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Beef Burger" class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs text-[var(--fc-text)] focus:border-[var(--fc-primary)] outline-none" />
                        @error('name') <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[var(--fc-text)] mb-1">Bengali Name</label>
                        <input type="text" name="bengali_name" value="{{ old('bengali_name') }}" placeholder="e.g. বিফ বার্গার" class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs text-[var(--fc-text)] focus:border-[var(--fc-primary)] outline-none" />
                    </div>
                </div>

                <!-- Prices -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-[var(--fc-text)] mb-1">Selling Price (৳) *</label>
                        <input type="number" step="0.01" name="selling_price" value="{{ old('selling_price') }}" required placeholder="e.g. 180" class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs text-[var(--fc-text)] focus:border-[var(--fc-primary)] outline-none" />
                        @error('selling_price') <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[var(--fc-text)] mb-1">Cost Price (৳)</label>
                        <input type="number" step="0.01" name="cost_price" value="{{ old('cost_price', 0) }}" placeholder="e.g. 100" class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs text-[var(--fc-text)] focus:border-[var(--fc-primary)] outline-none" />
                        <p class="text-[10px] text-[var(--fc-text-muted)] mt-1">Used to calculate exact profit per item</p>
                    </div>
                </div>

                <!-- Stock and Preparation Time -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-[var(--fc-text)] mb-1">Current Stock</label>
                        <input type="number" name="current_stock" value="{{ old('current_stock', 50) }}" class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs text-[var(--fc-text)] focus:border-[var(--fc-primary)] outline-none" />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[var(--fc-text)] mb-1">Minimum Alert Stock</label>
                        <input type="number" name="min_stock" value="{{ old('min_stock', 10) }}" class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs text-[var(--fc-text)] focus:border-[var(--fc-primary)] outline-none" />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[var(--fc-text)] mb-1">Unit</label>
                        <input type="text" name="unit" value="{{ old('unit', 'plate') }}" placeholder="plate, pcs, cup" class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs text-[var(--fc-text)] focus:border-[var(--fc-primary)] outline-none" />
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[var(--fc-text)] mb-1">Description / Ingredients</label>
                    <textarea name="description" rows="3" placeholder="Special sauce, toasted bun, grilled beef patty..." class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs text-[var(--fc-text)] focus:border-[var(--fc-primary)] outline-none">{{ old('description') }}</textarea>
                </div>

                <div class="pt-3 border-t border-[var(--fc-border)] flex items-center justify-end gap-3">
                    <a href="{{ route('foods.index') }}" class="px-4 py-2 rounded-xl border border-[var(--fc-border)] text-xs font-bold text-[var(--fc-text-muted)] hover:bg-[var(--fc-bg)]">
                        Cancel
                    </a>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-[var(--fc-primary)] text-[var(--fc-primary-text)] font-extrabold text-xs shadow-md hover:opacity-95">
                        Save Food Item
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts::app>
