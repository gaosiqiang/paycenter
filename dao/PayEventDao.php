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

}