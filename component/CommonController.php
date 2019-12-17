<?php
/**
 * Created by PhpStorm.
 * User: sqtech
 * Date: 2018/12/28
 * Time: 2:48 PM
 */

namespace app\component;

use Yii;
use yii\web\Controller;

class CommonController extends Controller
{
    public $code = 0;
    public $msg = '';
    public $data = [];

    /**
     * 输出json
     * @param int $code
     * @param string $msg
     * @param array $data
     */
    public function echoJson($code = 0, $msg = '', $data = [])
    {
        $this->code = $code === 0 ? $this->code : $code;
        $this->msg = $msg === '' ? $this->msg : $msg;
        $this->data = $data === [] ? $this->data : $data;
        //设置header
        $this->setResponseHttpHeader([['Content-type: appliction/json']]);
        echo json_encode([
            'code' => $this->code,
            'msg' => $this->msg,
            'data' => $this->data,
        ]);
        exit();
    }

    /**
     * 设置http响应头
     * @param array $header
     */
    public function setResponseHttpHeader($header = [])
    {
        array_map(function($n){
            if (is_array($n)) {
                if (count($n) > 3) {
                    throw new \Exception('http头设置参数错误', 500);
                }
                switch (count($n)) {
                    case 1:
                        header($n[0]);
                        break;
                    case 2:
                        header($n[0], $n[1]);
                        break;
                    case 3:
                        header($n[0], $n[1], $n[2]);
                        break;
                }
            } else {
                throw new \Exception('http头设置格式错误', 500);
            }
        }, $header);
    }

}