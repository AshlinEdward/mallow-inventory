<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LowStockController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'threshold' => ['sometimes', 'integer', 'min:0'],
        ]);

        $threshold = $validated['threshold'] ?? 10;

        $products = Product::where(
            'stock_on_hand',
            '<',
            $threshold
        )->orderBy('stock_on_hand')->get();

        return response()->json([
            'threshold' => $threshold,
            'count' => $products->count(),
            'products' => $products,
        ]);
    }
}
