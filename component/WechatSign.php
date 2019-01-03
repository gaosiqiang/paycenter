<?php
/**
 * Created by PhpStorm.
 * User: sqtech
 * Date: 2019/1/3
 * Time: 5:06 PM
 */

namespace app\component;


class WechatSign
{

    public static function getSign($data)
    {
        $sign = self::MakeSign($data);
        return $sign;
    }

    /**
     * 生成签名
     * @param WxPayConfigInterface $config  配置对象
     * @param bool $needSignType  是否需要补signtype
     * @return 签名，本函数不覆盖sign成员变量，如要设置签名需要调用SetSign方法赋值
     */
    public static function MakeSign($data, $needSignType = true)
    {
        if($needSignType) {
            $data['sign_type'] = self::GetSignType();
        }
        //签名步骤一：按字典序排序参数
        ksort($data);
        $string = self::ToUrlParams($data);
        //签名步骤二：在string后加入KEY
        $string = $string . "&key=".self::GetKey();
        //签名步骤三：MD5加密或者HMAC-SHA256
        if(self::GetSignType() == "MD5"){
            $string = md5($string);
        } else if(self::GetSignType() == "HMAC-SHA256") {
            $string = hash_hmac("sha256",$string ,self::GetKey());
        } else {
            throw new \Exception("签名类型不支持！");
        }

        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);
        return $result;
    }

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

}