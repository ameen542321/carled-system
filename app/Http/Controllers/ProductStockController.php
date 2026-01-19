<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Store;
use App\Models\StockMovement;
use Illuminate\Http\Request;

class ProductStockController extends Controller
{
    /**
     * صفحة إدارة المخزون
     */
    public function index(Store $store, Product $product)
    {
        // جلب سجل الحركات
        $movements = $product->stockMovements()
            ->orderBy('created_at', 'desc')
            ->get();

        return view('user.stores.products.stock.index', compact('store', 'product', 'movements'));
    }

    /**
     * زيادة المخزون
     */
    public function increase(Request $request, Store $store, Product $product)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
            'note'     => 'nullable|string|max:255',
        ]);

        // زيادة الكمية
        $product->quantity += $request->quantity;
        $product->save();

        // تسجيل حركة
        StockMovement::create([
            'store_id'   => $store->id,
            'product_id' => $product->id,
            'user_id'    => auth()->id(),
            'type'       => 'increase',
            'quantity'   => $request->quantity,
            'note'       => $request->note ?? 'زيادة مخزون',
        ]);

        return back()->with('success', 'تمت زيادة المخزون بنجاح');
    }

    /**
     * خصم المخزون
     */
    public function decrease(Request $request, Store $store, Product $product)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
            'note'     => 'nullable|string|max:255',
        ]);

        // حماية من خصم كمية أكبر من المتوفر
        if ($request->quantity > $product->quantity) {
            return back()->withErrors(['quantity' => 'لا يمكن خصم كمية أكبر من الكمية المتوفرة']);
        }

        // خصم الكمية
        $product->quantity -= $request->quantity;
        $product->save();

        // تسجيل حركة
        StockMovement::create([
            'store_id'   => $store->id,
            'product_id' => $product->id,
            'user_id'    => auth()->id(),
            'type'       => 'decrease',
            'quantity'   => $request->quantity,
            'note'       => $request->note ?? 'خصم مخزون',
        ]);

        return back()->with('success', 'تم خصم المخزون بنجاح');
    }
}
