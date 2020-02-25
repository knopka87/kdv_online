<?php

namespace app\models;

use app\models\query\OrdersUsersQuery;
use Yii;

/**
 * This is the model class for table "orders_users".
 *
 * @property int $order_id
 * @property int $user_id
 * @property int $status
 *
 * @property Orders $orders
 * @property Users $users
 */
class OrdersUsers extends \yii\db\ActiveRecord
{
    const STATUS_START = 0;
    const STATUS_DONE = 1;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'orders_users';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['order_id', 'user_id'], 'required'],
            [['order_id', 'user_id', 'status'], 'integer'],
            //[['order_id'], 'unique', 'targetAttribute' => ['order_id', 'order_id' => 'user_id']], // ошибка в уникальности 2х сразу полей
            [['order_id'], 'exist', 'skipOnError' => true, 'targetClass' => Orders::className(), 'targetAttribute' => ['order_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => Users::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'order_id' => 'Order ID',
            'user_id' => 'Users ID',
            'status' => 'Status',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrder()
    {
        return $this->hasOne(Orders::className(), ['id' => 'order_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(Users::className(), ['id' => 'user_id']);
    }

    /**
     * {@inheritdoc}
     * @return OrdersUsersQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new OrdersUsersQuery(get_called_class());
    }
}
