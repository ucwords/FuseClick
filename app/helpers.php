<?php
if (!function_exists('closure_function_run')) {
    function closure_function_run($closure)
    {
        $input = func_get_args();  //------获取一个函数的所有参数
        unset($input[ 0 ]);

        if (isset($closure) && !empty($closure) && $closure instanceof \Closure) {
            return call_user_func_array($closure, $input); //调用回调函数，并把一个数组参数作为回调函数的参数
        }
    }
}

if (!function_exists('anyToArray')) {
    function anyToArray($data)
    {
        if (is_string($data)) {
            $json_temp = json_decode($data, true);
            if (json_last_error() == JSON_ERROR_NONE) {
                return $json_temp;
            }

            return [$data];
        }
        if (is_array($data)) {
            return $data;
        }
        if (is_object($data)) {
            return (array)$data;
        }
    }
}
if (!function_exists('fmtOut')) {
    function fmtOut($str, $dm = 1)
    {
        //TODO
        if ($dm == 1) {
            echo date('Y-m-d H:i:s') . "|" . $str . PHP_EOL;
        }
        if ($dm == 3) {
            echo date('Y-m-d H:i:s') . "|" . $str . PHP_EOL;
            exit();
        }
    }
}
if (!function_exists('imageTypeByUrl')) {
    function imageTypeByUrl($url)
    {
        if (getUrlCode($url) != 200) {
            return false;
        }
        $result = getimagesize($url);
        switch ($result['mime']) {
            case "image/jpeg":
                return 'jpg';
                break;
            case "image/png":
                return 'png';
                break;

            default :
                return 'webp';
                break;
        }
        /*if (exif_imagetype($url) == IMAGETYPE_PNG) {
            return 'png';
        } elseif (exif_imagetype($url) == IMAGETYPE_JPEG) {
            return 'jpg';
        } else {
            return 'webp';
        }*/

    }
}

//解决exif_imagetype不存在
if ( ! function_exists( 'exif_imagetype' ) ) {
    function exif_imagetype ( $filename ) {
        if ( ( list($width, $height, $type, $attr) = getimagesize( $filename ) ) !== false ) {
            switch ($type) {
                case 2:
                    return 'jpg';
                    break;
                case 3:
                    return 'png';
                    break;

                default :
                    return false;
                    break;
            }
        }
    }
}

if (!function_exists('getUrlCode')) {
    function getUrlCode($url)
    {
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            $headers = get_headers($url);

            return substr($headers[ 0 ], 9, 3);
        } else {
            return 404;
        }
    }
}
if (!function_exists('fmtOfferName')) {
    function fmtOfferName($name)
    {
        $name = str_replace([';', ',', '&'], " ", $name);
        $name = str_replace(['(', ')'], ['', ''], $name);

        return $name;
    }
}