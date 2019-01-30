<?php
/**
 * Created by PhpStorm.
 * User: kontem
 * Date: 2018/1/11
 * Time: 22:59
 */

namespace App\Http\Traits;


trait unitTest
{
    public static function unitTestBoolean($config, $key)
    {
        if (isset($config[ 'unit_testing' ])) {
            $unit_testing_conf = $config[ 'unit_testing' ];
            if (isset($unit_testing_conf[ $key ])) {
                return (boolean)$unit_testing_conf[ $key ];
            }
        }
    }
}