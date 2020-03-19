<?php

namespace app\models;

use app\models\kdv\KdvBasket;
use app\models\kdv\KdvProduct;
use Yii;
use yii\base\Model;
use yii\behaviors\TimestampBehavior;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\db\ActiveRecord;


/**
 * This is the model class for table "order_positions".
 *
 * @property int $id
 * @property int $kdv_id
 * @property int $order_id
 * @property int $user_id
 * @property string $kdv_url
 * @property float $amount
 * @property float $multiple
 * @property double $price
 * @property double $kdv_price
 * @property string $unit
 * @property string $caption
 * @property int $weight
 * @property float $protein
 * @property float $fat
 * @property float $carbon
 * @property int $kcal
 * @property int $created_at
 * @property int $updated_at
 *
 * @property Orders $order
 */
class OrderPositionsSearch extends OrderPositions
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id'], 'integer'],
            [['user_id'], 'safe'],
        ];
    }

    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    public function getTotalPositionProvider($orderId, $params)
    {
        $query = OrderPositions::find()
            ->addSelect(['*', 'IF(user_id = '.Yii::$app->user->id.', 0, 1) as sort_user'])
            ->andWhere(['order_id' => $orderId])
            ->orderBy(['sort_user' => SORT_ASC, 'user_id' => SORT_ASC]);
        $dataProvider = new ActiveDataProvider(
            [
                'query' => $query,
                'pagination' => [
                    'pageSize' => 100
                ],
                'sort' => false,
            ]
        );

        // загружаем данные формы поиска и производим валидацию
        if (!($this->load($params) && $this->validate())) {

            return $dataProvider;
        }
        $query->andFilterWhere(['user_id' => $this->user_id]);

        return $dataProvider;
    }
}
