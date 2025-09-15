<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Services\ExpenseAiService;
use App\Models\Expense;

class ExpenseAiController extends Controller
{
    protected $expenseAiService;

    public function __construct(ExpenseAiService $expenseAiService)
    {
        $this->expenseAiService = $expenseAiService;
    }

    /**
     * Process receipt upload with AI OCR
     */
    public function processReceipt(Request $request)
    {
        try {
            $request->validate([
                'receipt' => 'required|file|mimes:jpeg,png,jpg,pdf|max:10240', // 10MB max
                'use_ai' => 'nullable|string|in:0,1,true,false'
            ]);

            $user = Auth::user();
            $file = $request->file('receipt');
            $useAi = filter_var($request->input('use_ai', '1'), FILTER_VALIDATE_BOOLEAN);

            // Store the receipt file
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('receipts/' . $user->id, $fileName, 'public');

            $extractedData = null;
            if ($useAi) {
                // Process with AI
                $result = $this->expenseAiService->extractReceiptData(storage_path('app/public/' . $filePath));
                if ($result['success']) {
                    $extractedData = $result['data'];
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Receipt processed successfully',
                'file_path' => $filePath,
                'description' => $extractedData['description'] ?? '',
                'amount' => $extractedData['amount'] ?? '',
                'category' => $extractedData['category'] ?? '',
                'date' => $extractedData['date'] ?? date('Y-m-d'),
                'merchant' => $extractedData['merchant'] ?? ''
            ]);
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
        try {
            $request->validate([
                'description' => 'required|string|max:255',
                'amount' => 'nullable|numeric|min:0'
            ]);

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
     * Detect fraud in expense
     */
    public function detectFraud(Request $request)
    {
        try {
            $request->validate([
                'description' => 'required|string|max:255',
                'amount' => 'required|numeric|min:0',
                'category' => 'required|string|max:50',
                'date' => 'required|date'
            ]);

            $expenseData = $request->only(['description', 'amount', 'category', 'date']);
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

    /**
     * Get budget analysis
     */
    public function getBudgetAnalysis(Request $request)
    {
        try {
            $user = Auth::user();
            $month = $request->get('month', date('Y-m'));
            
            // Get user's budget for the month
            $budget = $user->preferences['monthly_budget'] ?? 0;
            
            // Get expenses for the month
            $expenses = Expense::where('user_id', $user->id)
                ->whereRaw('DATE_FORMAT(date, "%Y-%m") = ?', [$month])
                ->get()
                ->toArray();

            $result = $this->expenseAiService->analyzeBudget($expenses, $budget);

            return response()->json([
                'success' => $result['success'],
                'budget' => [
                    'total' => $budget,
                    'spent' => array_sum(array_column($expenses, 'amount')),
                    'remaining' => $budget - array_sum(array_column($expenses, 'amount')),
                    'percentage' => $budget > 0 ? (array_sum(array_column($expenses, 'amount')) / $budget) * 100 : 0
                ],
                'analysis' => $result['analysis']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error analyzing budget: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get expense analytics
     */
    public function getAnalytics(Request $request)
    {
        try {
            $user = Auth::user();
            $month = $request->get('month', date('Y-m'));
            
            // Get expenses for the month
            $expenses = Expense::where('user_id', $user->id)
                ->whereRaw('DATE_FORMAT(date, "%Y-%m") = ?', [$month])
                ->get()
                ->toArray();

            $result = $this->expenseAiService->generateInsights($expenses);

            // Format data for charts
            $categoryData = [];
            foreach ($result['insights']['category_breakdown'] as $category => $amount) {
                $categoryData[] = [
                    'name' => ucfirst(str_replace('-', ' ', $category)),
                    'amount' => $amount
                ];
            }

            $monthlyData = [];
            foreach ($result['insights']['monthly_trends'] as $month => $amount) {
                $monthlyData[] = [
                    'month' => date('M Y', strtotime($month . '-01')),
                    'amount' => $amount
                ];
            }

            return response()->json([
                'success' => true,
                'analytics' => [
                    'categories' => $categoryData,
                    'monthly' => $monthlyData,
                    'insights' => $result['insights']
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating analytics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Set monthly budget
     */
    public function setBudget(Request $request)
    {
        try {
            $request->validate([
                'amount' => 'required|numeric|min:0'
            ]);

            $user = Auth::user();
            $preferences = $user->preferences ?? [];
            $preferences['monthly_budget'] = $request->amount;
            $user->preferences = $preferences;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Budget set successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error setting budget: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export expense report
     */
    public function exportReport(Request $request, $format)
    {
        try {
            $user = Auth::user();
            $month = $request->get('month', date('Y-m'));
            
            $expenses = Expense::where('user_id', $user->id)
                ->whereRaw('DATE_FORMAT(date, "%Y-%m") = ?', [$month])
                ->get();

            if ($format === 'pdf') {
                return $this->exportPdf($expenses, $month);
            } elseif ($format === 'excel') {
                return $this->exportExcel($expenses, $month);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Unsupported format'
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error exporting report: ' . $e->getMessage()
            ], 500);
        }
    }

    private function exportPdf($expenses, $month)
    {
        // Simple PDF generation (in a real app, use a proper PDF library)
        $html = "<h1>Expense Report for {$month}</h1>";
        $html .= "<table border='1'><tr><th>Description</th><th>Amount</th><th>Category</th><th>Date</th></tr>";
        
        foreach ($expenses as $expense) {
            $html .= "<tr>";
            $html .= "<td>{$expense->description}</td>";
            $html .= "<td>\${$expense->amount}</td>";
            $html .= "<td>{$expense->category}</td>";
            $html .= "<td>{$expense->date}</td>";
            $html .= "</tr>";
        }
        
        $html .= "</table>";

        return response($html)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="expense-report.pdf"');
    }

    private function exportExcel($expenses, $month)
    {
        // Simple CSV export (in a real app, use a proper Excel library)
        $csv = "Description,Amount,Category,Date\n";
        
        foreach ($expenses as $expense) {
            $csv .= "\"{$expense->description}\",{$expense->amount},\"{$expense->category}\",{$expense->date}\n";
        }

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="expense-report.csv"');
    }
}
