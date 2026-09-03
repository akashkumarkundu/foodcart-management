<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFoodRequest;
use App\Models\Category;
use App\Models\Food;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class FoodController extends Controller
{
    public function index(Request $request): View
    {
        $query = Food::with('category');

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->filled('low_stock')) {
            $query->lowStock();
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('bengali_name', 'like', "%{$search}%");
            });
        }

        $sort = $request->input('sort', 'name');
        $direction = $request->input('direction', 'asc');
        $foods = $query->orderBy($sort, $direction)->paginate(16)->withQueryString();

        $categories = Category::orderBy('name')->get();

        return view('foods.index', compact('foods', 'categories'));
    }

    public function create(): View
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();

        return view('foods.create', compact('categories'));
    }

    public function store(StoreFoodRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = Str::slug($data['name']).'-'.Str::random(5);
        $data['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('foods', 'public');
            $data['image'] = '/storage/'.$path;
        }

        $food = Food::create($data);

        return redirect()->route('foods.index')
            ->with('success', "Food item '{$food->name}' created successfully!");
    }

    public function edit(Food $food): View
    {
        $categories = Category::orderBy('name')->get();

        return view('foods.edit', compact('food', 'categories'));
    }

    public function update(Request $request, Food $food): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'bengali_name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'preparation_time' => ['nullable', 'integer', 'min:1'],
            'current_stock' => ['nullable', 'integer', 'min:0'],
            'min_stock' => ['nullable', 'integer', 'min:0'],
            'unit' => ['nullable', 'string', 'max:50'],
            'is_active' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('foods', 'public');
            $validated['image'] = '/storage/'.$path;
        }

        $validated['is_active'] = $request->has('is_active');

        $food->update($validated);

        return redirect()->route('foods.index')
            ->with('success', "Food item '{$food->name}' updated successfully!");
    }

    public function updatePrice(Request $request, Food $food): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'selling_price' => ['required', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
        ]);

        $updateData = ['selling_price' => $validated['selling_price']];
        if (array_key_exists('cost_price', $validated) && $validated['cost_price'] !== null) {
            $updateData['cost_price'] = $validated['cost_price'];
        }

        $food->update($updateData);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "'{$food->name}' এর নতুন দাম ৳".number_format($food->selling_price, 2).' আপডেট হয়েছে!',
                'food' => [
                    'id' => $food->id,
                    'name' => $food->name,
                    'bengali_name' => $food->bengali_name,
                    'selling_price' => (float) $food->selling_price,
                    'cost_price' => (float) $food->cost_price,
                    'profit_per_item' => (float) $food->profit_per_item,
                    'profit_margin' => (float) $food->profit_margin,
                ],
            ]);
        }

        return back()->with('success', "'{$food->name}' এর দাম আপডেট করা হয়েছে (৳".number_format($food->selling_price, 2).')।');
    }

    public function toggleActive(Food $food): RedirectResponse
    {
        $food->update(['is_active' => ! $food->is_active]);

        $status = $food->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "{$food->name} has been {$status}.");
    }

    public function destroy(Food $food): RedirectResponse
    {
        $name = $food->name;
        $food->delete();

        return redirect()->route('foods.index')
            ->with('success', "Food item '{$name}' deleted successfully.");
    }
}
