<?php

namespace app\models\catalog;

use app\models\kdv\KdvProduct;
use GuzzleHttp\Client;
use phpQuery;
use Yii;

/**
 * This is the model class for table "kdv_categories".
 *
 * @property int $id
 * @property int $kdv_id
 * @property string $name
 * @property string $url
 * @property string $image_src
 */
class CatalogCategory extends \yii\db\ActiveRecord
{
    private static $kdvUrl = 'https://kdvonline.ru/';
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'kdv_categories';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'url'], 'required'],
            [['name', 'url', 'image_src'], 'string', 'max' => 255],
            [['url'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'url' => 'Url',
            'image_src' => 'Image Src',
        ];
    }

    /**
     * {@inheritdoc}
     * @return CatalogCategoryQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new CatalogCategoryQuery(get_called_class());
    }

    public static function getKdvCategories($useParcer = false) {

        if ($useParcer) {
            $categories = [];
            $kdvCategories = self::parseKdvCategories();
            /** @var CatalogCategory $category */
            foreach ($kdvCategories as $category) {
                $category->save();
                //var_dump($category->getErrors());
                $categories[] = $category->toArray();
            }
            return $categories;
        }

        return static::find()->orderBy('name')->all();
    }

    private static function parseKdvCategories() {
        $categories = [];

        // создаем экземпляр класса
        $client = new Client();
        // отправляем запрос к странице Яндекса
        $res = $client->request('GET', self::$kdvUrl);
        // получаем данные между открывающим и закрывающим тегами body
        $body = $res->getBody();
        // подключаем phpQuery
        $elements = phpQuery::newDocumentHTML($body)->find('div.catalog__XSRW-')->find('.catalog-item__1lF5H');
        echo $elements->count();
        foreach ($elements as $document) {
            $links = $document->getElementsByTagName('a');
            $kdvCategory = new CatalogCategory();
            foreach ($links as $category) {
                $kdvCategory->url = $category->getAttribute('href');
                $kdvCategory->image_src = str_replace('40x40', '80x80', $category->getElementsByTagName('img')->item(0)->getAttribute('src'));
                $kdvCategory->name = $category->getElementsByTagName('div')->item(0)->textContent;
                $kdvCategory->kdv_id = preg_replace('#.+-([0-9]+)$#', '$1', $kdvCategory->url);
                $categories[] = $kdvCategory;
            }
        }
        return $categories;
    }

    public static function getItems($categoryId) {
        $items = [];

        $kdvProduct = new KdvProduct();
        $kdvItems = $kdvProduct->getProductList($categoryId);

        foreach ($kdvItems as $kdvItem) {
            $item = [];
            if ($kdvItem->isAvailable) {
                $item['img'] =
                    'https://api.magonline.ru/thumbnail/220x220/' .
                    sprintf("%02d", (int)($kdvItem->image->id / 1000)) . '/' .
                    ($kdvItem->image->id % 1000) . '/' .
                    $kdvItem->image->id . '.png';
                $item['name'] = $kdvItem->name;
                $item['kdv_price'] = $kdvItem->quant->pricePerUnit;
                $item['price'] = ceil($kdvItem->quant->pricePerUnit*105)/100;
                $item['id'] = $kdvItem->id;
                $item['kdv_url'] = 'https://kdvonline.ru/product/'.$kdvItem->code.'-'.$kdvItem->id;
                $item['unit'] = $kdvItem->quant->unit;
                $item['multiple'] = $kdvItem->quant->multiple;
                $items[] = $item;
            }
        }

        return $items;
    }
}
