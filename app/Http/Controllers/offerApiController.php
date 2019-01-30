<?php
/**
 * Created by PhpStorm.
 * User: kontem
 * Date: 2018/1/1
 * Time: 15:33
 */

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use App\Models\HttpCurl as HttpCurl;

class offerApiController extends BaseController
{
    public $api_config = null;
    public $geo = null;

    public static function fmtOut($code = 0, $message = null)
    {
        return ['code' => $code, 'message' => $message];
    }

    public function getApi($url, $http_params = null, $response_type = 'json')
    {
        $curl = new HttpCurl;
        if (isset($this->api_config[ 'http_basic_auth' ])) {
            //base64encode(username+":"+password)
            if (is_array($this->api_config[ 'http_basic_auth' ])) {
                list($username, $password) = $this->api_config[ 'http_basic_auth' ];
                $curl->setHeader([
                    'Authorization: Basic ' . base64_encode($username . ":" . $password)
                ]);
            }
        }
        if (isset($this->api_config[ 'set_header' ])) {
                $header = $this->api_config[ 'set_header' ];
                $curl->setHeader($header);
        }

        if (isset($this->api_config['setUserAgent'])) {
            //dd($this->api_config['setUserAgent']);
            $curl->setUserAgent($this->api_config['setUserAgent']);
        }
        if (isset($this->api_config[ 'set_cookie' ])) {
            $cookie = $this->api_config[ 'set_cookie' ];
            $curl->setCookie($cookie);
        }

        //offer_api_post_json
        if (isset($this->api_config[ 'offer_api_post_json' ]) && !empty($this->api_config[ 'offer_api_post_json' ])) {
            $data = $curl
                ->setParams($this->api_config[ 'offer_api_post_json' ])
                ->setTimeout(30)
                ->get($url, $response_type);
        } else {
            $data = $curl
                ->setParams($http_params)
                ->setTimeout(30)
                ->get($url, $response_type);
        }

        if ($data != false) {
            $result = self::fmtOut();
            $result[ 'data' ] = $data;

            return $result;
        } else {
            $result = self::fmtOut(4000, $curl->error_info);

            return $result;
        }
    }

    public static function postApi($url, $post_data)
    {

    }

    public static function offerConversion()
    {

    }
}