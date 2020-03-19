<?php

namespace app\models\kdv;


class Kdv
{
    private $token = '0f91d406518837b7499a30984647851d97096cd3-8c367eaf99d6905ecf4905fef0daaf110325cbab';
    private static $_instance;

    private function __construct()
    {
        $this->auth()->setLocation(228723);
    }

    public static function getInstance()
    {
        if (self::$_instance === null) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    private function auth()
    {

        $kdvLogin = \Yii::$app->params['kdv_login'];
        $kdvPassword = \Yii::$app->params['kdv_password'];

        $this->curl("{\"query\":\"\\r\\n  mutation (\$identity: String!, \$password: String!) {\\r\\n    signIn (identity: \$identity, credential: \$password) {\\r\\n  id\\r\\n  lastname\\r\\n  firstname\\r\\n  email\\r\\n  phone\\r\\n  type\\r\\n  requisite {\\r\\n  organizationName\\r\\n  inn\\r\\n  kpp\\r\\n  address\\r\\n}\\r\\n  subscribe\\r\\n  isEmailVerified\\r\\n  isPhoneVerified\\r\\n  isRegistered\\r\\n  emailSignature\\r\\n  contractorStatus\\r\\n}\\r\\n  }\",\"variables\":{\"identity\":\"$kdvLogin\",\"password\":\"$kdvPassword\"}}");
        return $this;
    }

    private function setLocation($locationId) {
        $this->curl("{\"query\":\"mutation (\$id: Int!) {\\r\\n    setLocationById (id: \$id) {\\r\\n  location {\\r\\n  id\\r\\n  title\\r\\n  geoCenter {\\r\\n  latitude\\r\\n  longitude\\r\\n}\\r\\n  geoPolygon {\\r\\n  list {\\r\\n  latitude\\r\\n  longitude\\r\\n}\\r\\n}\\r\\n  parents {\\r\\n  id\\r\\n  title\\r\\n  geoCenter {\\r\\n  latitude\\r\\n  longitude\\r\\n}\\r\\n  geoPolygon {\\r\\n  list {\\r\\n  latitude\\r\\n  longitude\\r\\n}\\r\\n}\\r\\n  locationKind {\\r\\n  id\\r\\n  name\\r\\n  shortName\\r\\n}\\r\\n}\\r\\n  locationKind {\\r\\n  id\\r\\n  name\\r\\n  shortName\\r\\n}\\r\\n}\\r\\n  stocks {\\r\\n  id\\r\\n  name\\r\\n  description\\r\\n  vendorGuid\\r\\n}\\r\\n}\\r\\n  }\",\"variables\":{\"id\":$locationId}}");
        return $this;
    }

    public function curl($postFields)
    {

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.magonline.ru/api/graphql",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $postFields,
            CURLOPT_HTTPHEADER => array(
                "Connection: keep-alive",
                "Accept: application/json, text/plain, */*",
                "token: $this->token",
                "Origin: https://kdvonline.ru",
                "User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/79.0.3945.136 YaBrowser/20.2.2.177 Yowser/2.5 Yptp/1.21 Safari/537.36",
                "DNT: 1",
                "Content-Type: application/json",
                "Sec-Fetch-Site: cross-site",
                "Sec-Fetch-Mode: cors",
                "Accept-Encoding: gzip, deflate, br",
                "Accept-Language: ru,en;q=0.9,la;q=0.8,uk;q=0.7,uz;q=0.6"
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }
}
