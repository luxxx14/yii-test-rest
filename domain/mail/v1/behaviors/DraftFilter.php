<?php

namespace domain\mail\v1\behaviors;

use yii2rails\domain\data\Query;
use yii2rails\domain\behaviors\query\QueryFilter;

class DraftFilter extends QueryFilter
{

    public $actions = [];

    public function prepareQuery(Query $query)
    {
        $query = Query::forge($query);
        $isDraft = $query->getWhere('is_draft');
        if ($isDraft) {
            $addressEntity = \App::$domain->mail->address->myAddress();
            $query->andWhere(['from' => $addressEntity->getEmail()]);
        }
    }

}
