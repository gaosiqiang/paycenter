<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "pay_event".
 *
 * @property int $id
 * @property int $pay_order_id 支付订单id
 * @property int $event_type 记录类型（10创建支付订单，20支付回调，30退款，40退款回调）
 * @property string $event_data 记录数据（回调类型就是回调数据）
 * @property int $create_time 创建时间
 */
class PayEvent extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pay_event';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['pay_order_id', 'event_type', 'create_time'], 'integer'],
            [['event_data'], 'required'],
            [['event_data'], 'string', 'max' => 5000],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'pay_order_id' => 'Pay Order ID',
            'event_type' => 'Event Type',
            'event_data' => 'Event Data',
            'create_time' => 'Create Time',
        ];
    }
}
