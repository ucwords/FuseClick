<?php

namespace App\Http\Controllers;

use App\Http\Traits\unitTest;
use App\Http\Traits\ApiConversion AS ApiConversion;

/**
 * Class commonSyncController
 * @package App\Http\Controllers
 * 标准化接入adv source
 */
class commonSyncController extends offerApiController
{
    use ApiConversion, unitTest;
    public $config_list = [];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $config_list = $this->config_list;
        foreach ($config_list as $conf_key) {
            fmtOut("Call:" . $conf_key);
            $this->api_config = config($conf_key);
            $this->runJob();
        }
    }


    public function runJob()
    {
        $conversion_field = $this->api_config[ 'conversion_field' ];
        $offer_api = $this->api_config[ 'offer_api' ];
        $result = self::getApi($offer_api, []);
        //dd($result);
        self::unitTestBoolean($this->api_config, 'api_offers') AND dd($result);   //return true 则打印广告册offer list
        if (isset($result[ 'code' ]) && $result[ 'code' ] == 0) {
            if (!empty($result[ 'data' ])) {
                fmtOut("Api offers get complete");

                $offers = closure_function_run($this->api_config[ 'offer_list' ], $result[ 'data' ]);

                self::unitTestBoolean($this->api_config, 'offer_list') AND dd($offers);

                if (is_array($offers)) {

                    fmtOut("Get Offers Item :" . count($offers));
                    foreach ($offers as $offers_item) {
                        self::unitTestBoolean($this->api_config, 'offers_item') AND dd($offers_item);

                        if (isset($this->api_config[ 'offer_filter' ])) {  //设置过滤条件
                            $filter_result = true;
                            $this->dataFilter($offers_item, $this->api_config[ 'offer_filter' ], $filter_result);
                            if ($filter_result == false) {
                                continue;
                            }
                        }
                        $conversion_result = [];
                        $this->conversionValue($offers_item, $conversion_field, $conversion_result);

                        $creative = $this->getCreative($offers_item);  //素材处理
                        $carrier = $this->getCarrier($offers_item);   //运营商处理

                        self::unitTestBoolean($this->api_config, 'conversion_value_creative') AND dd($conversion_result, $creative, $carrier);
                        if (isset($this->api_config[ 'storage' ])) {
                            $storage_class = $this->api_config[ 'storage' ];   //设置存储落脚点
                            $class_name = "App\\AdvSourceStorage\\" . $storage_class . "Storage";
                            $controller = app()->make($class_name);
                            app()->call([$controller, 'save'], [$conversion_result, $creative, $carrier]);

                        } else {
                            fmtOut("Without Offers Storage");
                        }
                        //循环一次
                    }
                } else {
                    fmtOut("offers get error");
                }

            }
        }
    }

    /**
     * @author Dyson
     * @description 处理素材
     * @param $item
     * @return mixed
     * @time 2018/11/21 14:24
     */
    public function getCreative($item)
    {
        if (isset($this->api_config[ 'conversion_creative' ])) {
            return closure_function_run($this->api_config[ 'conversion_creative' ], $item);
        }
    }


    /**
     * @author Dyson
     * @description 处理运营商
     * @param $item
     * @return mixed
     * @time 2018/11/21 14:52
     */
    public function getCarrier($item) {
        if (isset($this->api_config[ 'carrier' ])) {
            return closure_function_run($this->api_config[ 'carrier' ], $item);
        }
    }
}
