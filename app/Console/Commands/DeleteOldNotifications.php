<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DeleteOldNotifications extends Command
{
    // هذا الأمر مخصص لحذف جميع الإشعارات التي مضى عليها 20 يومًا.
    // يتم تشغيله يدويًا من لوحة التحكم أو من التيرمنال عند الحاجة.
    // لا يعتمد على الـ Scheduler ولا يعمل تلقائيًا.
    protected $signature = 'notifications:delete-old';

    protected $description = 'Delete notifications older than 20 days';

    public function handle()
    {
        DB::table('notifications')
            ->where('created_at', '<', now()->subDays(20))
            ->delete();

        $this->info('Old notifications deleted successfully.');
    }
}
