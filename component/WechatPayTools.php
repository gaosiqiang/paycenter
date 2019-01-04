<?php
/**
 * Created by PhpStorm.
 * User: sqtech
 * Date: 2019/1/3
 * Time: 4:22 PM
 */

namespace app\component;

class WechatPayTools
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

    /**
     * 将xml转为array
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

    /**
     *
     * 获取jsapi支付的参数
     * @param array $UnifiedOrderResult 统一支付接口返回的数据
     * @throws WxPayException
     *
     * @return json数据，可直接填入js函数作为参数
     */
    public static function GetJsApiParameters($UnifiedOrderResult, $config)
    {
        if(!array_key_exists("appid", $UnifiedOrderResult)
            || !array_key_exists("prepay_id", $UnifiedOrderResult)
            || $UnifiedOrderResult['prepay_id'] == "")
        {
            throw new \Exception("参数错误");
        }
        $data = [];
        $data['appId'] = $UnifiedOrderResult["appid"];
        $timeStamp = time();
        $data['timeStamp'] = "$timeStamp";
        $data['nonceStr'] = WechatSign::getNonceStr();
        $data['package'] = "prepay_id=" . $UnifiedOrderResult['prepay_id'];
        $data['paySign'] = WechatSign::MakeSign($config);;
        $parameters = json_encode($data);
        return $parameters;
    }

    /**
     *
     * 获取地址js参数
     *
     * @return 获取共享收货地址js函数需要的参数，json格式可以直接做参数使用
     */
    public static function GetEditAddressParameters($config)
    {
        $data = array();
        $data["appid"] = $config['appid'];
        $data["url"] = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        $time = time();
        $data["timestamp"] = "$time";
        $data["noncestr"] = WechatSign::getNonceStr();
        $data["accesstoken"] = $config["access_token"];

        ksort($data);
        $params = self::ToUrlParams($data);
        $addrSign = sha1($params);
        $afterData = [
            "addrSign" => $addrSign,
            "signType" => "sha1",
            "scope" => "jsapi_address",
            "appId" => $config['appid'],
            "timeStamp" => $data["timestamp"],
            "nonceStr" => $data["noncestr"],
        ];
        $parameters = json_encode($afterData);
        return $parameters;
    }

    /**
     *
     * 拼接签名字符串
     * @param array $urlObj
     *
     * @return 返回已经拼接好的字符串
     */
    private static function ToUrlParams($urlObj)
    {
        $buff = "";
        foreach ($urlObj as $k => $v)
        {
            if($k != "sign"){
                $buff .= $k . "=" . $v . "&";
            }
        }

        $buff = trim($buff, "&");
        return $buff;
    }

}