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
     * array转xml
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
            $xml.="<".$key.">".$val."</".$key.">";
        }
        $xml.="</xml>";
        return $xml;
    }

    /**
     * xml转为array
     * @param string $xml
     * @throws WxPayException
     */
    public static function xmlToArray($xml)
    {
        if(!$xml){
            throw new Exception("xml数据异常！");
        }
        //将XML转为array
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        return json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    }

}