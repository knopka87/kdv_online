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
    const STATUS_DONE = 3;
    const STATUS_BASKET = 4;

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
     * @return OrdersQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new OrdersQuery(get_called_class());
    }

    /**
     * @param $id
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
    public static function findTodayOrder() {

        $today = strtotime(date('d.m.Y'));
        $tomorrow  = mktime(0, 0, 0, date('m')  , date('d')+1, date('Y'));
        return static::find()
            ->andWhere(['status' => Orders::STATUS_ACTIVE])
            ->andFilterCompare('created_at', '>='.$today)
            ->andFilterCompare('created_at', '<'.$tomorrow)
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
     * @return array
     */
    public static function getTopWeightOrder() {

        return OrderPositions::find()
            ->addSelect(['*', 'SUM(weight*amount) as weight'])
            ->addGroupBy('order_id')
            ->with(['order' => function ($query) {
				$query->andWhere(['status' => Orders::STATUS_DONE]);
				}
			])
            ->orderBy('weight DESC')
            ->limit(3)
            ->asArray()
            ->all();
    }

    /**
     * @return array
     */
    public static function getTopTotalPriceOrder() {

        return OrderPositions::find()
            ->addSelect(['*', 'SUM(amount*price) as sum'])
            ->addGroupBy('order_id')
            ->with(['order' => function ($query) {
				$query->andWhere(['status' => Orders::STATUS_DONE]);
				}
			])
            ->orderBy('sum DESC')
            ->limit(3)
            ->asArray()
            ->all();
    }

    /**
     * @return array
     */
    public static function getTopCountPositions() {

        return OrderPositions::find()
            ->addSelect(['*', 'SUM(amount) as sum'])
            ->addGroupBy('order_id')
            ->with(['order' => function ($query) {
				$query->andWhere(['status' => Orders::STATUS_DONE]);
				}
			])
            ->orderBy('sum DESC')
            ->limit(3)
            ->asArray()
            ->all();
    }

    /**
     * @return array
     */
    public static function getTopCountUsers() {

        return OrderPositions::find()
            ->addSelect(['order_id', 'COUNT(DISTINCT user_id) as sum'])
            ->with(['order' => function ($query) {
				$query->andWhere(['status' => Orders::STATUS_DONE]);
				}
			])
            ->addGroupBy('order_id')
            ->orderBy('sum DESC')
            ->limit(3)
            ->asArray()
            ->all();
    }

    /**
     * @return bool
     */
    public function isProcessing() {

        return OrdersUsers::find()->andWhere(['orders_id' => $this->id, 'status' => OrdersUsers::STATUS_START])->count() > 0;
    }

    /**
     * @return OrdersUsers[]|array
     */
    public function whoIsProcessing() {
        return OrdersUsers::find()->andWhere(['orders_id' => $this->id, 'status' => OrdersUsers::STATUS_START])->all();
    }

}
