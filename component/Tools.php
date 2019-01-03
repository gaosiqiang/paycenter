<?php
/**
 * Created by PhpStorm.
 * User: sqtech
 * Date: 2019/1/3
 * Time: 4:22 PM
 */

namespace app\component;


class Tools
{
    /**
     * 获取毫秒级别的时间戳
     */
    private static function getMillisecond()
    {
        //获取毫秒的时间戳
        $time = explode ( " ", microtime () );
        $time = $time[1] . ($time[0] * 1000);
        $time2 = explode( ".", $time );
        $time = $time2[0];
        return $time;
    }

    /**
     * 输出xml字符
     * @throws WxPayException
     **/
    public static function arrayToXml($array)
    {
        if(!is_array($array) || count($array) <= 0)
        {
            throw new \Exception("数组数据异常！");
        }

        $xml = "<xml>";
        foreach ($array as $key=>$val)
        {
//            if (is_numeric($val)){
//                $xml.="<".$key.">".$val."</".$key.">";
//            }else{
//                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
//            }
            $xml.="<".$key.">".$val."</".$key.">";
        }
        $xml.="</xml>";
        return $xml;
    }
}