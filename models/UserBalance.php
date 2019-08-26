<?php

namespace app\models;

use app\models\query\UserBalanceQuery;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "user_balance".
 *
 * @property int $id
 * @property int $user_id
 * @property int $balance
 * @property int $expire_at
 * @property int $created_at
 * @property int $updated_at
 *
 * @property Users $user
 */
class UserBalance extends \yii\db\ActiveRecord
{
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
            [['user_id', 'expire_at', 'created_at', 'updated_at'], 'integer'],
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

    public function writeOffs($sum, $orderId) {
        $this->balance -= $sum;
        $this->save();

        $balanceLog = new UserBalanceLog();
        $balanceLog->user_id = $this->user_id;
        $balanceLog->order_id = $orderId;
        $balanceLog->sum = -round($sum, 2);
        $balanceLog->comment = 'Списание за заказ №'.$orderId;
        $balanceLog->insert();
    }

    public static function depositBalanse($sum, $userId, $orderId) {
        $balance = UserBalance::find()->andWhere(['user_id' => $userId])->one();
        if (!$balance) {
            $balance = new UserBalance();
            $balance->user_id = $userId;
        }
        $balance->balance += round($sum, 2);
        $balance->save();

        $balanceLog = new UserBalanceLog();
        $balanceLog->user_id = $userId;
        $balanceLog->order_id = $orderId;
        $balanceLog->sum = round($sum, 2);
        $balanceLog->comment = 'Пополнение баланса за заказ №'.$orderId;
        $balanceLog->save();

    }

    public static function getBalanceHtml() {
        $balance = UserBalance::find()->andWhere(['user_id' => \Yii::$app->user->id])->one()->balance;
        if ($balance >= 0) {
            $class = 'success';
        }
        else {
            $class = 'danger';
        }

        return '<button class="btn btn-'.$class.'" style="margin: 8px 0;">Баланс: ' . (float)round($balance, 2) . ' р.</button>';

    }
}
