<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        $query = Payment::with(['order', 'customer'])->latest('payment_date');

        if ($request->filled('method')) {
            $query->where('payment_method', $request->method);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('transaction_id', 'like', "%{$search}%")
                    ->orWhere('reference', 'like', "%{$search}%")
                    ->orWhereHas('order', function ($oq) use ($search) {
                        $oq->where('order_number', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('date')) {
            $query->whereDate('payment_date', $request->date);
        }

        $payments = $query->paginate(15)->withQueryString();

        // Payment method breakdown totals
        $allPayments = Payment::where('status', 'completed')->get();
        $summary = [
            'total' => (float) $allPayments->sum('amount'),
            'cash' => (float) $allPayments->where('payment_method', 'cash')->sum('amount'),
            'bkash' => (float) $allPayments->where('payment_method', 'bkash')->sum('amount'),
            'nagad' => (float) $allPayments->where('payment_method', 'nagad')->sum('amount'),
            'rocket' => (float) $allPayments->where('payment_method', 'rocket')->sum('amount'),
            'card' => (float) $allPayments->where('payment_method', 'card')->sum('amount'),
        ];

        return view('payments.index', compact('payments', 'summary'));
    }
}
