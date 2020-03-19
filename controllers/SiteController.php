<?php

namespace app\controllers;

use app\models\kdv\Kdv;
use app\models\kdv\KdvBasket;
use app\models\kdv\KdvProduct;
use app\models\Notification;
use app\models\SignupForm;
use app\models\Tokens;
use app\models\Tools;
use app\models\Users;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        if (!Yii::$app->user->isGuest) {
            Yii::$app->response->redirect(['catalog/index']);
        }
        return $this->render('index');
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    public function actionForget($accessToken) {

        $model = new SignupForm();

        $findUser = Users::find()->andWhere(['accessToken' => $accessToken])->one();

        if (!$findUser) {
            \Yii::$app->session->setFlash('error', 'Пользователь не найден!');
        }

        if ($model->load(Yii::$app->request->post())) {
            if ($user = $model->resetPassword($findUser->username, $model->password)) {
                if (Yii::$app->getUser()->login($user)) {
                    return $this->goHome();
                }
            }
        }

        return $this->render('forget', [
            'user' => $findUser,
            'model' => $model
        ]);
    }

    public function actionSignup()
    {
        $model = new SignupForm();

        if ($model->load(Yii::$app->request->post())) {
            if ($user = $model->signup()) {
                if (Yii::$app->getUser()->login($user)) {
                    return $this->goHome();
                }
            }
        }

        return $this->render('signup', [
            'model' => $model,
        ]);
    }

    public function actionSaveToken() {

        $request = Yii::$app->request;
        if ($post = $request->post()) {
            $token = new Tokens();
            if ($token->load($post, '')) {
                $findToken = Tokens::find()->andWhere(['token' => $token->token])->one();
                if ($findToken) {
                    // по идее не должны сюда попадать..
                    $findToken->user_id = $token->user_id;
                    $findToken->status = Tokens::STATUS_ACTIVE;
                    $findToken->update();
                }
                else {
                    $token->status = Tokens::STATUS_ACTIVE;
                    $token->insert();
                }

            }
        }
    }

    public function actionNotification() {

        if (!Yii::$app->user->identity->isAdmin()) {
            Yii::$app->response->redirect(['site/index']);
        }

        $notification = new Notification();
        $res = null;

        $request = Yii::$app->request;
        if ($post = $request->post()) {
            $notificationPost = new Notification();
            $notificationPost->title = $post['Notification']['title'];
            $notificationPost->body = $post['Notification']['body'];
            $notificationPost->clickAction = $post['Notification']['click_action'];
            $res = $notificationPost->send();

            if ($res) {
                Yii::$app->session->setFlash('success', 'Сообщение успешно отправлено');
            }
            else {
                Yii::$app->session->setFlash('error', 'Сообщение не удалось отправить');
            }

        }

        return $this->render('notification', ['notificationModel' => $notification, 'res' => $res]);
    }

    public function actionTest() {

        /*$kdvProduct = new KdvBasket();
        $kdvProduct->addBasket(1378, 2);
        return $kdvProduct->sincBasket(12);*/
        $kdvProduct = new KdvProduct();
        return Tools::dd($kdvProduct->getProductInfo(277));

    }
    /*
    public function actionAddAdmin(){
        $model = Users::find()->where(['username' => 'a.yanover'])->one();
        if (empty($model)) {
            $user = new Users();
            $user->username = 'a.yanover';
            $user->setPassword('130387');
            $user->role = Users::ROLE_ADMINISTRATOR;
            $user->generateAuthKey();
            if ($user->save()) {
                echo 'good';
            }
        }
    }*/
}
