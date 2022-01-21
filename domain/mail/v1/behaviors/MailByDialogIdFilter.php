<?php

namespace domain\mail\v1\behaviors;

use App;
use domain\mail\v1\entities\DialogEntity;
use yii\db\Expression;
use yii2rails\domain\behaviors\query\QueryFilter;
use yii2rails\domain\data\Query;
use yii2rails\domain\enums\JoinEnum;
use yii2rails\extension\common\enums\StatusEnum;

class MailByDialogIdFilter extends QueryFilter
{

    public $actions = [];

    public function prepareQuery(Query $query)
    {
        $query = Query::forge($query);
        $dialogId = $query->getWhere('dialog_id');
        $query->removeWhere('dialog_id');
        $discussionId = $query->getWhere('discussion_id');
        $query->removeWhere('discussion_id');
        if ($dialogId) {
            $query->andWhere(['dialog_id' => $dialogId, 'flow.status' => StatusEnum::ENABLE]);
        } elseif ($discussionId) {
            $this->forgeConditionByDiscussionId($discussionId, $query);
            $query->andWhere(['flow.status' => StatusEnum::ENABLE]);
        } else {
            $query->andWhere(['status' => StatusEnum::ENABLE]);
        }
    }

    private function forgeConditionByDialogId(int $dialogId, Query $query)
    {
        /** @var DialogEntity $dialogEntity */
        $dialogEntity = App::$domain->mail->dialog->oneById($dialogId);
        $query->join(JoinEnum::RIGHT, 'mail.mail as mm', "mm.id = flow.mail_id");
        $query->andWhere(new Expression('("from" = \'' . $dialogEntity->actor .
            '\' and "to" = \'["' . $dialogEntity->contractor .
            '"]\') or ("from" = \'' . $dialogEntity->contractor . '\' and "to" = \'["' . $dialogEntity->actor . '"]\')'));
        $query->andWhere(['flow.status' => StatusEnum::ENABLE]);
    }

    private function forgeConditionByDiscussionId(int $discussionId, Query $query)
    {
        $query->join(JoinEnum::RIGHT, 'mail.mail as mm',
            "mm.id = flow.mail_id " .
            "and discussion_id = " . $discussionId);
    }

}
