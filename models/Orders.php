<?php

namespace app\models;

use app\models\query\OrdersQuery;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "orders".
 *
 * @property int $id
 * @property string $status
 * @property int $created_at
 * @property int $updated_at
 *
 * @property OrderPositions[] $orderPositions
 */
class Orders extends \yii\db\ActiveRecord
{
    const STATUS_NOT_ACTIVE = 1;
    const STATUS_ACTIVE = 2;
    const STATUS_CLOSE = 3;

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
     * {@inheritdoc}
     *
     * @return OrdersQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new OrdersQuery(get_called_class());
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::find()
            ->active()
            ->andWhere(['id' => $id])
            ->one();
    }

    public static function findTodayOrder() {

        $today = strtotime(date('d.m.Y'));
        $tomorrow  = mktime(0, 0, 0, date('m')  , date('d')+1, date('Y'));
        return static::find()
            ->andWhere(['status' => Orders::STATUS_ACTIVE])
            ->andFilterCompare('created_at', '>='.$today)
            ->andFilterCompare('created_at', '<'.$tomorrow)
            ->one();
    }

    public function isTodayOrder() {

        $today = strtotime(date('d.m.Y'));
        $tomorrow  = mktime(0, 0, 0, date('m')  , date('d')+1, date('Y'));

        return $this->created_at >= $today && $this->created_at < $tomorrow;
    }


}
