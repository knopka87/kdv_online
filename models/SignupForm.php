<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * Signup form
 */
class SignupForm extends Model
{

    public $username;
    public $email;
    public $password;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['username', 'trim'],
            ['username', 'required'],
            ['username', 'unique', 'targetClass' => '\app\models\Users', 'message' => 'Данный пользователь уже существует'],
            ['username', 'string', 'min' => 2, 'max' => 255],
            ['password', 'required'],
            ['password', 'string', 'min' => 6],
        ];
    }

    /**
     * Signs user up.
     *
     * @return Users|null the saved model or null if saving fails
     */
    public function signup()
    {

        if (!$this->validate()) {
            return null;
        }

        $user = new Users();
        $user->username = $this->username;
        $user->role = Users::ROLE_USER;
        $user->active = Users::STATUS_NOT_ACTIVE;
        $user->setPassword($this->password);
        $user->generateAuthKey();
        return $user->save() ? $user : null;
    }

    public function resetPassword($username, $password) {
        $user = Users::find()->andWhere(['username' => $username])->one();
        if (!$user) {
            return false;
        }
        $user->setPassword($password);
        $user->update();
        return $user;
    }

}
