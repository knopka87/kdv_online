<?php

namespace app\models\query;

use app\models\Tokens;

/**
 * This is the ActiveQuery class for [[\app\models\Tokens]].
 *
 * @see \app\models\Tokens
 */
class TokensQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return \app\models\Tokens[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return \app\models\Tokens|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    /**
     * @return $this
     */
    public function active() {

        return $this->andWhere(['status' => Tokens::STATUS_ACTIVE]);
    }
}
