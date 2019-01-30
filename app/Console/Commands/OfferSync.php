<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use App\Models\HandleOffer;
use App\Models\OffersLook;

class OfferSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'offer:sync {adv}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'offer同步';

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
            if (isset($adv_config['commonSync']) && $adv_config['commonSync'] == true) {
                $controller_name = "App\\Http\\Controllers\\commonSyncController";
                $controller = app()->make($controller_name);
                $controller->config_list = ['advertiser.' . $adv_name];
                app()->call([$controller, 'index'], []);

            } else {
                $controller_name = "App\\Http\\Controllers\\${adv_name}Controller";
                $controller = app()->make($controller_name);
                app()->call([$controller, 'index'], []);
            }

        } else {
            die("Error adv source info");
        }

    }

}
