<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductSearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->query('query');

        // البحث عن المستخدم في أي من الـ Guards المتاحة (محاسب أو مدير)
        $user = Auth::guard('accountant')->user() ?: Auth::guard('web')->user();

        // التأكد من وجود مستخدم ومتجر
        if (!$user || !$user->store_id) {
            return response()->json([]);
        }

        return Product::where('store_id', $user->store_id)
            ->where('status', 1)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%$query%")
                  ->orWhere('barcode', 'like', "%$query%");
            })
            ->limit(10)
            ->get(['id', 'name', 'price', 'description'])
            ->map(function($product) {
                // تحويل السعر لعدد صحيح ليتناسب مع واجهة البيع السريع
                $product->price = (int) $product->price;
                return $product;
            });
    }
}
