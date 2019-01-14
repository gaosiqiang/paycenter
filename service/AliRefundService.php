<?php
/**
 * Author: SiqiangGao
 * Site: gaosiqiang.com
 * Email: gaosiqiang7@163.com
 * QQ: 205459371
 * GitHub: https://github.com/gaosiqiang
 * DateTime: 19/1/13 21:11
 */

namespace app\service;

use app\library\alipay_sdk\AopSdk;

class AliRefundService
{

    /**
     * @return int
     * https://docs.open.alipay.com/api_1/alipay.trade.refund/
     * https://openclub.alipay.com/read.php?tid=8328&fid=72&page=1
     */
    public function refund()
    {
        //初始化
        (new AopSdk())->init();
        $aop = new AopClient ();
        $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
        $aop->appId = 'your app_id';
        $aop->rsaPrivateKey = '请填写开发者私钥去头去尾去回车，一行字符串';
        $aop->alipayrsaPublicKey='请填写支付宝公钥，一行字符串';
        $aop->apiVersion = '1.0';
        $aop->signType = 'RSA2';
        $aop->postCharset='GBK';
        $aop->format='json';
        $request = new AlipayTradeRefundRequest ();
        $request->setBizContent("{" .
            "\"out_trade_no\":\"20150320010101001\"," .
            "\"trade_no\":\"2014112611001004680073956707\"," .
            "\"refund_amount\":200.12," .
            "\"refund_currency\":\"USD\"," .
            "\"refund_reason\":\"正常退款\"," .
            "\"out_request_no\":\"HZ01RF001\"," .
            "\"operator_id\":\"OP001\"," .
            "\"store_id\":\"NJ_S_001\"," .
            "\"terminal_id\":\"NJ_T_001\"," .
            "      \"goods_detail\":[{" .
            "        \"goods_id\":\"apple-01\"," .
            "\"alipay_goods_id\":\"20010001\"," .
            "\"goods_name\":\"ipad\"," .
            "\"quantity\":1," .
            "\"price\":2000," .
            "\"goods_category\":\"34543238\"," .
            "\"categories_tree\":\"124868003|126232002|126252004\"," .
            "\"body\":\"特价手机\"," .
            "\"show_url\":\"http://www.alipay.com/xxx.jpg\"" .
            "        }]," .
            "      \"refund_royalty_parameters\":[{" .
            "        \"royalty_type\":\"transfer\"," .
            "\"trans_out\":\"2088101126765726\"," .
            "\"trans_out_type\":\"userId\"," .
            "\"trans_in_type\":\"userId\"," .
            "\"trans_in\":\"2088101126708402\"," .
            "\"amount\":0.1," .
            "\"amount_percentage\":100," .
            "\"desc\":\"分账给2088101126708402\"" .
            "        }]," .
            "\"org_pid\":\"2088101117952222\"" .
            "  }");
        $result = $aop->execute ( $request);

        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        if(!empty($resultCode)&&$resultCode == 10000){
            return 1;
        } else {
            return 0;
        }
    }

}