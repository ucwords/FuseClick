<?php
namespace App\AdvSourceStorage;

use App\Models\OffersLook;
use App\Models\LocalOffer\Offer;

class offersLookStorage
{
    public static function save($conversion_result, $creative, $carrier)
    {
        /*********************自动与offerslook同步开始************************/
       if (in_array($conversion_result['offer']['advertiser_id'], [359, 530])) { //需要自动更新定的广告主
           $offer_id = self::saveOffer($conversion_result['offer']);
           $offer_info = Offer::where('id', $offer_id)
               ->select('offer_id', 'alive_status', 'offerslook_status', 'push_status', 'original_offer_id')->first();

           if ($offer_info['alive_status'] == 1 && $offer_info['push_status'] == 0) { //new offer

               $bool = self::runJob($conversion_result, $creative, $carrier);
               if ($bool) {
                   Offer::where('id', $offer_id)->update(['offerslook_status' => 1, 'offer_id' => $bool, 'push_status' => 1, 'post_ol_time' => date('Y-m-d H;i:s', time())]);
               }

               var_dump(date('Y-m-d H:i:s', time()).' 新增offer :' .$bool);

           } elseif($offer_info['alive_status'] == 1 && $offer_info['push_status'] == 1 && $offer_info['offerslook_status'] == 0) {  // 重启OL上offer
               $result = OffersLook::toChangOffersLookStatus($offer_info['offer_id'], 'active');
               $result_arr = anyToArray($result);
               if (isset($result_arr['code']) && $result_arr['code'] == 0) { //更新成功
                   Offer::where('id', $offer_id)->update(['offerslook_status' => 1]);
               }

               var_dump(date('Y-m-d H:i:s', time()).' 重启offer :' .$offer_info['offer_id']);

           } elseif ($offer_info['alive_status'] == 0 && $offer_info['push_status'] == 1 && $offer_info['offerslook_status'] == 1) { //停掉OL上offer
               $result = OffersLook::toChangOffersLookStatus($offer_info['offer_id'], 'paused');
               $result_arr = anyToArray($result);
               if (isset($result_arr['code']) && $result_arr['code'] == 0) { //更新成功
                   Offer::where('id', $offer_id)->update(['offerslook_status' => 0]);
               }
               var_dump(date('Y-m-d H:i:s', time()).' 停掉offer :' .$offer_info['offer_id']);
           } else {
               $result = OffersLook::offerPut($offer_info['offer_id'], json_encode($conversion_result));
               $result_arr = anyToArray($result);

               if (isset($result_arr[ 'code' ]) && $result_arr[ 'code' ] == 0) {
                   var_dump('to update offer: '.$offer_info['offer_id']);
               }
           }
           /*********************自动与offerslook同步结束************************/
       } else { //不需要自动更新的广告主走着里
           self::runJob($conversion_result, $creative, $carrier);
       }

    }

    public static function runJob($conversion_result, $creative, $carrier)
    {
        $exist = OffersLook::offerExist($conversion_result, $conversion_result[ 'offer' ][ 'advertiser_id' ]);
        $exist_arr = anyToArray($exist);

        fmtOut("OffersLook::offerExist Complete!" . $conversion_result[ 'offer' ][ 'name' ] . " Res:" . json_encode($exist_arr));
        if (isset($exist_arr[ 'code' ]) && $exist_arr[ 'code' ] == 0) {
            if ($exist_arr[ 'data' ][ 'totalRows' ] == 0) {
                $result = OffersLook::offerPost(json_encode($conversion_result));
                $offerslook_result = anyToArray($result);

                if (isset($offerslook_result[ 'code' ]) && $offerslook_result[ 'code' ] == 0) {
                    $offer_id = $offerslook_result[ 'data' ][ 'offer' ][ 'id' ];
                    fmtOut("Sync offerslook offer_id:${offer_id}");

                    if (isset($carrier) && !empty($carrier)) { //兼容CPA类型运营商模块 创建Carrier
                        foreach ($carrier as $item) {
                            $carrier_result = OffersLook::createCarrier($offer_id, json_encode(['name' => trim($item)]));
                            $carrier_result_arr = anyToArray($carrier_result);
                            if (isset($carrier_result['code']) && $carrier_result['code'] == 0) {
                                fmtOut("Create a new carrier: ${item} for offer_id: ${offer_id} create Success!");
                            } else {
                                fmtOut("Create a new carrier: ${item} for offer_id: ${offer_id} create Error! result: ". json_encode($carrier_result_arr));
                            }
                        }
                    }

                    if (isset($creative[ 'thumbfile' ])) {
                        foreach ($creative[ 'thumbfile' ] as $thumbfile) {
                            $uploadThumbnail_result = OffersLook::uploadThumbnail($thumbfile[ 'local_path' ], $offer_id);
                            if (isset($uploadThumbnail_result[ 'code' ]) && $uploadThumbnail_result[ 'code' ] == 0) {
                                $offer_thumbnail_id = $uploadThumbnail_result[ 'data' ][ 'offer_thumbnail' ][ 'id' ];
                                fmtOut("Sync offerslook offer_id:${offer_id} offer_thumbnail create Success! Id:${offer_thumbnail_id}");
                            } else {
                                fmtOut('Sync offerslook offer_id:'.json_encode($uploadThumbnail_result).'offer_thumbnail create Error!');
                            }
                        }
                    }

                    if (isset($creative[ 'image' ])) {
                        foreach ($creative[ 'image' ] as $image) {
                            $upload_result = OffersLook::uploadCreative($image[ 'local_path' ], $offer_id);
                            if (isset($upload_result[ 'code' ]) && $upload_result[ 'code' ] == 0) {
                                $offer_upload_id = $upload_result[ 'data' ][ 'offer_creative' ][ 0 ][ 'id' ];
                                fmtOut("Sync offerslook offer_id:${offer_id} offer_creative create Success! Id:${offer_upload_id}");
                            } else {
                                fmtOut('Sync offerslook offer_id:'.json_encode($upload_result). 'offer_creative create Error!');
                            }
                        }
                    }
                    if (isset($creative[ 'file' ])) {
                        foreach ($creative[ 'file' ] as $file) {
                            $upload_result = OffersLook::uploadCreative($file[ 'local_path' ], $offer_id);

                            if (isset($upload_result[ 'code' ]) && $upload_result[ 'code' ] == 0) {
                                $offer_upload_id = $upload_result[ 'data' ][ 'offer_creative' ][ 0 ][ 'id' ];
                                fmtOut("Sync offerslook offer_id:${offer_id} offer_creative create Success! Id:${offer_upload_id}");
                            }
                        }
                    }

                    return $offer_id;
                } else {
                    fmtOut("OffersLook::offerPost :" . json_encode($result));

                    return false;
                }
            } else {
                $exist_offer_id = $exist_arr[ 'data' ][ 'rowset' ][ 0 ][ 'offer' ][ 'id' ];
                if ($exist_offer_id) {
                    $result = OffersLook::offerPut($exist_offer_id, json_encode($conversion_result));
                    $result_arr = anyToArray($result);

                    if (isset($result_arr[ 'code' ]) && $result_arr[ 'code' ] == 0) {

                        if (isset($carrier) && !empty($carrier)) { //兼容CPA类型运营商模块 创建Carrier
                            foreach ($carrier as $item) {
                                $carrier_result = OffersLook::createCarrier($exist_offer_id, json_encode(['name' => trim($item)]));
                                $carrier_result_arr = anyToArray($carrier_result);
                                if (isset($carrier_result['code']) && $carrier_result['code'] == 0) {
                                    fmtOut("Create a new carrier: ${item} for offer_id: ${exist_offer_id} create Success!");
                                } else {
                                    fmtOut("Create a new carrier: ${item} for offer_id: ${exist_offer_id} create Error! result: ". json_encode($carrier_result_arr));
                                }
                            }
                        }

                        return $exist_offer_id;
                    }
                    return false;
                }
                fmtOut("OffersLook::offerExist num :" . $exist_arr[ 'data' ][ 'totalRows' ] . " ${exist_offer_id} " . $conversion_result[ 'offer' ][ 'name' ]);
            }
        }
    }


    /**
     * @author Dyson
     * @description 新单子则添加到本地、默认alive_status = 1、offerslook_status和push_status = 0；
     * @param $data
     * @return bool
     * @time 2018/11/21 15:14
     */
    public static function saveOffer($data)
    {
        $offer_model = Offer::updateOrCreate([
            'advertiser_id' => $data[ 'advertiser_id' ],
            'original_offer_id' => $data[ 'advertiser_offer_id' ],
            'user_id' => 0,
        ],[
            'alive_status'=> 1,
            'title'=> $data[ 'name' ],
            'advertiser_id' => $data[ 'advertiser_id' ],
            'original_offer_name' => $data[ 'name' ],
            'original_offer_id' => $data[ 'advertiser_offer_id' ],
            'preview_url' => $data[ 'preview_url' ],
            'tracking_url' => $data[ 'destination_url' ],
            'description' => $data[ 'description' ],
            'revenue' => $data[ 'revenue' ],
            'revenue_type' => $data[ 'revenue_type' ],
            'payout' => $data[ 'payout' ],
            'payout_type' => $data[ 'payout_type' ],
            'currency' => empty($data[ 'currency' ]) ? 'USD' : $data[ 'currency' ],
            'start_time' => time(),
            'status' => $data[ 'status' ],
        ]);

        if ($offer_model) {
            return $offer_model->id;
        }
    }
}