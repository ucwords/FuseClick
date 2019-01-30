<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

use App\Models\LocalOffer\FuseOffer;
use App\Models\FuseClick;

class AutoSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto:sync {adv}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'offer auto sync';

    /**
     * Create a new command instance.
     *
     * @return void
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
    public function handle()
    {
        $adv_name = $this->argument('adv');
        $adv_config = config('advertiser.' . $adv_name);

        if (!empty($adv_config)) {
            FuseOffer::where('advertiser_id', $adv_config['advertiser_id'])->update(['adv_status' => 0]); //runJob前 默认广告主侧的offer都会paused

            /*if (isset($adv_config['commonSync']) && $adv_config['commonSync'] == true) {
                $controller_name = "App\\Http\\Controllers\\commonSyncController";
                $controller = app()->make($controller_name);
                $controller->config_list = ['advertiser.' . $adv_name];
                app()->call([$controller, 'index'], []);

            } else {
                $controller_name = "App\\Http\\Controllers\\${adv_name}Controller";
                $controller = app()->make($controller_name);
                app()->call([$controller, 'index'], []);
            }*/

            #TODO 同步结束 同步offer状态
            $manger_adv_id = $adv_config['advertiser_id'];
            $update_local_offer_ids=[];

            $paused_offer_result = FuseOffer::where('advertiser_id',$manger_adv_id)
                ->where('adv_status', 0)
                ->select('fuse_offer_id', 'id')->orderBy('fuse_offer_id')
                ->chunk(100, function($offer) use (&$update_local_offer_ids){
                    foreach ($offer as $v) {
                        //$id[] = $v->fuse_offer_id;
                        $update_local_offer_ids[]=$v->fuse_offer_id;
                       // $origina_id[] = $v->id;
                    }
                    //$result = FuseClick::updateOfferStatus(implode(',', $id),  'Paused');

                });
            #TODO 根据$result 结果来判断 是否同步本地offer 状态
            if(!empty($update_local_offer_ids)){
                $result = FuseClick::updateOfferStatus($update_local_offer_ids,  'Paused');
                FuseOffer::whereIn('fuse_offer_id', $update_local_offer_ids)->update(['fuse_status' => 0]);
            }


        } else {
            die("Error adv source info");
        }

    }

}
