<?php
/*
return [
    'papaya'              => (include "advertiser/papaya.php"),
    'clickdealer_android' => (include "advertiser/clickdealer_android.php"),
    'clickdealer_ios'     => (include "advertiser/clickdealer_ios.php"),
    'newborntown'         => (include "advertiser/newborntown.php"),
    'tapgerine'           => (include "advertiser/tapgerine.php"),
    'promoadx'            => (include "advertiser/promoadx.php"),
    'sharpdsp'            => (include "advertiser/sharpdsp.php"),
    'mobusi'              => (include "advertiser/mobusi.php"),
    'rtbdemand'           => (include "advertiser/rtbdemand.php"),
    'avazu'               => (include "advertiser/avazu.php"),
    'mobhuge'             => (include "advertiser/mobhuge.php"),
    'xbaos'               => (include "advertiser/xbaos.php"),
    'marsclick'           => (include "advertiser/marsclick.php"),
];
*/
$path = __DIR__ . DIRECTORY_SEPARATOR . "advertiser";
$config = [];
use Symfony\Component\Finder\Finder;

foreach (Finder::create()->files()->name('*.php')->in($app->basePath('config/advertiser')) as $file) {
    //dd($file->getPathname());
    if (file_exists($file->getPathname())) {
        $config_key = $file->getBasename(".php");
        $config[ $config_key ] = (include $file->getPathname());
    }
}


return $config;
