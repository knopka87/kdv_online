<?php

namespace app\models;

use app\models\query\OrdersQuery;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "orders".
 *
 * @property int $id
 * @property string $status
 * @property int $created_at
 * @property int $updated_at
 *
 * @property OrderPositions[] $orderPositions
 * @property OrdersUsers[] $ordersUsers
 */
class Orders extends \yii\db\ActiveRecord
{
    const STATUS_NOT_ACTIVE = 1; // не активен
    const STATUS_ACTIVE = 2; // открыт для заказа
    const STATUS_BLOCK = 3; // заказ заблокирован
    const STATUS_BASKET = 4; // корзина
    const STATUS_PAYED = 5; // закрыт и списаны деньги с баланса

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'orders';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['status'], 'required'],
            [['created_at', 'updated_at', 'status'], 'integer'],
        ];
    }

    public function behaviors(){
        return [
            [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
            ],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrderPositions()
    {
        return $this->hasMany(OrderPositions::className(), ['order_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrdersUsers()
    {
        return $this->hasMany(OrdersUsers::className(), ['order_id' => 'id']);
    }

    /**
     * @return OrdersQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new OrdersQuery(get_called_class());
    }

    /**
     * @param int $id
     * @return Orders|null
     */
    public static function findIdentity($id)
    {
        return static::find()
            ->active()
            ->andWhere(['id' => $id])
            ->one();
    }

    /**
     * @return Orders|null
     */
    public static function findActiveOrder() {

        return static::find()
            ->andWhere(['status' => Orders::STATUS_ACTIVE])
            ->orderBy('created_at ASC')
            ->one();
    }

    /**
     * @return bool
     */
    public function isTodayOrder() {

        $today = strtotime(date('d.m.Y'));
        $tomorrow  = mktime(0, 0, 0, date('m')  , date('d')+1, date('Y'));

        return $this->created_at >= $today && $this->created_at < $tomorrow;
    }

    /**
     * @return bool
     */
    public function isProcessing() {

        return OrdersUsers::find()->andWhere(['order_id' => $this->id, 'status' => OrdersUsers::STATUS_START])->count() > 0;
    }

    /**
     * @return OrdersUsers[]|array
     */
    public function whoIsProcessing() {
        return OrdersUsers::find()->andWhere(['order_id' => $this->id, 'status' => OrdersUsers::STATUS_START])->all();
    }

    /**
     * @return array
     */
    public static function statusDone() {
        return [self::STATUS_BLOCK, self::STATUS_PAYED];
    }
}
