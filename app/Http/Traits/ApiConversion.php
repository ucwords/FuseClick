<?php
namespace App\Http\Traits;


trait ApiConversion
{
    public function conversionValue($offers_item, $conversion_field, &$conversion_result)
    {
        //$conversion_result = [];
        foreach ($conversion_field as $conversion_key => $conversion_item) {
            if (is_array($conversion_item)) {
                foreach ($conversion_item as $node_key => $node_item) {
                    if (is_string($node_item)) {
                        $conversion_result[ $conversion_key ][ $node_key ] = $offers_item[ $node_item ];
                    } else {
                        $conversion_result[ $conversion_key ][ $node_key ] = closure_function_run($node_item, $offers_item);
                    }
                }
            }
        }
    }

    public function dataFilter($offers_item, $filter_conf, &$result) //$result 引用传递
    {
        if (is_array($offers_item)) {
            foreach ($filter_conf as $key => $item) {
                $result = closure_function_run($item, $offers_item);
                if ($result == false) {
                    return false;
                }
            }
        }

        return true;
    }
}