<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $query = Order::with(['customer', 'user', 'items.food', 'latestPayment']);

        if ($request->filled('status')) {
            $query->where('order_status', $request->status);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($cq) use ($search) {
                        $cq->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $orders = $query->latest()->paginate(15)->withQueryString();

        return view('orders.index', compact('orders'));
    }

    public function show(Order $order): View
    {
        $order->load(['customer', 'user', 'items.food', 'payments', 'coupon']);

        return view('orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $request->validate([
            'order_status' => ['required', 'string', 'in:pending,preparing,ready,completed,cancelled'],
        ]);

        $status = $request->order_status;
        $order->update([
            'order_status' => $status,
            'completed_at' => $status === 'completed' ? now() : $order->completed_at,
        ]);

        return back()->with('success', "Order #{$order->order_number} status updated to ".ucfirst($status));
    }

    /**
     * Professional printable and thermal receipt invoice
     */
    public function invoice(Order $order): View
    {
        $order->load(['customer', 'user', 'items.food', 'payments', 'coupon']);

        $cartName = Setting::get('cart_name', 'রেশম নগরী বাইটস (Resham Nogori Bites)');
        $cartAddress = Setting::get('cart_address', 'টি-বাঁধ সংলগ্ন, পদ্মা গার্ডেন রোড, রাজশাহী');
        $cartPhone = Setting::get('cart_phone', '01712-345678');
        $cartEmail = Setting::get('cart_email', 'info@foodcart360.com');

        return view('orders.invoice', compact('order', 'cartName', 'cartAddress', 'cartPhone', 'cartEmail'));
    }
}
