<?php

namespace App\Console\Commands;
use App\Models\User;
use Illuminate\Console\Command;

class AutoSuspendExpiredUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:auto-suspend-expired-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
   public function handle()
{
    $users = User::whereDate('subscription_end_at', '<', now())
                 ->where('status', 'active')
                 ->get();

    foreach ($users as $user) {

        $user->update([
            'status' => 'suspended',
            'suspension_reason' => 'انتهى الاشتراك تلقائيًا',
        ]);

        foreach ($user->stores as $store) {
            $store->update([
                'status' => 'suspended',
                'suspension_reason' => 'انتهى اشتراك مالك المتجر',
            ]);

            foreach ($store->accountants as $acc) {
                $acc->update([
                    'status' => 'suspended',
                    'suspension_reason' => 'انتهى اشتراك مالك المتجر',
                ]);
            }
        }
    }

    $this->info('تم إيقاف المستخدمين المنتهية اشتراكاتهم');
}

}
