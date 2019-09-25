<?php

namespace app\models;

use Codeception\Lib\Parser;
use GuzzleHttp\Client;
use phpQuery;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;


/**
 * This is the model class for table "order_positions".
 *
 * @property int $id
 * @property int $order_id
 * @property int $user_id
 * @property string $kdv_url
 * @property int $amount
 * @property double $price
 * @property string $caption
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
            [['order_id', 'user_id', 'amount', 'created_at', 'updated_at'], 'integer'],
            [['price'], 'number'],
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


    public function getKdvPageInfo() {
        // парсинг сайта кдв и заполнение полей caption, price

        // создаем экземпляр класса
        $client = new Client();
        // отправляем запрос к странице Яндекса
        $res = $client->request('GET', $this->kdv_url);
        // получаем данные между открывающим и закрывающим тегами body
        $body = $res->getBody();
        // подключаем phpQuery
        $document = phpQuery::newDocumentHTML($body);

        if (strpos($document->html(), 'Нет в наличии') !== false) {
            return false;
        }

        preg_match('#class=.product-cart__price-value[^>]+>(.*)</span>#', $document->html(), $match);
        $price = (float)str_replace(",", ".",$match[1]);

        $caption = $document->find('.product-description')->children('div')->children('h1')->html();

        $this->price = $price;
        $this->caption = $caption;

        return true;
    }


    /**
     * @param int $id id позиции заказа
     *
     * @return ActiveRecord
     */
    public static function findIdentity($id): ActiveRecord
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

        $findPosition = OrderPositions::find()->andWhere(
            [
                'order_id' => $this->order_id,
                'user_id' => $this->user_id,
                'kdv_url' => $this->kdv_url
            ]
        )->one();

        if ($findPosition) {
            $findPosition->amount = $this->amount;
            //$findPosition->getKdvPageInfo(); // по идее цена не должна обновиться
            $findPosition->update();
            \Yii::$app->session->setFlash('info', 'Кол-во у товара изменено');
        }
        elseif ($this->getKdvPageInfo()) {
            $this->insert();
            \Yii::$app->session->setFlash('success', 'Товар успешно добавлен в корзину');
        }
        else {
            \Yii::$app->session->setFlash('error', 'Не удалось добавить товар в корзину');
        }
    }

    public static function getTotalBalance($dataProvider) {

        $totalBalance = 0;

        foreach ($dataProvider as $item){
            $totalBalance += $item['amount']*$item['price'];
        }

        return $totalBalance;
    }
}
