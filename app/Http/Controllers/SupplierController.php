<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(Request $request): View
    {
        $query = Supplier::withCount('purchases');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('contact_person', 'like', "%{$search}%");
        }

        $suppliers = $query->latest()->paginate(15)->withQueryString();

        $totalDues = (float) Supplier::sum('balance_due');
        $totalPurchased = (float) Supplier::sum('total_purchased');

        return view('suppliers.index', compact('suppliers', 'totalDues', 'totalPurchased'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'products_supplied' => ['nullable', 'string', 'max:500'],
        ]);

        Supplier::create($validated);

        return back()->with('success', 'Supplier added successfully.');
    }

    public function show(Supplier $supplier): View
    {
        $supplier->load(['purchases.items']);

        return view('suppliers.show', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'products_supplied' => ['nullable', 'string', 'max:500'],
            'status' => ['nullable', 'string', 'in:active,inactive'],
        ]);

        $supplier->update($validated);

        return back()->with('success', 'Supplier updated successfully.');
    }
}
