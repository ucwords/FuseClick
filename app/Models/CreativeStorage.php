<?php
/**
 * Created by PhpStorm.
 * User: kontem
 * Date: 2018/1/2
 * Time: 12:20
 */

namespace App\Models;

use Storage;

class CreativeStorage
{
    public static function save($name, $from)
    {
        if (filter_var($from, FILTER_VALIDATE_URL)) {
            //$url = "http://www.google.co.in/intl/en_com/images/srpr/logo1w.png";
            $contents = file_get_contents($from);
            //$name = substr($url, strrpos($url, '/') + 1);
            $path = Storage::put($name, $contents);
            if ($path == true && is_file(storage_path('app') . DIRECTORY_SEPARATOR . $name)) {
                return storage_path('app') . DIRECTORY_SEPARATOR . $name;
            }
        }

    }
}