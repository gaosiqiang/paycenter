<?php
/**
 * Created by PhpStorm.
 * User: sqtech
 * Date: 2019/1/9
 * Time: 6:42 PM
 */

namespace app\dao;

use Yii;
use app\models\PayOrder;

class PayOrderDao
{

    /**
     * 创建订单数据
     * @param $insert_data
     * @return int|string
     * @throws \yii\db\Exception
     */
    public static function createOrder($insert_data)
    {
        $connection  = Yii::$app->db;
        $values = '';
        $fields = '';
        $table_name = PayOrder::tableName();
        foreach ($insert_data as $key => $value) {
            $fields .= $key.',';
            $values .= is_string($value) ? "'" . $value . "'," : $value . ",";
        }
        $fields = rtrim($fields, ',');
        $values = rtrim($values, ',');
        $sql = "insert into $table_name($fields) value ($values)";
        $ret = $connection->createCommand($sql)->execute();
        if (!$ret) {
            return 0;
        }
        return $connection->getLastInsertID();
    }

    /**
     * 获取订单数据
     * @param $order_id
     * @return array|false
     * @throws \yii\db\Exception
     */
    public static function getOrderById($order_id)
    {
        $connection  = Yii::$app->db;
        $fields = [
            'channel_id',
            'scene_id',
            'biz_order_id',
            'order_status',
            'handle_status',
            'type',
            'params',
            'create_time',
            'update_time',
        ];
        $table_name = PayOrder::tableName();
        $fields_str = implode(',', $fields);
        $sql = "select $fields_str from $table_name where id=$order_id";
        $ret = $connection->createCommand($sql)->queryOne();
        if (!$ret) {
            return [];
        }
        return $ret;
    }

    /**
     * 更新订单支付状态
     * @param $order_id
     * @param $order_status
     * @param $handle_status
     * @return int
     * @throws \yii\db\Exception
     */
    public static function updatOrderPayStatusById($order_id, $order_status, $handle_status)
    {
        $connection  = Yii::$app->db;
        $table_name = PayOrder::tableName();
        $sql = "update $table_name set order_status=$order_status,handle_status=$handle_status where id=$order_id";
        $ret = $connection->createCommand($sql)->execute();
        if (!$ret) {
            return 0;
        }
        return 1;
    }

}