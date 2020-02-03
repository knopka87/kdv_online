<?php

namespace app\models;

use app\models\query\TokensQuery;
use Yii;

/**
 * This is the model class for table "tokens".
 *
 * @property int $id
 * @property int $user_id
 * @property string $token
 * @property int $status
 *
 * @property Users $user
 */
class Tokens extends \yii\db\ActiveRecord
{

    const STATUS_ACTIVE = 1;
    const STATUS_NOT_ACTIVE = 0;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tokens';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'token'], 'required'],
            [['user_id', 'status'], 'integer'],
            [['token'], 'string', 'max' => 255],
            [['token'], 'unique'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => Users::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'token' => 'Token',
            'status' => 'Status'
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasOne(Users::className(), ['id' => 'user_id']);
    }

    /**
     * {@inheritdoc}
     * @return TokensQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new TokensQuery(get_called_class());
    }

    public static function deleteToken($token)
    {
        self::deleteAll(['token' => $token]);
    }

}
