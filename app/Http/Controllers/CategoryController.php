<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * عرض الأقسام الخاصة بمتجر معين
     */
    public function index(Store $store)
    {
        $categories = Category::where('store_id', $store->id)->get();
        $trashCount = Category::onlyTrashed()
            ->where('store_id', $store->id)
            ->count();

        return view('user.stores.categories.index', compact('store', 'trashCount', 'categories'));
    }

    /**
     * صفحة إضافة قسم أو نشاط
     */
    public function create(Store $store, Request $request)
    {
        // القيمة تأتي من الرابط: ?is_main_category=1 أو 0
        $is_main_category = $request->get('is_main_category', 0);

        return view('user.stores.categories.create', compact('store', 'is_main_category'));
    }

    /**
     * حفظ قسم جديد أو نشاط جديد
     */
    public function store(Request $request, Store $store)
    {
        $request->validate([
            'name'             => 'required|string|max:255',
            'description'      => 'nullable|string',
            'status'           => 'required|in:active,inactive',
            'is_main_category' => 'required|boolean',
        ]);

        Category::create([
            'name'             => $request->name,
            'description'      => $request->description,
            'status'           => $request->status,
            'store_id'         => $store->id,
            'user_id'          => auth()->id(),
            'is_main_category' => $request->is_main_category,
        ]);

        return redirect()
            ->route('user.stores.categories.index', $store->id)
            ->with('success', 'تم إضافة القسم بنجاح');
    }

    /**
     * صفحة تعديل قسم أو نشاط
     */
    public function edit(Store $store, Category $category)
    {
        if ($category->store_id != $store->id) {
            abort(403);
        }

        $is_main_category = $category->is_main_category;

        return view('user.stores.categories.edit', compact('store', 'category', 'is_main_category'));
    }

    /**
     * تحديث القسم أو النشاط
     */
    public function update(Request $request, Store $store, Category $category)
    {
        if ($category->store_id != $store->id) {
            abort(403);
        }

        $request->validate([
            'name'             => 'required|string|max:255',
            'description'      => 'nullable|string',
            'status'           => 'required|in:active,inactive',
            'is_main_category' => 'required|boolean',
        ]);

        $category->update([
            'name'             => $request->name,
            'description'      => $request->description,
            'status'           => $request->status,
            'is_main_category' => $request->is_main_category,
        ]);

        return redirect()
            ->route('user.stores.categories.index', $store->id)
            ->with('success', 'تم تحديث القسم بنجاح');
    }

    /**
     * عرض الأقسام المحذوفة (سلة المحذوفات)
     */
    public function trash(Store $store)
    {
        $categories = Category::onlyTrashed()
            ->where('store_id', $store->id)
            ->get();

        return view('user.stores.categories.trash', compact('store', 'categories'));
    }

    /**
     * استرجاع قسم محذوف
     */
    public function restore(Store $store, $id)
    {
        $category = Category::onlyTrashed()
            ->where('store_id', $store->id)
            ->where('id', $id)
            ->firstOrFail();

        $category->restore();

        return redirect()
            ->route('user.stores.categories.trash', $store->id)
            ->with('success', 'تم استرجاع القسم بنجاح');
    }

    /**
     * حذف نهائي
     */
    public function forceDelete(Store $store, $id)
    {
        $category = Category::onlyTrashed()
            ->where('store_id', $store->id)
            ->where('id', $id)
            ->firstOrFail();

        $category->forceDelete();

        return redirect()
            ->route('user.stores.categories.trash', $store->id)
            ->with('success', 'تم حذف القسم نهائيًا');
    }

    /**
     * تفعيل/تعطيل القسم
     */
    public function toggleStatus(Store $store, Category $category)
    {
        if ($category->store_id != $store->id) {
            abort(403);
        }

        $newStatus = $category->status === 'active' ? 'inactive' : 'active';
        $category->update(['status' => $newStatus]);

        if ($newStatus === 'inactive') {
            $category->products()->update(['status' => 'inactive']);
        }

        return redirect()
            ->route('user.stores.categories.index', $store->id)
            ->with('success', 'تم تحديث حالة القسم');
    }

    /**
     * حذف القسم (Soft Delete)
     */
    public function destroy(Store $store, Category $category)
    {
        if ($category->store_id != $store->id) {
            abort(403);
        }

        $category->delete();

        return redirect()
            ->route('user.stores.categories.index', $store->id)
            ->with('success', 'تم حذف القسم');
    }
}
