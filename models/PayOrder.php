<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "pay_order".
 *
 * @property int $id
 * @property int $channel_id 服务频道id
 * @property int $scene_id 业务场景id
 * @property string $biz_order_id 业务id
 * @property int $order_status 支付订单状态（0未支付，10已支付）
 * @property int $handle_status 订单处理状态（0未处理，10支付成功，20处理失败）
 * @property int $type 订单类型（10支付，20退款）
 * @property string $params 创建支付订单参数
 * @property int $create_time 创建时间
 * @property int $update_time 更新时间
 */
class PayOrder extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pay_order';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['channel_id', 'scene_id', 'order_status', 'handle_status', 'type', 'create_time', 'update_time'], 'integer'],
            [['params'], 'required'],
            [['biz_order_id'], 'string', 'max' => 225],
            [['params'], 'string', 'max' => 5000],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'channel_id' => 'Channel ID',
            'scene_id' => 'Scene ID',
            'biz_order_id' => 'Biz Order ID',
            'order_status' => 'Order Status',
            'handle_status' => 'Handle Status',
            'type' => 'Type',
            'params' => 'Params',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
        ];
    }
}
