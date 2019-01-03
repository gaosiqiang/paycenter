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
}