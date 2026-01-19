<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Store;
use App\Models\Category;
use Illuminate\Http\Request;

class ProductController extends Controller
{
   public function index(Store $store, Request $request)
{
    $query = $store->products();

    // بحث بالاسم
    if ($request->filled('search')) {
        $query->where('name', 'LIKE', '%' . $request->search . '%');
    }

    // فلترة حسب القسم
    if ($request->filled('category_id')) {
        $query->where('category_id', $request->category_id);
    }

    // فلترة حسب الحالة
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    // ترتيب المنتجات بحيث تظهر المنتجات منخفضة المخزون أولاً
    $query->orderByRaw('quantity <= min_stock DESC')  // المنتجات الخطرة أولاً
          ->orderBy('quantity', 'asc');               // الأقل كمية قبل الأعلى

    // Pagination
    $products = $query->paginate(20)->withQueryString();

    // عدد المحذوفات
    $trashedCount = Product::onlyTrashed()
        ->where('store_id', $store->id)
        ->count();

    // الأقسام
    $categories = Category::where('store_id', $store->id)->get();

    return view('user.stores.products.index', compact('store', 'products', 'categories', 'trashedCount'));
}


    public function create(Store $store, Request $request)
{
    $categories = Category::where('store_id', $store->id)->get();

    // القسم المختار تلقائيًا
    $selectedCategory = $request->category_id;

    return view('user.stores.products.create', compact(
        'store',
        'categories',
        'selectedCategory'
    ));
}



    public function store(Request $request, Store $store)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price'       => 'required|numeric|min:0',
            'cost_price'  => 'nullable|numeric|min:0',
            'quantity'    => 'required|integer|min:0',
            'min_stock'   => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'status'      => 'required|in:active,inactive',
            'image'       => 'nullable|image|max:2048',
        ]);

        $slug = str()->slug($request->name);

        if (Product::where('store_id', $store->id)->where('slug', $slug)->exists()) {
            return back()->withErrors(['name' => 'اسم المنتج موجود مسبقًا في هذا المتجر'])->withInput();
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        Product::create([
            'store_id'    => $store->id,
            'user_id'     => auth()->id(),
            'category_id' => $request->category_id,
            'name'        => $request->name,
            'slug'        => $slug,
            'description' => $request->description,
            'price'       => $request->price,
            'cost_price'  => $request->cost_price,
            'quantity'    => $request->quantity,
            'min_stock'   => $request->min_stock ?? 1,
            'status'      => $request->status,
            'image'       => $imagePath,
        ]);

       if ($request->has('stay_here')) {
    return redirect()
        ->route('user.stores.products.create', $store->id)
        ->with('success', 'تم إضافة المنتج بنجاح، يمكنك إضافة منتج آخر');
}

return redirect()
    ->route('user.stores.products.index', $store->id)
    ->with('success', 'تم إضافة المنتج بنجاح');

    }

    public function edit(Store $store, Product $product)
{
    $categories = Category::where('store_id', $store->id)->get();

    return view('user.stores.products.edit', compact(
        'store',
        'product',
        'categories'
    ));
}



    public function update(Request $request, Store $store, Product $product)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price'       => 'required|numeric|min:0',
            'cost_price'  => 'nullable|numeric|min:0',
            'min_stock'   => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'status'      => 'required|in:active,inactive',
            'image'       => 'nullable|image|max:2048',
        ]);

        $slug = str()->slug($request->name);

        if (
            Product::where('store_id', $store->id)
                ->where('slug', $slug)
                ->where('id', '!=', $product->id)
                ->exists()
        ) {
            return back()->withErrors(['name' => 'اسم المنتج موجود مسبقًا في هذا المتجر'])->withInput();
        }

        $imagePath = $product->image;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        $product->update([
            'category_id' => $request->category_id,
            'name'        => $request->name,
            'slug'        => $slug,
            'description' => $request->description,
            'price'       => $request->price,
            'cost_price'  => $request->cost_price,

            'min_stock'   => $request->min_stock ?? 1,
            'status'      => $request->status,
            'image'       => $imagePath,
        ]);

        return redirect()->route('user.stores.products.index', $store->id)
                         ->with('success', 'تم تحديث المنتج بنجاح');
    }

    public function destroy(Store $store, Product $product)
    {
        $product->delete();

        return redirect()->route('user.stores.products.index', $store->id)
                         ->with('success', 'تم حذف المنتج');
    }

    public function trash(Store $store)
    {
        $products = Product::onlyTrashed()->where('store_id', $store->id)->get();
        return view('user.stores.products.trash', compact('store', 'products'));
    }

    public function restore(Store $store, $id)
    {
        $product = Product::onlyTrashed()
            ->where('store_id', $store->id)
            ->where('id', $id)
            ->firstOrFail();

        $product->restore();

        return redirect()->route('user.stores.products.trash', $store->id)
                         ->with('success', 'تم استرجاع المنتج');
    }

    public function forceDelete(Store $store, $id)
    {
        $product = Product::onlyTrashed()
            ->where('store_id', $store->id)
            ->where('id', $id)
            ->firstOrFail();

        $product->forceDelete();

        return redirect()->route('user.stores.products.trash', $store->id)
                         ->with('success', 'تم حذف المنتج نهائيًا');
    }

    public function toggleStatus(Store $store, Product $product)
    {
        $product->status = $product->status === 'active' ? 'inactive' : 'active';
        $product->save();

        return redirect()->route('user.stores.products.index', $store->id)
                         ->with('success', 'تم تحديث حالة المنتج');
    }
}
