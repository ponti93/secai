<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Expense;
use App\Services\ExpenseAiService;

class ExpenseController extends Controller
{
    protected $expenseAiService;

    public function __construct(ExpenseAiService $expenseAiService)
    {
        $this->expenseAiService = $expenseAiService;
    }

    /**
     * Display a listing of expenses
     */
    public function index()
    {
        $expenses = Expense::where('user_id', Auth::id())
            ->orderBy('expense_date', 'desc')
            ->get();

        return view('expenses.index', compact('expenses'));
    }

    /**
     * Show the form for creating a new expense
     */
    public function create()
    {
        return view('expenses.create');
    }

    /**
     * Store a new expense
     */
    public function store(Request $request)
    {
        $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'category' => 'required|string|max:50',
            'expense_date' => 'required|date',
            'status' => 'nullable|string|in:pending,approved,rejected',
            'notes' => 'nullable|string',
            'merchant' => 'nullable|string|max:255',
            'payment_method' => 'nullable|string|max:50',
            'receipt_number' => 'nullable|string|max:100'
        ]);

        Expense::create([
            'user_id' => Auth::id(),
            'description' => $request->description,
            'amount' => $request->amount,
            'tax_amount' => $request->tax_amount ?? 0,
            'category' => $request->category,
            'expense_date' => $request->expense_date,
            'status' => $request->status ?? 'pending',
            'notes' => $request->notes,
            'merchant' => $request->merchant,
            'payment_method' => $request->payment_method,
            'receipt_number' => $request->receipt_number
        ]);

        return redirect()->route('expenses.index')
            ->with('success', 'Expense created successfully!');
    }

    /**
     * Display the specified expense
     */
    public function show(Expense $expense)
    {
        if ($expense->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }
        return view('expenses.show', compact('expense'));
    }

    /**
     * Show the form for editing the specified expense
     */
    public function edit(Expense $expense)
    {
        if ($expense->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }
        return view('expenses.edit', compact('expense'));
    }

    /**
     * Update the specified expense
     */
    public function update(Request $request, Expense $expense)
    {
        if ($expense->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'category' => 'required|string|max:50',
            'expense_date' => 'required|date',
            'status' => 'nullable|string|in:pending,approved,rejected',
            'notes' => 'nullable|string',
            'merchant' => 'nullable|string|max:255',
            'payment_method' => 'nullable|string|max:50',
            'receipt_number' => 'nullable|string|max:100'
        ]);

        $expense->update([
            'description' => $request->description,
            'amount' => $request->amount,
            'tax_amount' => $request->tax_amount ?? 0,
            'category' => $request->category,
            'expense_date' => $request->expense_date,
            'status' => $request->status ?? 'pending',
            'notes' => $request->notes,
            'merchant' => $request->merchant,
            'payment_method' => $request->payment_method,
            'receipt_number' => $request->receipt_number
        ]);

        return redirect()->route('expenses.index')
            ->with('success', 'Expense updated successfully!');
    }

    /**
     * Remove the specified expense
     */
    public function destroy(Expense $expense)
    {
        if ($expense->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }
        $expense->delete();

        return redirect()->route('expenses.index')
            ->with('success', 'Expense deleted successfully!');
    }

    /**
     * Process receipt image and extract expense data
     */
    public function processReceipt(Request $request)
    {
        $request->validate([
            'receipt' => 'required|image|mimes:jpeg,png,jpg,gif|max:10240' // 10MB max
        ]);

        try {
            $result = $this->expenseAiService->extractReceiptData($request->file('receipt')->getPathname());
            
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'data' => $result['data']
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Failed to process receipt'
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error processing receipt: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Categorize expense using AI
     */
    public function categorizeExpense(Request $request)
    {
        $request->validate([
            'description' => 'required|string',
            'amount' => 'nullable|numeric'
        ]);

        try {
            $result = $this->expenseAiService->categorizeExpense(
                $request->description, 
                $request->amount
            );
            
            return response()->json([
                'success' => $result['success'],
                'category' => $result['category'],
                'confidence' => $result['confidence']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error categorizing expense: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get AI insights for expenses
     */
    public function getInsights()
    {
        try {
            $expenses = Expense::where('user_id', Auth::id())
                ->orderBy('expense_date', 'desc')
                ->get()
                ->toArray();

            $result = $this->expenseAiService->generateInsights($expenses);
            
            return response()->json([
                'success' => $result['success'],
                'insights' => $result['insights']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating insights: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Detect fraud in expense
     */
    public function detectFraud(Expense $expense)
    {
        if ($expense->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        try {
            $expenseData = [
                'description' => $expense->description,
                'amount' => $expense->amount,
                'category' => $expense->category,
                'date' => $expense->expense_date
            ];

            $result = $this->expenseAiService->detectFraud($expenseData);
            
            return response()->json([
                'success' => $result['success'],
                'is_fraud' => $result['is_fraud'],
                'risk_score' => $result['risk_score'],
                'reasons' => $result['reasons'],
                'recommendations' => $result['recommendations']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error detecting fraud: ' . $e->getMessage()
            ], 500);
        }
    }
}