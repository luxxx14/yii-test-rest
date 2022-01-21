<?php

namespace domain\mail\v1\behaviors;

use App;
use domain\mail\v1\enums\MailTypeEnum;
use yii\helpers\ArrayHelper;
use yii2rails\domain\behaviors\query\QueryFilter;
use yii2rails\domain\data\Query;

class MailOnlyFilter extends QueryFilter
{

    public $actions = [];

    public function prepareQuery(Query $query)
    {
        $query = Query::forge($query);
        $dialogId = $query->getWhere('dialog_id');
        $discussionId = $query->getWhere('discussion_id');
        if (is_null($dialogId) && is_null($discussionId)) {
            $flowCollection = App::$domain->mail->flow->repository->all($query);
            $flowIdList = ArrayHelper::getColumn($flowCollection, 'mail_id');
            $mailQuery = new Query();
            $mailQuery->andWhere([
                'discussion_id' => null,
                'type' => MailTypeEnum::MAIL,
                'id' => $flowIdList,
            ]);
            $mailCollection = App::$domain->mail->mail->repository->all($mailQuery);
            $mailIdList = ArrayHelper::getColumn($mailCollection, 'id');
            if (!empty($mailIdList)) {
                $query->andWhere(['mail_id' => $mailIdList]);
            } else {
                $query->andWhere(['mail_id' => null]);
            }
        }
    }

}
