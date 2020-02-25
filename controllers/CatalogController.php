<?php

namespace app\controllers;

use app\models\catalog\CatalogCategory;
use app\models\Orders;
use app\models\OrdersUsers;

class CatalogController extends \yii\web\Controller
{
    public function beforeAction($action)
    {

        if (\Yii::$app->user->isGuest || (int)\Yii::$app->user->id <= 0) {
            \Yii::$app->user->loginRequired();
        }
        return true;
    }

    public function actionIndex()
    {
        $categories = CatalogCategory::getKdvCategories();
        return $this->render('index', ['categories' => $categories]);
    }

    public function actionItems($categoryUrl) {

        $order = Orders::findActiveOrder();

        $category = CatalogCategory::find()
            ->andWhere('url = "/catalog/'.$categoryUrl.'"')
            ->addSelect(['kdv_id','name'])
            ->one();
        $items = CatalogCategory::getItems($category->kdv_id);

        return $this->render('items', [
            'items' => $items,
            'catalogName' => $category->name,
            'order' => $order
        ]);
    }

    public function actionGenerateGroupKdv() {
        CatalogCategory::getKdvCategories(true);
    }


}
