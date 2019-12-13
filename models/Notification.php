<?php
/**
 * Created by PhpStorm.
 * User: a.yanover
 * Date: 12.12.2019
 * Time: 9:50
 */

namespace app\models;

use yii\base\Model;


/**
 * Class Notification
 * @package app\models
 *
 * @property string title
 * @property string body
 * @property string $iconSrc
 * @property string clickAction
 */
class Notification extends Model
{
    public $iconSrc = 'https://kdvonline.ru/static/icons/apple-touch-icon-180x180.png';
    public $title;
    public $body;
    public $clickAction;

    /**
     * @return mixed
     */
    public function send($userList = null) {

        $url = 'https://fcm.googleapis.com/fcm/send';
        $YOUR_API_KEY = 'AAAA-1V76lI:APA91bFs9lqwuEQ37E01rfIPHZLDp22l4_OSnaBzasG9vvte9AabfLRCAF0bHBhlrQizGL5RuNX6GwnIu4D3WHRUGl4jnq-JItBLK-Y1bv-dpUx59B_p4uAZ6pklYKm_8RFPGNarlHO5'; // Server key

        $request_body = [
            'registration_ids' => $this->getTokens($userList),
            'notification' => [
                'title' => $this->title,
                'body' => $this->body,
                'icon' => $this->iconSrc,
                'click_action' => $this->clickAction,
            ],
        ];
        $fields = json_encode($request_body);

        $request_headers = [
            'Content-Type: application/json',
            'Authorization: key=' . $YOUR_API_KEY,
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $request_headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    /**
     * @param null $userId
     * @return array
     */
    public static function getTokens($userList = null) {
        $return = [];
        $tokenList = Tokens::find()->filterWhere(['user_id' => $userList])->addSelect('token')->all();
        foreach ($tokenList as $token) {
            $return[] = $token->token;
        }
        return $return;
    }

}