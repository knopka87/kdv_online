<?php

namespace app\models\kdv;


use app\models\OrderPositions;
use Yii;

class KdvBasket
{
    private $kdv;

    public function __construct()
    {
        $this->kdv = Kdv::getInstance();
    }

    public function getBasket()
    {
        $data = json_decode($this->kdv->curl(
            "{\"query\":\"\\r\\n"."".
            "  {\\r\\n".
            "    defaultCart {\\r\\n".
                    "  id\\r\\n".
                    "  name\\r\\n".
                    "  products {\\r\\n".
                        "  id\\r\\n".
                        "  name\\r\\n".
                        //"  description\\r\\n".
                        "  amount\\r\\n".
                        "  image {\\r\\n".
                            "  id\\r\\n".
                            "  title\\r\\n".
                            //"  alt\\r\\n".
                            "}\\r\\n".
                        "  price\\r\\n".
                        //"  isNew\\r\\n".
                        //"  isHit\\r\\n".
                        //"  isFavorite\\r\\n".
                        "  isAvailable\\r\\n".
                        //"  isWeight\\r\\n".
                        //"  isSubscribed\\r\\n".
                        "  isVeterinaryControl\\r\\n".
                        "  code\\r\\n".
                        /*"  quant {\\r\\n".
                            "  code\\r\\n".
                            "  fullName\\r\\n".
                            "  shortName\\r\\n".
                            "  multiple\\r\\n".
                            "  pricePerUnit\\r\\n".
                            "  unit\\r\\n".
                            "}\\r\\n".*/
                        "  categories {\\r\\n".
                        "  id\\r\\n".
                        "  name\\r\\n".
                        "  code\\r\\n".
                        "}\\r\\n".
                    "}\\r\\n".
                "}\\r\\n".
            "  }\",\"variables\":{}}"));

        return $data->data->defaultCart->products;
    }

    public function addBasket($posId, $amount) {
        $data = json_decode($this->kdv->curl(
            "{\"query\":\"mutation (\$input: [CartProductInput]!) {\\r\\n".
            "    setProductsInCart(input: \$input) {\\r\\n".
                "  id\\r\\n".
                "  products {\\r\\n".
                    "  id\\r\\n".
                    "  amount\\r\\n".
                    "  isAvailable\\r\\n".
                    "}\\r\\n".
                "}\\r\\n".
            "  }\",\"variables\":{\"input\":[{\"id\":$posId,\"amount\":$amount}]}}"));

        return $data->data->setProductsInCart->products;
    }

    public function delBasket($posId) {
        $this->kdv->curl(
            "{\"query\":\"mutation (\$id: [Int]!) {\\r\\n".
        "    delProductsFromCart(id: \$id) {\\r\\n".
            "  id\\r\\n".
            "}\\r\\n".
            "  }\",\"variables\":{\"id\":[$posId]}}");
        return $this;
    }

    public function changeBasket($posId, $amount) {
        return $this->addBasket($posId, $amount);
    }

    public function clearBasket() {
        $this->kdv->curl(
            "{\"query\":\"mutation {\\r\\n".
            "    clearCart {\\r\\n".
                "  id\\r\\n".
                "}\\r\\n".
            "  }\",\"variables\":{}}");
        return $this;
    }

    public function updateBasket($posId, $orderId) {
        $position = OrderPositions::find()
            ->addSelect('SUM(amount) as amount, multiple')
            ->andWhere(['kdv_id' => $posId, 'order_id' => $orderId])
            ->one();
        $kdvAmount = (int)($position->amount / $position->multiple);
        if ($kdvAmount > 0) {
            $this->addBasket($posId, $kdvAmount);
        }
        else {
            $this->delBasket($posId);
        }

    }

    public function sincBasket($orderId)
    {
        $kdvBasket = $this->getBasket();
        foreach ($kdvBasket as $position) {
            $kdvPositionList[$position->id] = $position;
        }

        $ourPositionList = OrderPositions::find()
            ->addSelect(['kdv_id', 'SUM(amount) as amount', 'order_id', 'caption', 'multiple'])
            ->andWhere(['order_id' => $orderId])
            ->groupBy('kdv_id')
            ->all();
        $message = '';
        /** @var OrderPositions $ourPosition */
        foreach ($ourPositionList as $ourPosition) {

            $kdvAmount = (int)($ourPosition->amount / $ourPosition->multiple);

            if (!isset($kdvPositionList[$ourPosition->kdv_id])) {
                $message .= '<br>'.$ourPosition->caption.' - не найдена в корзине КДВ';
                // попытаться добавить в корзину КДВ
                $newKdvBasket = [];
                if ($kdvAmount > 0) {
                    $newKdvBasket = $this->addBasket($ourPosition->kdv_id, $kdvAmount);
                }
                $findPosition = false;
                $isAvailable = true;
                foreach ($newKdvBasket as $newPosition) {
                    if ($newPosition->id == $ourPosition->kdv_id) {
                        if (!$newPosition->isAvailable) {
                            // товар не в наличии - обратно удаляем из корзины КДВ
                            $this->delBasket($ourPosition->kdv_id);
                            $message .= ', нет в наличии на КДВ';
                            $isAvailable = false;
                        }
                        else {
                            $message .= ', добавлена';
                            $findPosition = true;
                        }
                    }
                }

                if (!$findPosition) {
                    // не в наличии - удалить у всех пользователей данную позицию
                    if (!$isAvailable) {
                        OrderPositions::deletePositionForAllUser($ourPosition->order_id, $ourPosition->kdv_id);
                        $message .= ', удалена у всех пользователей';
                    }
                    else {
                        $message .= ', не достаточно кол-ва до полной коробки!';
                    }
                }

            }
            elseif (!$kdvPositionList[$ourPosition->kdv_id]->isAvailable) {
                $message .= '<br>'.$ourPosition->caption.' - нет в наличии на КДВ';
                // нет в наличии на сайте КДВ - удаляем у нас и на сайте КДВ
                OrderPositions::deletePositionForAllUser($ourPosition->order_id, $ourPosition->kdv_id);
                unset($kdvPositionList[$ourPosition->kdv_id]);
            }
            elseif ($kdvPositionList[$ourPosition->kdv_id]->amount == $kdvAmount) {
                if ($ourPosition->amount % $ourPosition->multiple !== 0) {
                    $message .= '<br>'.$ourPosition->caption.' - заказано не целое кол-во коробок!';
                }
                //$message .= '<br>'.$ourPosition->caption.' - всё хорошо';
                unset($kdvPositionList[$ourPosition->kdv_id]);
            }
            else {

                $message .='<br>'.$ourPosition->caption.' - обновлено кол-во на КДВ (с '.$kdvPositionList[$ourPosition->kdv_id]->amount.' до  '. $kdvAmount.')';

                // обновить кол-во в корзине кдв
                $this->updateBasket($ourPosition->kdv_id, $ourPosition->order_id);
                unset($kdvPositionList[$ourPosition->kdv_id]);
            }
        }
        // всё что осталось ещё в $kdvPositionList - удаляем из корзины кдв
        foreach ($kdvPositionList as $kdvPosition) {
            $message .= '<br>'.$kdvPosition->name.' - удалена из корзины КДВ';
            $this->delBasket($kdvPosition->id);
        }

        if (!empty($message)) {
            $message = '<b>Изменения в связи с синхронизацией позиций заказа с корзиной на КДВ:</b>'.$message;
        }
        else {
            $message = 'Синхронизация прошла без изменений';
        }
        return $message;
    }
}
