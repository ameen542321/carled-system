<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\Accountant;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class UserNotificationSendController extends Controller
{
    public function create()
    {
        $user = auth()->user();

        // المتاجر التابعة للمستخدم
        $stores = Store::where('user_id', $user->id)->get();

        // المحاسبون التابعون للمستخدم
        $accountants = Accountant::where('user_id', $user->id)->get();

        return view('notifications.send', compact('stores', 'accountants'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'target_type' => 'required|in:store,stores,accountant,accountants,admin',
            'target_ids'  => 'nullable|array',
            'title'       => 'required|string|max:255',
            'message'     => 'required|string|max:2000',
        ]);

        $user = auth()->user();

        // إرسال الإشعار
        NotificationService::send([
            'sender_id'   => $user->id,
            'sender_type' => 'user',
            'target_type' => $request->target_type,
            'target_ids'  => $request->target_ids,
            'title'       => $request->title,
            'message'     => $request->message,
        ]);

        return redirect()
            ->route('user.notifications.send')
            ->with('success', 'تم إرسال الإشعار بنجاح');
    }
}
