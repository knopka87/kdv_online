<?php

namespace app\models\query;

use app\models\Users;
use yii\db\ActiveQuery;

/**
 * Class UserQuery
 * @package common\models\query
 * @author Eugene Terentev <eugene@terentev.net>
 */
class UserQuery extends ActiveQuery
{
    /**
     * @return $this
     */
    public function active()
    {
        $this->andWhere(['active' => Users::STATUS_ACTIVE]);
        return $this;
    }
}