<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePurchaseRequest;
use App\Models\Food;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Services\InventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PurchaseController extends Controller
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {}

    public function index(Request $request): View
    {
        $query = Purchase::with(['supplier', 'items.food', 'user'])->latest('purchase_date');

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('status')) {
            $query->where('payment_status', $request->status);
        }

        $purchases = $query->paginate(15)->withQueryString();
        $suppliers = Supplier::where('status', 'active')->orderBy('name')->get();
        $foods = Food::where('is_active', true)->orderBy('name')->get();

        return view('purchases.index', compact('purchases', 'suppliers', 'foods'));
    }

    public function store(StorePurchaseRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated, $request) {
            $totalAmount = 0.0;
            foreach ($validated['items'] as $item) {
                $totalAmount += ((float) $item['quantity']) * ((float) $item['unit_price']);
            }

            $paidAmount = isset($validated['paid_amount']) ? (float) $validated['paid_amount'] : 0.0;
            $dueAmount = max(0, $totalAmount - $paidAmount);
            $paymentStatus = $dueAmount == 0 ? 'paid' : ($paidAmount > 0 ? 'partial' : 'due');

            $purchase = Purchase::create([
                'supplier_id' => $validated['supplier_id'],
                'user_id' => $request->user()?->id,
                'purchase_number' => Purchase::generatePurchaseNumber(),
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'due_amount' => $dueAmount,
                'payment_status' => $paymentStatus,
                'payment_method' => $validated['payment_method'],
                'purchase_date' => $validated['purchase_date'],
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {
                $qty = (float) $item['quantity'];
                $unitPrice = (float) $item['unit_price'];
                $lineTotal = $qty * $unitPrice;

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'food_id' => $item['food_id'] ?? null,
                    'item_name' => $item['item_name'],
                    'quantity' => $qty,
                    'unit' => $item['unit'],
                    'unit_price' => $unitPrice,
                    'total_price' => $lineTotal,
                ]);

                // If associated with a menu item, auto-increase stock
                if (! empty($item['food_id'])) {
                    $food = Food::find($item['food_id']);
                    if ($food) {
                        $this->inventoryService->addStock(
                            food: $food,
                            quantity: $qty,
                            unitCost: $unitPrice,
                            reference: $purchase,
                            user: $request->user(),
                            notes: "Stock added via Purchase #{$purchase->purchase_number}"
                        );
                    }
                }
            }

            // Update supplier financial records
            $supplier = Supplier::find($validated['supplier_id']);
            if ($supplier) {
                $supplier->increment('total_purchased', $totalAmount);
                $supplier->increment('total_paid', $paidAmount);
                $supplier->increment('balance_due', $dueAmount);
            }
        });

        return back()->with('success', 'Purchase order recorded and inventory updated successfully!');
    }

    public function show(Purchase $purchase): View
    {
        $purchase->load(['supplier', 'items.food', 'user']);

        return view('purchases.show', compact('purchase'));
    }
}
