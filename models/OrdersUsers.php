<?php

namespace app\models;

use app\models\query\OrdersUsersQuery;
use Yii;

/**
 * This is the model class for table "orders_users".
 *
 * @property int $orders_id
 * @property int $users_id
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
            [['orders_id', 'users_id'], 'required'],
            [['orders_id', 'users_id', 'status'], 'integer'],
            [['orders_id'], 'unique', 'targetAttribute' => ['orders_id', 'orders_id' => 'users_id']],
            [['orders_id'], 'exist', 'skipOnError' => true, 'targetClass' => Orders::className(), 'targetAttribute' => ['orders_id' => 'id']],
            [['users_id'], 'exist', 'skipOnError' => true, 'targetClass' => Users::className(), 'targetAttribute' => ['users_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'orders_id' => 'Orders ID',
            'users_id' => 'Users ID',
            'status' => 'Status',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrder()
    {
        return $this->hasOne(Orders::className(), ['id' => 'orders_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(Users::className(), ['id' => 'users_id']);
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
