<?php

namespace app\models;

use app\models\query\UserBalanceLogQuery;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "user_balance_log".
 *
 * @property int $id
 * @property int $user_id
 * @property string $comment
 * @property int $sum
 * @property int $created_at
 * @property int $updated_at
 *
 * @property Users $user
 */
class UserBalanceLog extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_balance_log';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id'], 'required'],
            [['user_id', 'order_id', 'created_at', 'updated_at'], 'integer'],
            [['sum'], 'number'],
            [['comment'], 'string', 'max' => 255],
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
     * @return UserBalanceLogQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new UserBalanceLogQuery(get_called_class());
    }

    public static function getTotalSum($dataProvider) {
        $totalBalance = 0;

        if ($dataProvider) {
            foreach ($dataProvider as $item) {
                $totalBalance += $item['sum'];
            }
        }

        return $totalBalance;
    }
}
