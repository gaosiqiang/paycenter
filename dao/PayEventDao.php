<?php
/**
 * Created by PhpStorm.
 * User: sqtech
 * Date: 2019/1/9
 * Time: 6:44 PM
 */

namespace app\dao;

use Yii;
use app\models\PayEvent;

class PayEventDao
{
    /**
     * 添加记录
     * @param $insert_data
     * @return int|string
     * @throws \yii\db\Exception
     */
    public static function addEvent($insert_data)
    {
        $connection  = Yii::$app->db;
        $values = '';
        $fields = '';
        $table_name = PayEvent::tableName();
        foreach ($insert_data as $key => $value) {
            $fields .= $key.',';
            $values .= is_string($value) ? "'".$value. "'," : $value. ",";
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
     * 获取支付订单id对应记录数据
     * @param $pay_order_id
     * @return array|false
     * @throws \yii\db\Exception
     */
    public static function getEventByPayOrderId($pay_order_id, $event_type)
    {
        if (!$pay_order_id || !$event_type) {
            return [];
        }
        $connection  = Yii::$app->db;
        $table_name = PayEvent::tableName();
        $fields = [
            'pay_order_id',
            'event_type',
            'event_data',
            'create_time',
        ];
        $fields_str = implode(',', $fields);
        $sql = "select $fields_str from $table_name where pay_order_id=$pay_order_id and event_type=$event_type";
        $ret = $connection->createCommand($sql)->queryOne();
        if (!$ret) {
            return [];
        }
        return $ret;
    }

}