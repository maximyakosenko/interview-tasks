<?php

namespace App\Console\Commands;
use App\Models\Shop;
use App\Tracking\TrackingService\Logger;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Models\Charge;

class CancelExpiredFreeCharges extends Command
{
    use DispatchesJobs;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 's4commerce:cancel-expired-free-charges';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cancels expired free charges';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        ini_set('memory_limit', -1);

        /** @var \Illuminate\Database\Eloquent\Collection $shops */
        $shops = Shop::whereNotNull('free_usage_till')
            ->whereRaw('Date(free_usage_till) < NOW()')
            ->join('charges', 'charges.shop_id', '=', 'shops.id')
            ->where('charges.is_recurring', '=', 1)
            ->where('charges.status', '=', \ChargeHelper::STATUS_ACTIVE)
            ->get();

        $count = $shops->count();
        if($count > 0){
            foreach ( $shops as $shopData ) {
                $findShop = Shop::whereDomain($shopData->domain)->first();
                /** @var Charge $charge */
                $charge = $findShop->charges()->periodic()->activeOrTrial()->first();
                if(!empty($charge)){
                    $charge->cancel();
                    Logger::action("cancelExpiredFreeCharge", ['id' => $charge->id, 'shop_id' => $findShop->id, 'status' => $charge->status]);
                }
            }
        }
    }
}
