<?php
/**
 * Created by PhpStorm.
 * User: win 10
 * Date: 2019/1/25
 * Time: 14:38
 */

namespace App\Models;

use Storage;

class FuseClick
{
    private static function OfferPostUrl()
    {
        return 'http://leanmobi.fuseclick.com/api/v2/setOffer?key=CC94A7587469463647D78EEFD52CBFE8';
    }

    private static function OfferCreateUrl($offer_id)
    {
        return 'http://leanmobi.fuseclick.com/api/v2/setOfferBanners?key=CC94A7587469463647D78EEFD52CBFE8&offer_id='.$offer_id;
    }

    public static function offerPost($data)
    {
        $api_url = self::OfferPostUrl();

        $curl = (new HttpCurl);
        $is_success = $curl->setHeader([
            "Content-Type: application/x-www-form-urlencoded",
        ])->setParams($data)->post($api_url);

        if ($is_success == false) {
            return $curl->error_info;
        } else {
            return $is_success;
        }
    }

    public static function uploadLogo($file_path, $offer_id)
    {
        $api_url = 'http://leanmobi.fuseclick.com/api/v1/setOfferLogo?key=CC94A7587469463647D78EEFD52CBFE8&offer_id='.$offer_id;//&offer_id='.$offer_id
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            "cache-control: no-cache",
            //"content-type: multipart/form-data; boundary=----WebKitFormBoundary7MA4YWxkTrZu0gW"
        ]);
        if ((version_compare(PHP_VERSION, '5.5') >= 0)) { //mime_content_type($file_path)
            $ext_image = getimagesize($file_path);
            //dd($ext_image);
            $aPost[ 'filename' ] = new \CURLFile($file_path, $ext_image['mime'], basename($file_path));
            curl_setopt($curl, CURLOPT_SAFE_UPLOAD, true);
        } else {
            $aPost[ 'filename' ] = "@" . $file_path;
        }

        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_URL, $api_url);
        curl_setopt($curl, CURLOPT_TIMEOUT, 120);
        curl_setopt($curl, CURLOPT_BUFFERSIZE, 128);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $aPost);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($curl);
        $status = curl_getinfo($curl);
        curl_close($curl);


        if (isset($status[ 'http_code' ]) && $status[ 'http_code' ] == 202) {
            $content = json_decode($response, true);
            return $content;
        } else {

            return false;
        }
    }

    public static function updateOfferStatus($offer_id, $status)
    {
        if (is_array($offer_id)) {
            foreach ($offer_id as $item) {
                $update_data = ['id' => $item,'status' => $status];
                $post_result = self::offerPost($update_data);

            }
        }

        /*$post_result_arr = json_decode($post_result['result'], true);
        $offer_id_from_fuse = $post_result_arr['data'][0]['id'];  //fuse 返回的id
        if ($post_result_arr['httpStatus'] == 201) {  //Update successfully!
            FuseOffer::where('fuse_offer_id', $offer_id_from_fuse)->update(['fuse_status' => 1, 'push_status' => 1]);
            fmtOut(date('Y-m-d H:i:s', time()).' 重启offer :' .$offer_id_from_fuse.' success');
        }*/
    }


    public static function uploadThumbnail($file_path, $offer_id)
    {
        $api_url = self::OfferCreateUrl($offer_id);

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            "cache-control: no-cache",
            //"content-type: multipart/form-data; boundary=----WebKitFormBoundary7MA4YWxkTrZu0gW"
        ]);
        if ((version_compare(PHP_VERSION, '5.5') >= 0)) { //mime_content_type($file_path)
            $ext_image = getimagesize($file_path);
            //dd($ext_image);
            $aPost[ 'file' ] = new \CURLFile($file_path, $ext_image['mime'], basename($file_path));
            curl_setopt($curl, CURLOPT_SAFE_UPLOAD, true);
        } else {
            $aPost[ 'file' ] = "@" . $file_path;
        }

        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_URL, $api_url);
        curl_setopt($curl, CURLOPT_TIMEOUT, 120);
        curl_setopt($curl, CURLOPT_BUFFERSIZE, 128);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $aPost);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($curl);
        $status = curl_getinfo($curl);
        curl_close($curl);

        if (isset($status[ 'http_code' ]) && $status[ 'http_code' ] == 202) {
            $content = json_decode($response, true);
            return $content;
        } else {

            return false;
        }

    }
}