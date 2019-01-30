<?php
/**
 * Created by PhpStorm.
 * User: kontem
 * Date: 2018/1/1
 * Time: 15:40
 */

namespace App\Models;

use Storage;

class OffersLook
{
    public static function AuthorizationCode()
    {
        return base64_encode("leanmobi:8f3c3029ed374a18983f2cac8b6cfb91");
    }

    public static function geoFmt($data)
    {
        $target = [];
        foreach ($data as $item) {
            $target[ 'target' ][] = [
                "type"    => 2,
                "country" => $item,
            ];
        }

        return $target;
    }



    public static function offerGet()
    {
        $api_url = "http://leanmobi.api.offerslook.com/v1/offers";
        //$api_url = "http://leanmobiapi.api.offerslook.com/v1/offers";
        $curl = (new HttpCurl);
        //"authorization: Basic bGVhbm1vYmk6YjkwN2ViNTczODllNDI5Zjg1Njg2OTc2ZGNjMTQzNzQ="
        $is_success = $curl->setHeader([
            'content-type: application/json',
            'Authorization: Basic ' . self::AuthorizationCode(),
        ])->get($api_url);
        if ($is_success == false) {
            return $curl->error_info;
        }
    }

    public static function toChangOffersLookStatus($offer_id, $status)
    {
        if (!empty($offer_id)) {
            $url = 'http://leanmobiapi.api.offerslook.com/v1/batches/offers/'.$offer_id.'?status='.$status;
            $curl = new HttpCurl;
            $offer_to_change = $curl->setHeader([
                'content-type: application/json',
                'Authorization: Basic ' . self::AuthorizationCode(),
            ])->patch($url);
            if ($offer_to_change == false) {
                return $curl->error_info;
            } else {
                return $offer_to_change;
            }
        }

    }

    public static function offerPost($data)
    {
        //$api_url = "http://leanmobi.api.offerslook.com/v1/offers";
        $api_url = "http://leanmobiapi.api.offerslook.com/v1/offers";
        $curl = (new HttpCurl);
        $is_success = $curl->setHeader([
            'content-type: application/json',
            'Authorization: Basic ' . self::AuthorizationCode(),
        ])->setParams($data)->post($api_url);
        if ($is_success == false) {
            return $curl->error_info;
        } else {
            return $is_success;
        }
    }

    public static function offerPut($offer_id, $data)
    {
        $api_url = "http://leanmobi.api.offerslook.com/v1/offers/${offer_id}";
        //$api_url = "http://leanmobiapi.api.offerslook.com/v1/offers/${offer_id}";
        
        $curl = (new HttpCurl);
        $is_success = $curl->setHeader([
            'content-type: application/json',
            'Authorization: Basic ' . self::AuthorizationCode(),
        ])->setParams($data)->put($api_url);
        if ($is_success == false) {
            return $curl->error_info;
        } else {
            return $is_success;
        }
    }

    public static function offerExist($data, $adv_id = 18)
    {
        $api_url = "http://leanmobi.api.offerslook.com/v1/batches/offers";
        $curl = (new HttpCurl);

        $key = urlencode($data[ 'offer' ][ 'name' ]);
        //$name = urlencode("グランブルーファンタジー (Gran Blue Fantasy) -IOS-JP");  &filters[status]=active
        $query_str = "limit=1&fields=id,name,advertiser_id,status,revenue&contains=offer&contains=offer,offer_event&filters[advertiser_id]=${adv_id}&filters[name][EQUAL_TO]=${key}";
        $api_url = $api_url . "?" . $query_str;
        $is_success = $curl->setHeader([
            'content-type: application/json',
            'Authorization: Basic ' . self::AuthorizationCode(),
        ])->get($api_url);

        if ($is_success == false) {
            return $curl->error_info;
        } else {
            return $is_success;
        }
    }

    /**
     * @param $offer_id
     * @param $carrier  json字符串
     * @return bool|mixed
     */
    public static function createCarrier($offer_id, $carrier)
    {
        $api_url = 'http://leanmobi.api.offerslook.com/v1/offers/'.$offer_id.'/carriers';
        $curl = (new HttpCurl);
        $is_success = $curl->setHeader([
            'content-type: application/json',
            'Authorization: Basic ' . self::AuthorizationCode(),
        ])->setParams($carrier)->post($api_url);
        if ($is_success == false) {
            return $curl->error_info;
        } else {
            return $is_success;
        }
    }

    public static function uploadThumbnail($file_path, $offer_id)
    {
        $curl = curl_init();
        $api_url = "http://leanmobi.api.offerslook.com/v1/offers/${offer_id}/thumbnails";
        //$api_url = "http://leanmobiapi.api.offerslook.com/v1/offers/${offer_id}/thumbnails";

        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Authorization: Basic ' . self::AuthorizationCode(),
            "cache-control: no-cache",
        ));
        if ((version_compare(PHP_VERSION, '5.5') >= 0)) { //mime_content_type($file_path)
            $ext_image = getimagesize($file_path);
            //dd($ext_image);
            $aPost[ 'file' ] = new \CURLFile($file_path, $ext_image['mime'], basename($file_path));
            curl_setopt($curl, CURLOPT_SAFE_UPLOAD, true);
        } else {
            $aPost[ 'file' ] = "@" . $file_path;
        }
        //$aPost['file'] = $offer_id;
        //dd($aPost);
        curl_setopt($curl, CURLOPT_URL, $api_url);
        curl_setopt($curl, CURLOPT_TIMEOUT, 120);
        curl_setopt($curl, CURLOPT_BUFFERSIZE, 128);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $aPost);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        $status = curl_getinfo($curl);
        curl_close($curl);
        if (isset($status[ 'http_code' ]) && $status[ 'http_code' ] == 200) {
            $content = json_decode($response, true);
            if (json_last_error() == 0) {
                return $content;
            }

            return $content;
        } else {

            return false;
        }
    }

    public static function uploadCreative($file_path, $offer_id)
    {
        $curl = curl_init();
        $api_url = "http://leanmobi.api.offerslook.com/v1/offers/${offer_id}/creatives";
        //$api_url = "http://leanmobiapi.api.offerslook.com/v1/offers/${offer_id}/creatives";

        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Authorization: Basic ' . self::AuthorizationCode(),
            "cache-control: no-cache",
        ));
        if ((version_compare(PHP_VERSION, '5.5') >= 0)) {
            $ext_image = getimagesize($file_path);
            $aPost[ 'file' ] = new \CURLFile($file_path, $ext_image['mime'], basename($file_path));
            curl_setopt($curl, CURLOPT_SAFE_UPLOAD, true);
        } else {
            $aPost[ 'file' ] = "@" . $file_path;
        }
        curl_setopt($curl, CURLOPT_URL, $api_url);
        curl_setopt($curl, CURLOPT_TIMEOUT, 120);
        curl_setopt($curl, CURLOPT_BUFFERSIZE, 128);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $aPost);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        $status = curl_getinfo($curl);
        curl_close($curl);
        if (isset($status[ 'http_code' ]) && $status[ 'http_code' ] == 200) {
            $content = json_decode($response, true);
            if (json_last_error() == 0) {
                return $content;
            }

            return $content;
        } else {

            return false;
        }
    }

    public static function build_data_files($boundary, $fields = null, $files = null)
    {
        $data = '';
        $eol = "\r\n";

        $delimiter = '-------------' . $boundary;

        if (!empty($fields)) {
            foreach ($fields as $name => $content) {
                $data .= "--" . $delimiter . $eol
                    . 'Content-Disposition: form-data; name="' . $name . "\"" . $eol . $eol
                    . $content . $eol;
            }
        }

        if (!empty($files)) {
            foreach ($files as $name => $content) {
                $data .= "--" . $delimiter . $eol
                    . 'Content-Disposition: form-data; name="' . $name . '"; filename="' . $name . '"' . $eol
                    //. 'Content-Type: image/png'.$eol
                    . 'Content-Transfer-Encoding: binary' . $eol;

                $data .= $eol;
                $data .= $content . $eol;
            }
        }
        $data .= "--" . $delimiter . "--" . $eol;


        return $data;
    }
}