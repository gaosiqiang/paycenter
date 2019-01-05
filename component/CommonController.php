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
        echo json_encode([
            'code' => $this->code,
            'msg' => $this->msg,
            'data' => $this->data,
        ]);
        exit();
    }
}