<x-layouts::app :title="'Edit ' . $food->name">
    <div class="max-w-2xl mx-auto space-y-6">
        <div class="flex items-center gap-3 pb-2 border-b border-[var(--fc-border)]">
            <a href="{{ route('foods.index') }}" class="p-2 rounded-xl border border-[var(--fc-border)] text-[var(--fc-text-muted)] hover:bg-[var(--fc-bg)]">
                <flux:icon name="arrow-left" class="size-4" />
            </a>
            <div>
                <h1 class="text-2xl font-black text-[var(--fc-text)]">Edit Food Item: {{ $food->name }}</h1>
                <p class="text-xs text-[var(--fc-text-muted)] mt-0.5">Modify pricing, recipes, and inventory parameters</p>
            </div>
        </div>

        <div class="fc-card p-6 shadow-xs">
            <form method="POST" action="{{ route('foods.update', $food) }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-[var(--fc-text)] mb-1">Category *</label>
                        <select name="category_id" required class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs text-[var(--fc-text)] focus:border-[var(--fc-primary)] outline-none">
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id', $food->category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }} @if($category->bengali_name) ({{ $category->bengali_name }}) @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[var(--fc-text)] mb-1">Name (English) *</label>
                        <input type="text" name="name" value="{{ old('name', $food->name) }}" required class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs text-[var(--fc-text)] focus:border-[var(--fc-primary)] outline-none" />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[var(--fc-text)] mb-1">Bengali Name</label>
                        <input type="text" name="bengali_name" value="{{ old('bengali_name', $food->bengali_name) }}" class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs text-[var(--fc-text)] focus:border-[var(--fc-primary)] outline-none" />
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-[var(--fc-text)] mb-1">Selling Price (৳) *</label>
                        <input type="number" step="0.01" name="selling_price" value="{{ old('selling_price', $food->selling_price) }}" required class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs text-[var(--fc-text)] focus:border-[var(--fc-primary)] outline-none" />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[var(--fc-text)] mb-1">Cost Price (৳)</label>
                        <input type="number" step="0.01" name="cost_price" value="{{ old('cost_price', $food->cost_price) }}" class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs text-[var(--fc-text)] focus:border-[var(--fc-primary)] outline-none" />
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-[var(--fc-text)] mb-1">Current Stock</label>
                        <input type="number" name="current_stock" value="{{ old('current_stock', $food->current_stock) }}" class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs text-[var(--fc-text)] focus:border-[var(--fc-primary)] outline-none" />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[var(--fc-text)] mb-1">Min Stock Alert</label>
                        <input type="number" name="min_stock" value="{{ old('min_stock', $food->min_stock) }}" class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs text-[var(--fc-text)] focus:border-[var(--fc-primary)] outline-none" />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[var(--fc-text)] mb-1">Unit</label>
                        <input type="text" name="unit" value="{{ old('unit', $food->unit) }}" class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs text-[var(--fc-text)] focus:border-[var(--fc-primary)] outline-none" />
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[var(--fc-text)] mb-1">Description</label>
                    <textarea name="description" rows="3" class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs text-[var(--fc-text)] focus:border-[var(--fc-primary)] outline-none">{{ old('description', $food->description) }}</textarea>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $food->is_active) ? 'checked' : '' }} class="rounded text-[var(--fc-primary)]" />
                    <label for="is_active" class="text-xs font-semibold text-[var(--fc-text)]">Active & Available on POS Menu</label>
                </div>

                <div class="pt-3 border-t border-[var(--fc-border)] flex items-center justify-end gap-3">
                    <a href="{{ route('foods.index') }}" class="px-4 py-2 rounded-xl border border-[var(--fc-border)] text-xs font-bold text-[var(--fc-text-muted)] hover:bg-[var(--fc-bg)]">
                        Cancel
                    </a>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-[var(--fc-primary)] text-[var(--fc-primary-text)] font-extrabold text-xs shadow-md hover:opacity-95">
                        Update Food Item
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts::app>
