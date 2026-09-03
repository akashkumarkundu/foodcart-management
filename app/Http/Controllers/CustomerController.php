<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $query = Customer::withCount('orders');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $customers = $query->orderByDesc('total_spent')->paginate(15)->withQueryString();

        $totalCustomers = Customer::count();
        $totalLoyaltyPoints = Customer::sum('loyalty_points');
        $totalCustomerSpent = Customer::sum('total_spent');

        return view('customers.index', compact(
            'customers',
            'totalCustomers',
            'totalLoyaltyPoints',
            'totalCustomerSpent'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20', 'unique:customers,phone'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        Customer::create($validated);

        return back()->with('success', 'Customer profile registered successfully.');
    }

    public function show(Customer $customer): View
    {
        $customer->load(['orders.items.food', 'loyaltyTransactions']);

        // Find favorite food item
        $favoriteFood = $customer->orders()
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->select('order_items.food_name', DB::raw('SUM(order_items.quantity) as total_ordered'))
            ->groupBy('order_items.food_name')
            ->orderByDesc('total_ordered')
            ->first();

        return view('customers.show', compact('customer', 'favoriteFood'));
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20', 'unique:customers,phone,'.$customer->id],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $customer->update($validated);

        return back()->with('success', 'Customer updated successfully.');
    }
}
