<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryController extends Controller
{
    /**
     * Display a listing of inventory items
     */
    public function index()
    {
        $inventory = Inventory::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('inventory.index', compact('inventory'));
    }

    /**
     * Show the form for creating a new inventory item
     */
    public function create()
    {
        return view('inventory.create');
    }

    /**
     * Store a newly created inventory item
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sku' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:100',
            'quantity' => 'required|integer|min:0',
            'unit_price' => 'required|numeric|min:0',
            'min_quantity' => 'nullable|integer|min:0',
            'supplier' => 'nullable|string|max:255',
            'supplier_contact' => 'nullable|string|max:255',
        ]);

        Inventory::create([
            'user_id' => Auth::id(),
            'name' => $request->name,
            'description' => $request->description,
            'sku' => $request->sku,
            'category' => $request->category,
            'quantity' => $request->quantity,
            'unit_price' => $request->unit_price,
            'min_quantity' => $request->min_quantity ?? 0,
            'supplier' => $request->supplier,
            'supplier_contact' => $request->supplier_contact,
            'needs_reorder' => $request->quantity <= ($request->min_quantity ?? 0),
        ]);

        return redirect()->route('inventory.index')->with('success', 'Inventory item created successfully!');
    }

    /**
     * Display the specified inventory item
     */
    public function show(Inventory $item)
    {
        if ($item->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }
        
        return view('inventory.show', compact('item'));
    }

    /**
     * Show the form for editing the specified inventory item
     */
    public function edit(Inventory $item)
    {
        if ($item->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }
        
        return view('inventory.edit', compact('item'));
    }

    /**
     * Update the specified inventory item
     */
    public function update(Request $request, Inventory $item)
    {
        if ($item->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sku' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:100',
            'quantity' => 'required|integer|min:0',
            'unit_price' => 'required|numeric|min:0',
            'min_quantity' => 'nullable|integer|min:0',
            'supplier' => 'nullable|string|max:255',
            'supplier_contact' => 'nullable|string|max:255',
        ]);

        $updateData = [
            'name' => $request->name,
            'description' => $request->description,
            'sku' => $request->sku,
            'category' => $request->category,
            'quantity' => $request->quantity,
            'unit_price' => $request->unit_price,
            'min_quantity' => $request->min_quantity ?? 0,
            'supplier' => $request->supplier,
            'supplier_contact' => $request->supplier_contact,
            'needs_reorder' => $request->quantity <= ($request->min_quantity ?? 0),
        ];

        $item->update($updateData);

        return redirect()->route('inventory.index')->with('success', 'Inventory item updated successfully!');
    }

    /**
     * Remove the specified inventory item
     */
    public function destroy(Inventory $item)
    {
        if ($item->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $item->delete();
        return redirect()->route('inventory.index')->with('success', 'Inventory item deleted successfully!');
    }

    /**
     * Forecast demand using AI
     */
    public function forecastDemand(Request $request)
    {
        $request->validate([
            'period' => 'nullable|string|in:7 days,30 days,90 days',
            'confidence' => 'nullable|string|in:low,medium,high'
        ]);

        try {
            $aiService = new \App\Services\AIInventoryService();
            
            $options = [
                'period' => $request->period ?? '30 days',
                'confidence' => $request->confidence ?? 'medium'
            ];
            
            $result = $aiService->forecastDemand(Auth::id(), $options);
            
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error forecasting demand: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate reorder suggestions using AI
     */
    public function generateReorderSuggestions()
    {
        try {
            $aiService = new \App\Services\AIInventoryService();
            $result = $aiService->generateReorderSuggestions(Auth::id());
            
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating reorder suggestions: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Optimize pricing using AI
     */
    public function optimizePricing(Request $request)
    {
        $request->validate([
            'market_conditions' => 'nullable|string|in:stable,growing,declining,volatile',
            'profit_margin' => 'nullable|numeric|min:0|max:100',
            'competition' => 'nullable|string|in:low,medium,high'
        ]);

        try {
            $aiService = new \App\Services\AIInventoryService();
            
            $options = [
                'market_conditions' => $request->market_conditions ?? 'stable',
                'profit_margin' => $request->profit_margin ?? 20,
                'competition' => $request->competition ?? 'medium'
            ];
            
            $result = $aiService->optimizePricing(Auth::id(), $options);
            
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error optimizing pricing: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate inventory insights using AI
     */
    public function generateInsights()
    {
        try {
            $aiService = new \App\Services\AIInventoryService();
            $result = $aiService->generateInsights(Auth::id());
            
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating insights: ' . $e->getMessage()
            ], 500);
        }
    }
}
