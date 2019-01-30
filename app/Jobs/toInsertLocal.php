<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use DB;

class toInsertLocal implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $data = null;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($offer_arr)
    {
        $this->data = $offer_arr;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $offer_id = $this->data[ 'data' ][ 'offer' ][ 'id' ];
        $advertiser_id = $this->data[ 'data' ][ 'offer' ][ 'advertiser_id' ];
        $advertiser_offer_id = $this->data[ 'data' ][ 'offer' ][ 'advertiser_offer_id' ];

        #更新本地
        DB::table('handle_offer')->where('advertiser_id', $advertiser_id)->where('advertiser_offer_id', $advertiser_offer_id)->update([
                'offerslook_id' => $offer_id,
                'offerslook_status' => 1,
                'alive_status'  => 1,
                'update_time'   => date('Y-m-d H:i:s'),
            ]);

    }

}
