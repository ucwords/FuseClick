<?php
use Illuminate\Support\Facades\Cache;
use App\Models\CreativeStorage;

return [
    'commonSync'          => true,                  //通用处理转化
    'storage'             => 'FuseClick',          //数据落地存储点
    'unit_testing'        => [                      //断点调试
        'api_offers'                => false,       //三方api获取情况 打印advertiser offer list
        'offer_list'                => false,       //offer data list
        'offers_item'               => false,       //single offer debug
        'conversion_value_creative' => false,       //数据转化格式后结果 与 素材采集结果  打印对接好的最终结果
    ],
    //'http_basic_auth'  => [],
    'offer_api'           => '',
    //'set_cookie'     =>    '',
    //'offer_api_post_json' => '',
   // 'set_header' => '',
    'creative_api'        => '',
    'advertiser_id'       => 8,
    'geo_api'             => '',
    'offer_list'          => function ($data) {   //接入广告主数据list
        return isset($data[ 'offers' ]) ? $data[ 'offers' ] : null;
    },
    'pay_out_rate'        => 0.8,
    'offer_filter'        => [    //数据过滤，return false 则跳过当前offer 可自定义过滤条件，如下所示
        'revenue' => function ($var) {
            return (float)$var[ 'price' ] < 0 ? false : true;
        },
        /*'offer_id'    => function ($var) {
            $arr = [6987,2754,7004,2030,6141,6090,7244,5499,6171,2602];
            if (!in_array($var['id'], $arr)) {
                return false;
            }
            return true;
        }*/
    ],
    'conversion_field'    => [
        'offer'          => [
            'advertiser_offer_id' => function ($var) {
                return $var['campid'];
            },
            'name'            => function ($var) {
                //AE|SA
                $name = fmtOfferName($var[ 'offer_name' ]);

                return isset($var[ 'offer_name' ]) ? $name : null;
            },
            'advertiser_id'   => function () {  //广告主id 与平台上id一致
                return 8;
            },
            'expire_date'        => function () {  //offer 过期时间 默认当前时间加一年
                return date('Y/m/d',strtotime("now") + 365 * 86400);
            },
            'status'          => function () { return "Active"; }, //Active/Pending/Paused/Archived

            'access_type'  => function () { return 'Need Approval'; },     //Public/Need Approval/Private

            'type'         => function() { return 'Mobile'; }, //Desktop/Mobile/Tablet

            'currency'        =>  function(){return 1;},   // 1=>USD
            'revenue_type'    => function () { return 'RPI'; },  //RPA/RPC/RPS/RPI/RPA+RPS/RPL
            'revenue'         => function ($var) {

                $payout = isset($var[ 'price' ]) ? (float)$var[ 'price' ] : 0;
                return $payout;
            },
            'payout_type'     => function () { return 'CPI'; }, //CPA/CPC/CPS/CPI/CPA+CPS/CPL
            'payout'          => function ($var) {
                $payout = isset($var[ 'price' ]) ? (float)$var[ 'price' ] : 0;

                //dd($payout);
                return round($payout * 1 * 0.8, 2);
            },
            'preview_url'     => function ($var) {  //若直接提供preview_url 则直接return。否则就拼接包名。
                /*if ($var[ 'os' ] == 'android') {
                    return 'https://play.google.com/store/apps/details?id=' . $var[ 'pkgname' ];
                }
                if ($var[ 'os' ] == 'ios') {
                    return 'https://itunes.apple.com/us/app/id' . $var[ 'pkgname' ];
                }*/

                return $var['preview_link'];
            },

            'tracking_protocol' => function() { return 'Server Postback URL'; },  //Server Postback URL/iFrame Pixel/Image Pixel
            'session_lifespan'   =>  function() { return '1 Week'; },  //1 Day/1 Week/2 Weeks/1 Month   点击和转化之前的时间，超过了设置的值后，转化会记录成rejected

            'url' => function ($var) { //tracking 	{TID}==click、 {AFFID} == aff id、 {SUB_AFFID} == sub id  {DEVICE_ID} == platform id
                 //方式一，替换广告主参数   &sub_aff_id={affid}_{sub_affid}
                $fill_vars = [
                    '[click_id]'      => '{TID}',
                    '[advertising_id]'      => '{DEVICE_ID}',
                    '[source]'     => '{AFFID}_{SUB_AFFID}'
                ];


                return str_replace(array_keys($fill_vars), array_values($fill_vars), $var[ 'tracking_link' ]);

                //方式二，自己拼接
                //return $var[ 'clkurl' ] . "&dv1={click_id}&nw_sub_aff={aff_id}_{source_id}";
            },
            'restriction'     => function($var) {  //offer KPI
                return $var['performance_criteria'];
            },
            /*'categories'     => function($var) {
                return $var['app_category'];
            },   //tag*/

            'app_id'          =>  function($var) {   //包名
                    return $var['app_id'];
            },
            'ssl'            => function() { return 0;}, //Enable SSL:0(by default): disable SSL 1: enable SSL.

            //=========================get setting======================
            'geo_targeting'  => function() { return 1;},
            'geo_type'       => function() { return 1;}, //1: include  0: exclude
            'geo_enforce'    => function() { return 1;}, //Enable Enforce Geo-targeting: 0(by default): OFF 1: ON

            'geo_countries'      => function ($var) { //匹配offer geo  geo_countries
                $countries = [];
                $geo_arr = explode(',', $var['geo']);

                foreach ($geo_arr as $k => $value) {
                    $countries[$k] = $value.':1';
                }

                return $countries;
            },

            //=========================cap setting======================
                //========= 上游侧设置===========
            'has_cap_limit'     => function() { return 1;},           //开启cap limit
            'cap_type'          => function() { return 'Conversion';},
            'cap_event_range'   => function() { return 'Initial';},    // All/Initial  针对多事件
            'cap_overall_limit' => function($var) {
                return $var['daily_cap'];
            },           //-1:no limit  总cap

            'cap_interval'      => function() { return 'Daily';},     //Daily/Weekly/Monthly
            'cap_interval_limit'  => function($var) {
                return $var['daily_cap'];
            },   //间隔 cap   需要cap_interval

                //========= 渠道侧设置===========
            'cap_affiliate_overall_limit'  => function($var) {
                return $var['daily_cap'];
            },   //-1:no limit  渠道总cap
            'cap_affiliate_interval'       =>  function() { return 'Daily';}, //Daily/Weekly/Monthly
            'cap_affiliate_interval_limit' =>  function($var) {
                return $var['daily_cap'];
            }, //间隔 cap  -1:no limit
            //=========================platform setting======================
            'device_targeting'  => function() {return 1;},
            'device_rules' => function($var) {
                $platform_type = $var['platform'];
                if($platform_type == 'Android') {
                    return $platform = [0 => '1:OS:Android'];
                } else {
                    return $platform = [0 => '1:OS:iOS'];
                }
            },

        ],
    ],

    'conversion_creative' => function ($item) {
        $creative = [];
        $icon_url = isset($item[ 'icon_links' ]) ? $item[ 'icon_links' ] : null;   //offer icon

        if (!empty($icon_url)) {
            $icon_url = str_replace('https', 'http', $icon_url);
            if (getUrlCode($icon_url) == 200) {
                //$file_name = basename($icon_url);
                $file_name = md5($icon_url) . "." . imageTypeByUrl($icon_url);
                $file_url = $icon_url;
                $creative[ 'thumbfile' ][] = [
                    'name'       => $file_name,
                    'url'        => $file_url,
                    'local_path' => CreativeStorage::save($file_name, $file_url),
                ];
            }
        }
        if (isset($item[ 'creatives' ]) && !empty($item[ 'creatives' ])) {  //offer creative
            $creatives = $item[ 'creatives' ];
            if (is_array($creatives)) {
                foreach ($creatives as $key => $fitem) {
                    $fitem[ 0 ] = str_replace('https', 'http', $fitem[ 0 ]);
                    if (getUrlCode($fitem[ 0 ]) != 200) {
                        continue;
                    }
                    //$file_name = basename($fitem[ 0 ]);
                    $file_name = md5($fitem[ 0 ]) . "." . imageTypeByUrl($fitem[ 0 ]);
                    $file_url = $fitem[ 0 ];
                    $creative[ 'image' ][] = [
                        'name'       => $file_name,
                        'url'        => $file_url,
                        'local_path' => CreativeStorage::save($file_name, $file_url),
                    ];
                }

            } elseif (is_string($creatives)) {
                //$file_name = basename($creatives);
                $file_name = md5($creatives) . "." . imageTypeByUrl($creatives);
                $file_url = $creatives;
                $creative[ 'image' ][] = [
                    'name'       => $file_name,
                    'url'        => $file_url,
                    'local_path' => CreativeStorage::save($file_name, $file_url),
                ];
            }
        }

        return $creative;
    },
    'carrier' => function ($item){
        $carrier_arr = isset($item['carrier']) ? $item['carrier'] : null ;
        return $carrier_arr;
    }
];
