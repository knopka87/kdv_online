<?php
/**
 * Created by PhpStorm.
 * User: a.yanover
 * Date: 18.11.2019
 * Time: 11:53
 */

namespace app\models;


class Tools
{

    public static function pageTotal($provider, $fieldName)
    {
        $total=0;
        foreach($provider as $item){
            $total+=$item[$fieldName];
        }
        return $total;
    }

    public static function priceFormat($price) {
        return number_format($price, 2, ',', ' ');
    }

    public static function dd($text) {
        return '<pre>'.var_export($text, 1).'</pre>';
    }

}
