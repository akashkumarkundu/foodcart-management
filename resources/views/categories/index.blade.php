<x-layouts::app title="Categories Management">
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-[var(--fc-border)]">
            <div>
                <h1 class="text-2xl font-black text-[var(--fc-text)]">Food Menu Categories</h1>
                <p class="text-xs text-[var(--fc-text-muted)] mt-0.5">Organize food items into fast-tap categories for the POS terminal</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Add New Category Form (1 Col) -->
            <div class="fc-card p-5 shadow-xs space-y-4">
                <h2 class="font-bold text-sm text-[var(--fc-text)] border-b border-[var(--fc-border)] pb-2">Add New Category</h2>
                <form method="POST" action="{{ route('categories.store') }}" class="space-y-3">
                    @csrf

                    <div>
                        <label class="block text-xs font-semibold text-[var(--fc-text)] mb-1">Category Name (English) *</label>
                        <input type="text" name="name" required placeholder="e.g. Bengali Food, Kebab" class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs text-[var(--fc-text)] focus:border-[var(--fc-primary)] outline-none" />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[var(--fc-text)] mb-1">Bengali Name</label>
                        <input type="text" name="bengali_name" placeholder="e.g. কাবাব, চা ও কফি" class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs text-[var(--fc-text)] focus:border-[var(--fc-primary)] outline-none" />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[var(--fc-text)] mb-1">Sort Display Order</label>
                        <input type="number" name="sort_order" value="0" class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs text-[var(--fc-text)] focus:border-[var(--fc-primary)] outline-none" />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[var(--fc-text)] mb-1">Description</label>
                        <textarea name="description" rows="2" class="w-full px-3 py-2 rounded-xl border border-[var(--fc-border)] bg-[var(--fc-card)] text-xs text-[var(--fc-text)] focus:border-[var(--fc-primary)] outline-none"></textarea>
                    </div>

                    <button type="submit" class="w-full py-2.5 rounded-xl bg-[var(--fc-primary)] text-[var(--fc-primary-text)] font-bold text-xs shadow-md hover:opacity-95">
                        Create Category
                    </button>
                </form>
            </div>

            <!-- Categories List (2 Cols) -->
            <div class="lg:col-span-2 fc-card overflow-hidden shadow-xs">
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-start">
                        <thead class="bg-[var(--fc-bg)] border-b border-[var(--fc-border)] text-[var(--fc-text-muted)] uppercase tracking-wider font-semibold">
                            <tr>
                                <th class="py-3 px-4 text-start">Order</th>
                                <th class="py-3 px-4 text-start">Name</th>
                                <th class="py-3 px-4 text-start">Bengali Name</th>
                                <th class="py-3 px-4 text-center">Items</th>
                                <th class="py-3 px-4 text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[var(--fc-border)]">
                            @forelse($categories as $category)
                                <tr class="hover:bg-[var(--fc-bg)]/40 transition-colors">
                                    <td class="py-3 px-4 font-bold text-[var(--fc-text-muted)]">
                                        #{{ $category->sort_order }}
                                    </td>
                                    <td class="py-3 px-4 font-bold text-[var(--fc-text)]">
                                        {{ $category->name }}
                                    </td>
                                    <td class="py-3 px-4 font-medium text-[var(--fc-text-muted)]">
                                        {{ $category->bengali_name ?? '-' }}
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="px-2 py-0.5 rounded-full bg-[var(--fc-bg)] border border-[var(--fc-border)] font-bold text-[11px]">
                                            {{ $category->foods_count }} items
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-end">
                                        <form method="POST" action="{{ route('categories.destroy', $category) }}" onsubmit="return confirm('Delete category?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded text-[var(--fc-text-muted)] hover:text-red-500">
                                                <flux:icon name="trash" class="size-3.5" />
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-6 text-center text-xs text-[var(--fc-text-muted)]">No categories found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-layouts::app>
