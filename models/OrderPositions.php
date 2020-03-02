<?php

namespace app\models;

use app\models\kdv\KdvBasket;
use app\models\kdv\KdvProduct;
use Yii;
use yii\behaviors\TimestampBehavior;
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
class OrderPositions extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'order_positions';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['order_id', 'user_id', 'kdv_url', 'amount'], 'required'],
            [['order_id', 'user_id', 'weight', 'created_at', 'updated_at'], 'integer'],
            [['price', 'amount', 'multiple'], 'number'],
            [['order_id', 'user_id', 'kdv_url'], 'unique', 'targetAttribute' => ['order_id', 'user_id', 'kdv_url']],
            [['kdv_url', 'caption'], 'string', 'max' => 255],
            [['order_id'], 'exist', 'skipOnError' => true, 'targetClass' => Orders::className(), 'targetAttribute' => ['order_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => Users::className(), 'targetAttribute' => ['user_id' => 'id']],
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
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'order_id' => 'Order ID',
            'user_id' => 'User ID',
            'kdv_url' => 'Kdv Url',
            'amount' => 'Amount',
            'price' => 'Price',
            'caption' => 'Caption',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
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


    public function getProductInfo() {

        preg_match('#-([0-9]+)$#', $this->kdv_url, $match);
        $this->kdv_id = (int)$match[1];

        $product = new KdvProduct();
        $productInfo = $product->getProductInfo($this->kdv_id);

        if (!$productInfo->isAvailable) {
            Yii::$app->session->setFlash('error', 'Товара нет в наличии!');
            return false;
        }

        $this->multiple = $productInfo->quant->multiple;
        $this->unit = $productInfo->quant->unit;
        $this->kdv_price = $productInfo->quant->pricePerUnit;
        $this->price = ceil($this->kdv_price * 105)/100;

        $this->caption = $productInfo->name;

        foreach ($productInfo->properties as $property) {
            switch ($property->name) {
                case 'weight_unit':
                    if ($property->unit === 'г') {
                        $this->weight = $property->value;
                    }
                    elseif ($property->unit === 'кг') {
                        $this->weight = $property->value*1000;
                    }
                    break;
                case 'protein_content':
                    $this->protein = $property->value;
                    break;
                case 'fat_content':
                    $this->fat = $property->value;
                    break;
                case 'carbohydrate_content':
                    $this->carbon = $property->value;
                    break;
                case 'energy_value':
                    $this->kcal = $property->value;
                    break;
            }
        }

        if (!$this->weight) {
            preg_match('/[^\d]*([0-9,]*).(г|кг).*/ui', $this->caption, $output);
            $weight = str_replace(',', '.', $output[1]);
            if ($output[2] === 'кг') {
                $weight *= 1000;
            }
            $this->weight = $weight;
        }

        return true;
    }

    public static function getDischangeHTML($number, $decimal = 0) {

        return number_format($number, $decimal, ',', ' ');
    }


    /**
     * @param int $id id позиции заказа
     *
     * @return ActiveRecord
     */
    public static function findIdentity($id)
    {
        return static::find()
            ->andWhere(['id' => $id])
            ->one();
    }

    public function addPosition(): void
    {
        if (empty($this->order_id.$this->user_id)) {
            return;
        }

        if (strpos($this->kdv_url, 'kdvonline') === false) {
            return;
        }

        $order = Orders::findIdentity($this->order_id);

        $ordersUsers = OrdersUsers::find()->andWhere(['order_id' => $this->order_id, 'user_id' => $this->user_id])->one();
        if (!$ordersUsers) {
            $ordersUsers = new OrdersUsers();
            $ordersUsers->user_id = $this->user_id;
            $ordersUsers->order_id = $this->order_id;
            $ordersUsers->status = OrdersUsers::STATUS_START;
            $ordersUsers->insert();
        }
        elseif ($ordersUsers->status != OrdersUsers::STATUS_START) {

            $ordersUsers->status = OrdersUsers::STATUS_START;
            $ordersUsers->update();
        }

        if (!$order || in_array($order->status, Orders::statusDone())) {
            if (!Yii::$app->user->identity->isAdmin()) {
                Yii::$app->session->setFlash('danger', 'Заказ заблокирован для изменений!');
                return;
            }
        }

        /** @var OrderPositions $findPosition */
        $findPosition = static::find()->andWhere(
            [
                'order_id' => $this->order_id,
                'user_id' => $this->user_id,
                'kdv_url' => $this->kdv_url
            ]
        )->one();


        $kdvBasket = new KdvBasket();
        if ($findPosition) {
            $findPosition->amount = $this->amount;
            $findPosition->getProductInfo();
            $findPosition->update();

            $kdvBasket->updateBasket($findPosition->kdv_id, $findPosition->order_id);

            Yii::$app->session->setFlash('info', 'Кол-во у товара изменено.');
        }
        elseif ($this->getProductInfo()) {

            $this->insert();

            $kdvBasket->updateBasket($this->kdv_id, $this->order_id);

            Yii::$app->session->setFlash('success', 'Товар успешно добавлен в заказ.');
        }
    }

    public function deletePosition($delKdvBasket=true) {

        $isAdmin = Yii::$app->user->identity->isAdmin();
        if ($delKdvBasket) {
            $kdvBasket = new KdvBasket();
            $kdvBasket->delBasket($this->kdv_id);
        }

        Yii::$app->session->setFlash('info', 'Позиция <b>'.$this->caption.'</b> успешно удалена.');

        if ($isAdmin && $this->user_id !== Yii::$app->user->id) {
            $notification = new Notification();
            $notification->title = 'Удалена позиция из заказа №' . $this->order_id;
            $notification->body = '"' . $this->caption . '" - нет в наличии на КДВ';
            $notification->clickAction = 'https://' . $_SERVER['HTTP_HOST'] .
                \yii\helpers\Url::to(['orders/view', 'id' => $this->order_id]);
            $notification->send([$this->user_id]);
        }
        $position = $this;
        $this->delete();

        if ($this->order->status == Orders::STATUS_PAYED) {
            UserBalance::refreshBalance($position->order_id, $position->user_id);
        }
    }

    public static function getTotalPrice($dataProvider) {

        $totalBalance = 0;
        $isAdmin = !Yii::$app->user->isGuest && Yii::$app->user->identity->isAdmin();

        foreach ($dataProvider as $item){
            if ($isAdmin) {
                $totalBalance += $item['amount'] * $item['kdv_price'];
            }
            else {
                $totalBalance += $item['amount'] * $item['price'];
            }
        }

        return round($totalBalance, 2);
    }

    public static function getTotalWeight($dataProvider) {

        $totalBalance = 0;

        foreach ($dataProvider as $item){
            $totalBalance += $item['amount']*$item['weight'];
        }

        return $totalBalance;
    }

    public static function deletePositionForAllUser($orderId, $kdvId) {

        $positionList = OrderPositions::find()
            ->andWhere(['order_id' => $orderId, 'kdv_id' => $kdvId])
            ->all();
        /** @var OrderPositions $position */
        foreach ($positionList as $position) {
            $position->deletePosition(false);
        }
        (new KdvBasket())->delBasket($kdvId);
    }
}
