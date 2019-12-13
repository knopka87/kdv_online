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

}