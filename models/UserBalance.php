<?php

namespace app\models;

use app\models\query\UserBalanceQuery;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\Html;

/**
 * This is the model class for table "user_balance".
 *
 * @property int $id
 * @property int $user_id
 * @property int $balance
 * @property int $created_at
 * @property int $updated_at
 *
 * @property Users $user
 */
class UserBalance extends \yii\db\ActiveRecord
{

    const TYPE_DEPOSIT = 1; // пополнение баланса
    const TYPE_WRITE_OFF = 2; // списание с баланса
    const TYPE_DONATE = 3; // donate
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_balance';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id'], 'required'],
            [['user_id'], 'unique'],
            [['balance'], 'number'],
            [['user_id', 'created_at', 'updated_at'], 'integer'],
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
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(Users::className(), ['id' => 'user_id']);
    }

    /**
     * {@inheritdoc}
     * @return UserBalanceQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new UserBalanceQuery(get_called_class());
    }


    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::find()
            ->andWhere(['id' => $id])
            ->one();
    }


    public static function changeBalance($sum, $userId, $orderId, $type = self::TYPE_DEPOSIT, $comment = false) {
        $balance = UserBalance::find()->andWhere(['user_id' => $userId])->one();
        if (!$balance) {
            $balance = new UserBalance();
            $balance->user_id = $userId;
        }

        switch ($type) {
            case self::TYPE_DEPOSIT:
                $sum = round($sum, 2);
                $comment = $comment?:'Пополнение баланса за заказ №'.$orderId;
                break;
            case self::TYPE_WRITE_OFF:
                $sum = -round($sum, 2);
                $comment = $comment?:'Списание за заказ №'.$orderId;
                break;
            case self::TYPE_DONATE:
                $sum = -round($sum, 2);
                $comment = $comment?:'Добровольный взнос на развитие проекта. Ну или просто, чтобы Сашке лучше жилось)';
                break;
        }

        $balance->balance += $sum;
        $balance->save();

        $balanceLog = new UserBalanceLog();
        $balanceLog->user_id = $userId;
        $balanceLog->order_id = $orderId;
        $balanceLog->sum = $sum;
        $balanceLog->comment = $comment;
        $balanceLog->type = $type;
        $balanceLog->insert();

    }

    public static function refreshBalance($orderId, $userId = 0) {

        // отменяем ранее сделанное списание средств за заказ
        $filter = ['order_id' => $orderId, 'type' => UserBalance::TYPE_WRITE_OFF];
        if ((int)$userId > 0) {
            $filter['user_id'] = $userId;
        }

        $balanceLogs = UserBalanceLog::find()
            ->andWhere($filter)
            ->all();

        foreach ($balanceLogs as $balanceLog) {
            $balance = self::find()->andWhere(['user_id' => $balanceLog->user_id])->one();
            $balance->balance -= round($balanceLog->sum, 2);
            $balance->save();

            $balanceLog->delete();
        }

        // заново списываем средства за заказ
        self::payOrder($orderId, $userId);
    }

    public static function payOrder($orderId, $userId = 0) {

        $userList = [];
        $filter = ['order_id' => $orderId];
        if ((int)$userId > 0) {
            $filter['user_id'] = $userId;
        }
        $positions = OrderPositions::find()
            ->addSelect([
                'user_id',
                'SUM(amount*price) as price',
            ])
            ->andWhere($filter)
            ->groupBy('user_id')
            ->all();
        if ($positions) {
            foreach ($positions as $position) {

                $userList[] = $position->user_id;

                UserBalance::changeBalance(
                    $position->price,
                    $position->user_id,
                    $orderId,
                    UserBalance::TYPE_WRITE_OFF
                );
            }
            Orders::updateAll(['status' => Orders::STATUS_PAYED], ['id' => $orderId ]);

            // отправка уведомлений только тем кто участвует в заказе
            $notification = new Notification();
            $notification->title = 'Изменение баланса';
            $notification->body = 'Произошло снятие средств за заказ №' . $orderId . '. Нажмите на сообщение для просмотра ' .
                'статистики по балансу с дальнейшим переходом на оплату.';
            $notification->clickAction = 'https://' . $_SERVER['HTTP_HOST'] .
                \yii\helpers\Url::to(['balance/index']);
            $notification->send($userList);
        }
    }

    public static function getBalanceHtml() {
        $balance = UserBalance::find()->andWhere(['user_id' => \Yii::$app->user->id])->one()->balance;
        if ($balance >= 0) {
            $color = '#449d44';
        }
        else {
            $color = '#d80027';
        }

        return Html::a(
            'Баланс: ' . (float)round($balance, 2) . ' р.',
            UserBalance::getTinkoffLink(),
            [
                'target'=>'_blank',
                'style' => 'color: #fff;background-color: '.$color.';',
                'title' => 'Пополнить баланс'
            ]
        );

    }

    public static function getTinkoffLink() {
        $link = Yii::$app->params['tinkoff_link'];

        $balance = UserBalance::find()->andWhere(['user_id' => \Yii::$app->user->id])->one()->balance;
        if ($balance < 0) {
            $link .= '?moneyAmount='.abs($balance);
        }
        return $link;
    }


}
