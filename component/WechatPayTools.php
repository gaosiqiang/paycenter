<?php
/**
 * Created by PhpStorm.
 * User: sqtech
 * Date: 2019/1/3
 * Time: 4:22 PM
 */
namespace app\component;

use app\component\Tools;
use app\component\WechatException;

class WechatPayTools
{
    /**
     * 获取jsapi支付的参数
     * @param array $UnifiedOrderResult 统一支付接口返回的数据
     * @throws WxPayException
     * @return json数据，可直接填入js函数作为参数
     */
    public static function GetJsApiParameters($UnifiedOrderResult, $config)
    {
        if(!array_key_exists("appid", $UnifiedOrderResult)
            || !array_key_exists("prepay_id", $UnifiedOrderResult)
            || $UnifiedOrderResult['prepay_id'] == "")
        {
            throw new WechatException("参数错误");
        }
        $data = [];
        $data['appId'] = $UnifiedOrderResult["appid"];
        $timeStamp = time();
        $data['timeStamp'] = "$timeStamp";
        $data['nonceStr'] = WechatSignTools::getNonceStr();
        $data['package'] = "prepay_id=" . $UnifiedOrderResult['prepay_id'];
        $data['paySign'] = WechatSignTools::MakeSign($config);;
        $parameters = json_encode($data);
        return $parameters;
    }

    /**
     * 获取地址js参数
     * @return 获取共享收货地址js函数需要的参数，json格式可以直接做参数使用
     */
    public static function GetEditAddressParameters($config)
    {
        $data = array();
        $data["appid"] = $config['appid'];
        $data["url"] = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        $time = time();
        $data["timestamp"] = "$time";
        $data["noncestr"] = WechatSignTools::getNonceStr();
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
     * 以post方式提交xml到对应的接口url
     *
     * @param WxPayConfigInterface $config  配置对象
     * @param string $xml  需要post的xml数据
     * @param string $url  url
     * @param bool $useCert 是否需要证书，默认不需要
     * @param int $second   url执行超时时间，默认30s
     * @throws WxPayException
     */
    public static function postXmlCurl($data, $url, $useCert = false, $second = 30, $sslCertPath = '', $sslKeyPath = '')
    {
        $ch = curl_init();
        $curlVersion = curl_version();
        $ua = "WXPaySDK/3.0.9 (".PHP_OS.") PHP/".PHP_VERSION." CURL/".$curlVersion['version']." "
            .$data['mch_id'];

        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        //代理相关配置
        $proxyHost = "0.0.0.0";
        $proxyPort = 0;
        //如果有配置代理这里就设置代理
        if($proxyHost != "0.0.0.0" && $proxyPort != 0){
            curl_setopt($ch,CURLOPT_PROXY, $proxyHost);
            curl_setopt($ch,CURLOPT_PROXYPORT, $proxyPort);
        }
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,TRUE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,2);//严格校验
        curl_setopt($ch,CURLOPT_USERAGENT, $ua);
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //如果使用证书
        if($useCert == true){
            //设置证书
            //使用证书：cert 与 key 分别属于两个.pem文件
            //证书文件请放入服务器的非web目录下，也就是不要放在虚拟目录下
            //$sslCertPath = "";
            //$sslKeyPath = "";
            curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
            curl_setopt($ch,CURLOPT_SSLCERT, $sslCertPath);
            curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
            curl_setopt($ch,CURLOPT_SSLKEY, $sslKeyPath);
        }
        //post提交方式
        $xml = Tools::arrayToXml($data);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        //运行curl
        $data = curl_exec($ch);
        //var_dump(curl_getinfo($ch));die();
        //返回结果
        if($data){
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            curl_close($ch);
            throw new WechatException("curl出错，错误码:$error");
        }
    }

    /**
     * 获取签名
     * @param $data
     * @return 签名，本函数不覆盖sign成员变量，如要设置签名需要调用SetSign方法赋值
     * @throws \Exception
     */
    public static function getSign($data, $key = '')
    {
        $sign = self::MakeSign($data, true, $key);
        return $sign;
    }

    /**
     * 生成签名
     * @param WxPayConfigInterface $config  配置对象
     * @param bool $needSignType  是否需要补signtype
     * @return 签名，本函数不覆盖sign成员变量，如要设置签名需要调用SetSign方法赋值
     */
    public static function MakeSign($data, $needSignType = true, $key = '')
    {
//        if($needSignType) {
//            $data['sign_type'] = self::GetSignType();
//        }
        //签名步骤一：按字典序排序参数
        ksort($data);
        //签名步骤二：在string后加入KEY
        $string = self::ToUrlParams($data);
        if ($key === '') {
            $key = self::GetKey();
        }
        $string = $string . "&key=". $key;
        //签名步骤三：MD5加密或者HMAC-SHA256
        if(self::GetSignType() == "MD5"){
            $string = md5($string);
        } else if(self::GetSignType() == "HMAC-SHA256") {
            $string = hash_hmac("sha256", $string , $key);
        } else {
            throw new WechatException("签名类型不支持！");
        }
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);
        return $result;
    }



    /**
     * 生成签名 - 重写该方法
     * @param WxPayConfigInterface $config  配置对象
     * @param bool $needSignType  是否需要补signtype
     * @return 签名，本函数不覆盖sign成员变量，如要设置签名需要调用SetSign方法赋值
     */
    public function MakeSign2($data, $needSignType = false)
    {
        if($needSignType) {
            $data['sign_type'] = self::GetSignType();
        }
        //签名步骤一：按字典序排序参数
        ksort($data);
        $string = self::ToUrlParams($data);
        //签名步骤二：在string后加入KEY
        $string = $string . "&key=".self::GetKey();
        //签名步骤三：MD5加密
        $string = md5($string);
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);
        return $result;
    }

    /**
     * 获取签名类型
     * @return string
     */
    public static function GetSignType()
    {
        return "HMAC-SHA256";
    }

    /**
     * 格式化参数格式化成url参数
     */
    public static function ToUrlParams($data)
    {
        $buff = "";
        foreach ($data as $k => $v)
        {
            if($k != "sign" && $v != "" && !is_array($v)){
                $buff .= $k . "=" . $v . "&";
            }
        }

        $buff = trim($buff, "&");
        return $buff;
    }

    public static function GetKey()
    {
        return '8934e7d15453e97507ef794cf7b0519d';
    }

    /**
     *
     * 产生随机字符串，不长于32位
     * @param int $length
     * @return 产生的随机字符串
     */
    public static function getNonceStr($length = 32)
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str ="";
        for ( $i = 0; $i < $length; $i++ )  {
            $str .= substr($chars, mt_rand(0, strlen($chars)-1), 1);
        }
        return $str;
    }

    /**
     * @param WxPayConfigInterface $config  配置对象
     * 检测签名
     */
    public static function CheckSign($data, $check_sign, $key = '')
    {
        if(!self::IsSignSet($data)){
            throw new WechatException("签名错误！");
        }

        $sign = self::MakeSign($data, false, $key);
        if($check_sign == $sign){
            //签名正确
            return true;
        }
        throw new WechatException("签名错误！");
    }

    /**
     * 判断签名，详见签名生成算法是否存在
     * @return true 或 false
     **/
    public static function IsSignSet($data)
    {
        return array_key_exists('sign', $data);
    }

    /**
     * 格式化微信支付结果数据
     * @param $data
     * @param $response
     * @param $sign
     * @return array
     */
    public static function InitResults($data, $response, $sign)
    {
        try {
            $response = Tools::xmlToArray($response);
            WechatPayTools::CheckSign($data, $sign);
            //失败则直接返回失败
            if($response['return_code'] != 'SUCCESS') {
                foreach ($response as $key => $value) {
                    #除了return_code和return_msg之外其他的参数存在，则报错
                    if($key != "return_code" && $key != "return_msg"){
                        throw new \app\component\WechatException("输入数据存在异常！");
                    }
                }
            }
        } catch (\app\component\WechatException $e) {
            return ['return_code' => $response['return_code'], 'return_msg' => $e->getMessage()];
        }
        return $response;
    }


}