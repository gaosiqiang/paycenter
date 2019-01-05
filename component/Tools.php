<?php
/**
 * Created by PhpStorm.
 * User: sqtech
 * Date: 2019/1/5
 * Time: 3:31 PM
 */

namespace app\component;


class Tools
{
    /**
     * post请求
     * @param $data
     * @param array $header
     * @return array
     */
    public static function http_post($url, $data, $header = [])
    {
        $curl = curl_init();
        if (!$header) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        }
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        //普通数据
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        $res = curl_exec($curl);
        $info = curl_getinfo($curl);
        curl_close($curl);
        return ['res' => $res, 'info' => $info];
    }

    /**
     * get请求
     * @param $data
     * @param array $header
     * @return array
     */
    public static function http_get($url, $data = [], $header = [])
    {
        $curl = curl_init();
        if (!$header) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        if ($data) {
            $url .= http_build_query($data);
        }
        curl_setopt($curl, CURLOPT_URL, $url);
        $res = curl_exec($curl);
        $info = curl_getinfo($curl);
        curl_close($curl);
        return ['res' => $res, 'info' => $info];
    }

}