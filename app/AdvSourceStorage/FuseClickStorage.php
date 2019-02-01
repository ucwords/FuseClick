<?php
namespace App\AdvSourceStorage;

use App\Models\FuseClick;
use App\Models\LocalOffer\FuseOffer;

class FuseClickStorage
{
    public static function save($conversion_result, $creative, $carrier)
    {
       //if (in_array($conversion_result['offer']['advertiser_id'], [5,6, 8])) { //需要自动更新定的广告主
           /*********************自动与FuseClick同步开始************************/

           $offer_id = self::saveOffer($conversion_result['offer']);
           $offer_info = FuseOffer::where('id', $offer_id)
               ->select('id', 'fuse_offer_id', 'adv_status', 'fuse_status', 'push_status', 'advertiser_offer_id')->first();

           if ($offer_info['adv_status'] == 1 && $offer_info['push_status'] == 0) { //new offer 需要push

               $bool = self::runJobs($conversion_result['offer'], $creative, $carrier, $offer_info);
               if ($bool) {
                   FuseOffer::where('id', $offer_id)->update(['fuse_status' => 1, 'fuse_offer_id' => $bool, 'push_status' => 1]);
                   fmtOut(date('Y-m-d H:i:s', time()).' 新增offer :' .$bool.' success of Adv '.$conversion_result['offer']['advertiser_id']);
               } else {
                   fmtOut(date('Y-m-d H:i:s', time()).' 新增offer :' .$bool.' error of Adv '.$conversion_result['offer']['advertiser_id']);
               }

           } elseif($offer_info['adv_status'] == 1 && $offer_info['push_status'] == 1 && $offer_info['fuse_status'] == 0) {  // 重启Fuse上offer

               $result = self::updateOfferStatus($offer_info['fuse_offer_id'], 'Active');

               if ($result) { //更新成功
                   FuseOffer::where('fuse_offer_id', $result)->update(['fuse_status' => 1]);
                   fmtOut(date('Y-m-d H:i:s', time()).' 重启offer: ' .$result.' success');
               } else {
                   fmtOut(date('Y-m-d H:i:s', time()).' 重启offer: ' .$result.' error');
               }

           } elseif ($offer_info['adv_status'] == 0 && $offer_info['push_status'] == 1 && $offer_info['fuse_status'] == 1) { //停掉Fuse上offer

               $result = self::updateOfferStatus($offer_info['fuse_offer_id'], 'Paused');
               if ($result) {
                   FuseOffer::where('fuse_offer_id', $result)->update(['fuse_status' => 0]);
                   fmtOut(date('Y-m-d H:i:s', time()).' Pause offer :' .$result.' success');
               } else {
                   fmtOut(date('Y-m-d H:i:s', time()).' Pause offer :' .$result.' error');
               }
           }

           /*********************自动与offerslook同步结束************************/
       //} else { //不需要自动更新的广告主走着里
           //$offer_info = ['fuse_offer_id' => 1];
           //self::runJobs($conversion_result['offer'], $creative, $carrier, $offer_info);
       //}

    }

    public static function runJobs($conversion_result, $creative, $carrier, $local_offer)
    {
        if ($local_offer['fuse_offer_id'] == 0) {  //数据库中的fuse offer id 默认为0  大于零则已经存在在fuseClick中
            #TODO setup_01 推送offer
            $post_result = FuseClick::offerPost($conversion_result);
            $post_result_arr = json_decode($post_result['result'], true);
            //$post_result_arr['httpStatus'] = 201;
            if ($post_result_arr['httpStatus'] == 201) {  //Created successfully!
                $offer_id_from_fuse = $post_result_arr['data'][0]['id'];  //fuse 返回的id
                fmtOut("Sync FuseClick create offer_id:${offer_id_from_fuse} Success!");
                FuseOffer::where('id', $local_offer['id'])->update(['fuse_status' => 1, 'push_status' => 1, 'fuse_offer_id' => intval($offer_id_from_fuse)]); //同步本地offer的两个状态
                #TODO setup_02 上传素材
                if (isset($creative['logo'])) {

                    foreach ($creative[ 'logo' ] as $image) {
                        $upload_result = FuseClick::uploadLogo($image[ 'local_path' ], $offer_id_from_fuse);

                        if (isset($upload_result[ 'httpStatus' ]) && $upload_result[ 'httpStatus' ] == 202) {
                            fmtOut("Sync FuseClick offer_id:${offer_id_from_fuse} offer_logo create Success!");
                        } else {
                            fmtOut('Sync FuseClick offer_id:'.json_encode($upload_result). ' offer_logo create Error!');
                        }
                    }
                }
                if (isset($creative['image'])) {
                    foreach ($creative[ 'image' ] as $image) {
                        $upload_result = FuseClick::uploadThumbnail($image[ 'local_path' ], $offer_id_from_fuse);
                        if (isset($upload_result[ 'httpStatus' ]) && $upload_result[ 'httpStatus' ] == 202) {
                            fmtOut("Sync FuseClick offer_id:${offer_id_from_fuse} offer_creative create Success! ");
                        } else {
                            fmtOut('Sync FuseClick offer_id:'.json_encode($upload_result). 'offer_creative create Error!');
                        }
                    }
                }

                return $offer_id_from_fuse;
            } else {
                fmtOut("Sync FuseClick create error ${post_result_arr}");
                return false;
            }

        } else {  //否则则为不需要自动更新

            $post_result = FuseClick::offerPost($conversion_result);
            $post_result_arr = json_decode($post_result['result'], true);
            if ($post_result_arr['httpStatus'] == 201) {  //Created successfully!

                $offer_id_from_fuse = $post_result_arr['data'][0]['id'];  //fuse 返回的id
                #TODO setup_01 上传素材
                if (isset($creative['image'])) {
                    foreach ($creative[ 'image' ] as $image) {
                        $upload_result = FuseClick::uploadThumbnail($image[ 'local_path' ], $offer_id_from_fuse);
                        $upload_result = anyToArray($upload_result);
                        if (isset($upload_result[ 'httpStatus' ]) && $upload_result[ 'httpStatus' ] == 202) {
                            $offer_upload_id = $upload_result[ 'data' ][ $offer_id_from_fuse ][ 0 ][ 'banner_id' ];
                            fmtOut("Sync FuseClick offer_id:${offer_id_from_fuse} offer_creative create Success! Id:${offer_upload_id}");
                        } else {
                            fmtOut('Sync FuseClick offer_id:'.json_encode($upload_result). 'offer_creative create Error!');
                        }
                    }
                }

            }
        }
    }


    /**
     * @author Dyson
     * @description 改变offer状态 Active/Pending/Paused/Archived
     * @param $offer_id
     * @time 2019/1/29 17:32
     */
    public static function updateOfferStatus($offer_id, $status)
    {
        $update_data = ['id' => $offer_id,'status' => $status];
        $post_result = FuseClick::offerPost($update_data);
        $post_result_arr = json_decode($post_result['result'], true);
        if ($post_result_arr['httpStatus'] == 202) {  //Update successfully!
            return $offer_id;
        } else {
            return false;
        }
    }



    /**
     * @author Dyson
     * @description 新单子则添加到本地、默认adv_status = 1、fuse_status和push_status = 0；
     * @param $data
     * @return bool
     * @time 2018/11/21 15:14
     */
    public static function saveOffer($data)
    {
        $offer_model = FuseOffer::updateOrCreate([
            'advertiser_id' => $data[ 'advertiser_id' ],
            'advertiser_offer_id' => $data[ 'advertiser_offer_id' ]
        ],[
            'name'=> $data[ 'name' ],
            'advertiser_id' => $data[ 'advertiser_id' ],
            'advertiser_offer_id' => $data[ 'advertiser_offer_id' ],
            'expire_date' => $data[ 'expire_date' ],
            'status' => $data[ 'status' ],
            'access_type' => $data[ 'access_type' ],
            'type' => $data[ 'type' ],
            'currency' => $data[ 'currency' ],
            'revenue_type' => $data[ 'revenue_type' ],
            'revenue' => $data[ 'revenue' ],
            'payout_type' => $data[ 'payout_type' ],
            'payout' => $data['payout'],
            'preview_url' => $data[ 'preview_url' ],
            'tracking_protocol' => $data[ 'tracking_protocol' ],
            'session_lifespan' => $data[ 'session_lifespan' ],
            'url' => $data[ 'url' ],
            'restriction' => $data[ 'restriction' ],
            'app_id' => $data[ 'app_id' ],
            'ssl' => $data[ 'ssl' ],
            'has_cap_limit' => $data[ 'has_cap_limit' ],
            'cap_type' => $data[ 'cap_type' ],
            'cap_event_range' => $data[ 'cap_event_range' ],
            'cap_overall_limit' => $data[ 'cap_overall_limit' ],
            'cap_interval' => $data[ 'cap_interval' ],
            'cap_interval_limit' => $data[ 'cap_interval_limit' ],
            'cap_affiliate_overall_limit' => isset($data[ 'cap_affiliate_overall_limit' ]) ? $data[ 'cap_affiliate_overall_limit' ] : 0,
            'cap_affiliate_interval' => isset($data[ 'cap_affiliate_interval' ]) ? $data[ 'cap_affiliate_interval' ] : 0,
            'cap_affiliate_interval_limit' => isset($data[ 'cap_affiliate_interval_limit' ]) ? $data[ 'cap_affiliate_interval_limit' ] : 0,
            'adv_status' => 1,
        ]);

        if ($offer_model) {
            return $offer_model->id;
        }
    }
}