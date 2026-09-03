<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExpenseRequest;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    public function index(Request $request): View
    {
        $query = Expense::with(['category', 'user'])->latest('date');

        if ($request->filled('category_id')) {
            $query->where('expense_category_id', $request->category_id);
        }

        if ($request->filled('month')) {
            $month = Carbon::parse($request->month);
            $query->whereBetween('date', [$month->startOfMonth()->toDateString(), $month->endOfMonth()->toDateString()]);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('reference', 'like', "%{$search}%");
            });
        }

        $expenses = $query->paginate(15)->withQueryString();
        $categories = ExpenseCategory::withCount('expenses')->get();

        // Calculate totals
        $todayExpenses = (float) Expense::whereDate('date', today())->sum('amount');
        $thisMonthExpenses = (float) Expense::whereBetween('date', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])->sum('amount');
        $totalAllTime = (float) Expense::sum('amount');

        return view('expenses.index', compact(
            'expenses',
            'categories',
            'todayExpenses',
            'thisMonthExpenses',
            'totalAllTime'
        ));
    }

    public function store(StoreExpenseRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()?->id;

        if ($request->hasFile('receipt')) {
            $path = $request->file('receipt')->store('receipts', 'public');
            $data['receipt_path'] = '/storage/'.$path;
        }

        Expense::create($data);

        return back()->with('success', 'Expense recorded successfully.');
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        $expense->delete();

        return back()->with('success', 'Expense entry removed.');
    }
}
