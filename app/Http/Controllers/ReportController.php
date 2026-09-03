<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Food;
use App\Models\Order;
use App\Models\Waste;
use App\Services\FinancialService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(
        protected FinancialService $financialService
    ) {}

    public function index(Request $request): View
    {
        return $this->sales($request);
    }

    public function sales(Request $request): View
    {
        $dates = $this->resolveDateRange($request);
        $summary = $this->financialService->getSummary($dates['start'], $dates['end']);

        $orders = Order::with(['customer', 'items.food', 'payments'])
            ->where('order_status', 'completed')
            ->whereBetween('created_at', [$dates['start'], $dates['end']])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $type = 'sales';

        return view('reports.sales', compact('orders', 'summary', 'dates', 'type'));
    }

    public function expenses(Request $request): View
    {
        $dates = $this->resolveDateRange($request);

        $expenses = Expense::with(['category', 'user'])
            ->whereBetween('date', [$dates['start']->toDateString(), $dates['end']->toDateString()])
            ->latest('date')
            ->paginate(20)
            ->withQueryString();

        $totalExpenses = (float) Expense::whereBetween('date', [$dates['start']->toDateString(), $dates['end']->toDateString()])->sum('amount');
        $type = 'expenses';

        return view('reports.expenses', compact('expenses', 'totalExpenses', 'dates', 'type'));
    }

    public function waste(Request $request): View
    {
        $dates = $this->resolveDateRange($request);

        $wastes = Waste::with(['food', 'user'])
            ->whereBetween('date', [$dates['start']->toDateString(), $dates['end']->toDateString()])
            ->latest('date')
            ->paginate(20)
            ->withQueryString();

        $totalWasteCost = (float) Waste::whereBetween('date', [$dates['start']->toDateString(), $dates['end']->toDateString()])->sum('estimated_cost');
        $type = 'waste';

        return view('reports.waste', compact('wastes', 'totalWasteCost', 'dates', 'type'));
    }

    public function inventory(Request $request): View
    {
        $foods = Food::with('category')
            ->orderBy('current_stock', 'asc')
            ->get();

        $totalValuation = (float) $foods->sum(fn ($f) => $f->current_stock * $f->cost_price);
        $lowStockCount = $foods->filter(fn ($f) => $f->is_low_stock)->count();
        $type = 'inventory';

        return view('reports.inventory', compact('foods', 'totalValuation', 'lowStockCount', 'type'));
    }

    /**
     * CSV Exporter for reports
     */
    public function exportCsv(Request $request, string $type): StreamedResponse
    {
        $dates = $this->resolveDateRange($request);
        $fileName = "foodcart360_{$type}_".$dates['start']->format('Ymd').'_to_'.$dates['end']->format('Ymd').'.csv';

        return response()->streamDownload(function () use ($type, $dates) {
            $handle = fopen('php://output', 'w');
            // Add UTF-8 BOM for Excel compatibility with Bengali text
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            if ($type === 'sales') {
                fputcsv($handle, ['Order #', 'Date', 'Customer', 'Payment Method', 'Subtotal (BDT)', 'Discount (BDT)', 'Total (BDT)', 'Status']);
                $orders = Order::with('customer')
                    ->where('order_status', 'completed')
                    ->whereBetween('created_at', [$dates['start'], $dates['end']])
                    ->lazy();

                foreach ($orders as $order) {
                    fputcsv($handle, [
                        $order->order_number,
                        $order->created_at->format('Y-m-d H:i'),
                        $order->customer?->name ?? 'Guest',
                        strtoupper($order->payment_method),
                        $order->subtotal,
                        $order->discount_amount,
                        $order->total_amount,
                        ucfirst($order->order_status),
                    ]);
                }
            } elseif ($type === 'expenses') {
                fputcsv($handle, ['Date', 'Category', 'Description', 'Payment Method', 'Amount (BDT)', 'Reference']);
                $expenses = Expense::with('category')
                    ->whereBetween('date', [$dates['start']->toDateString(), $dates['end']->toDateString()])
                    ->lazy();

                foreach ($expenses as $expense) {
                    fputcsv($handle, [
                        $expense->date->format('Y-m-d'),
                        $expense->category?->name ?? 'Other',
                        $expense->description,
                        strtoupper($expense->payment_method),
                        $expense->amount,
                        $expense->reference ?? '-',
                    ]);
                }
            } elseif ($type === 'waste') {
                fputcsv($handle, ['Date', 'Food Item', 'Quantity', 'Unit', 'Estimated Cost (BDT)', 'Reason', 'Notes']);
                $wastes = Waste::with('food')
                    ->whereBetween('date', [$dates['start']->toDateString(), $dates['end']->toDateString()])
                    ->lazy();

                foreach ($wastes as $waste) {
                    fputcsv($handle, [
                        $waste->date->format('Y-m-d'),
                        $waste->food?->name,
                        $waste->quantity,
                        $waste->unit,
                        $waste->estimated_cost,
                        ucfirst($waste->reason),
                        $waste->notes ?? '',
                    ]);
                }
            } elseif ($type === 'inventory') {
                fputcsv($handle, ['Food Item', 'Bengali Name', 'Category', 'Selling Price (BDT)', 'Cost Price (BDT)', 'Current Stock', 'Min Stock', 'Unit', 'Valuation (BDT)']);
                $foods = Food::with('category')->get();
                foreach ($foods as $food) {
                    fputcsv($handle, [
                        $food->name,
                        $food->bengali_name ?? '',
                        $food->category?->name ?? '',
                        $food->selling_price,
                        $food->cost_price,
                        $food->current_stock,
                        $food->min_stock,
                        $food->unit,
                        $food->current_stock * $food->cost_price,
                    ]);
                }
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    protected function resolveDateRange(Request $request): array
    {
        $range = $request->input('range', 'this_month');

        return match ($range) {
            'today' => [
                'start' => Carbon::today()->startOfDay(),
                'end' => Carbon::today()->endOfDay(),
                'label' => 'Today',
            ],
            'yesterday' => [
                'start' => Carbon::yesterday()->startOfDay(),
                'end' => Carbon::yesterday()->endOfDay(),
                'label' => 'Yesterday',
            ],
            'last_7_days' => [
                'start' => Carbon::now()->subDays(6)->startOfDay(),
                'end' => Carbon::now()->endOfDay(),
                'label' => 'Last 7 Days',
            ],
            'this_year' => [
                'start' => Carbon::now()->startOfYear(),
                'end' => Carbon::now()->endOfYear(),
                'label' => 'This Year ('.Carbon::now()->year.')',
            ],
            default => [
                'start' => Carbon::now()->startOfMonth(),
                'end' => Carbon::now()->endOfMonth(),
                'label' => 'This Month ('.Carbon::now()->format('F Y').')',
            ],
        };
    }
}
