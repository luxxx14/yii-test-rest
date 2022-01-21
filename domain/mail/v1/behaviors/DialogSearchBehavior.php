<?php

namespace domain\mail\v1\behaviors;

use App;
use domain\mail\v1\enums\MailTypeEnum;
use yii\helpers\ArrayHelper;
use yii2rails\domain\behaviors\query\BaseQueryFilter;
use yii2rails\domain\data\Query;
use yii2rails\extension\common\enums\StatusEnum;

class DialogSearchBehavior extends BaseQueryFilter
{
    public function prepareQuery(Query $query)
    {
        $search = $query->getWhere('search');
        $query->removeWhere('search');
        $emails = [];
        $lastMessage = [];
        if (!empty($search['text'])) {
            $search['full_name'] = $search['text'];
            $search['post_name'] = $search['text'];
            unset($search['text']);
        }
        if (!empty($search['full_name'])) {
            $workerEmails = $this->emailsByFullName($search['full_name']);
            $emails = ArrayHelper::merge($emails, $workerEmails);
        }
        if (!empty($search['post_name'])) {
            $workerEmails = $this->emailsByPostName($search['post_name']);
            $emails = ArrayHelper::merge($emails, $workerEmails);
        }
        if (!empty($search['last_message'])) {
            $lastMessage = $this->emailsByLastMessage($search['last_message']);
        }
        if (!empty($emails)) {
            $query->andWhere([
                'contractor' => $emails,
            ]);
        }
        if (!empty($lastMessage)) {
            $query->andWhere([
                'id' => $lastMessage,
            ]);
        }
        return $query;
    }

    private function emailsByFullName($fullName)
    {
        $personQuery = new Query;
        $personQuery->andWhere([
            'search' => [
                'name' => $fullName,
            ],
        ]);
        $personCollection = App::$domain->user->person->repository->all($personQuery);
        if (!empty($personCollection)) {
            $personEmails = ArrayHelper::getColumn($personCollection, 'email');
            //TODO: диалоги не должны быть подвязаны на воркерах
            /*
                $personIds = ArrayHelper::getColumn($personCollection, 'id');
                $workerCollection = App::$domain->staff->worker->allByPersonIds($personIds);
                $workerEmails = ArrayHelper::getColumn($workerCollection, 'corporate_email');
                return $workerEmails;
            */
            return $personEmails;
        } else {
            return [''];
        }
    }

    private function emailsByPostName($postName)
    {
        $postQuery = new Query;
        $postQuery->andWhere([
            'search' => [
                'value' => $postName,
            ],
        ]);
        $postCollection = App::$domain->reference->item->all($postQuery);
        if (!empty($postCollection)) {
            $postIds = ArrayHelper::getColumn($postCollection, 'id');
            $workerCollection = App::$domain->staff->worker->allByPostIds($postIds);
            $workerEmails = ArrayHelper::getColumn($workerCollection, 'corporate_email');
            return $workerEmails;
        } else {
            return [''];
        }
    }

    private function emailsByLastMessage($lastMessage)
    {
        $messageQuery = new Query();
        $messageQuery->andWhere(['type' => MailTypeEnum::MESSAGE, 'status' => StatusEnum::ENABLE]);
        $messageCollection = App::$domain->mail->mail->repository->all($messageQuery);
        $idMessageCollection = ArrayHelper::getColumn($messageCollection, 'id');
        $addressEntity = App::$domain->mail->address->myAddress();
        $flowQuery = new Query();
        $flowQuery->select(['dialog_id', 'max(mail_id) as mail_id']);
        $flowQuery->andWhere(['mail_id' => $idMessageCollection, 'mail_address' => $addressEntity->getEmail(), 'status' => StatusEnum::ENABLE]);
        $flowQuery->groupBy(['dialog_id']);
        $flowCollection = App::$domain->mail->flow->repository->all($flowQuery);
        $idFlowCollection = ArrayHelper::getColumn($flowCollection, 'mail_id');
        $messageQuery = new Query();
        $messageQuery->andWhere([
            'id' => $idFlowCollection,
            'search' => [
                'content' => $lastMessage,
            ],
        ]);
        $messageQuery->orderBy(['created_at' => SORT_DESC]);
        $messageCollection = App::$domain->mail->mail->repository->all($messageQuery);
        $idMessageCollection = ArrayHelper::getColumn($messageCollection, 'id');
        $flowQuery = new Query();
        $flowQuery->andWhere(['mail_id' => $idMessageCollection]);
        $flowCollection = App::$domain->mail->flow->repository->all($flowQuery);
        $idFlowCollection = array_unique(ArrayHelper::getColumn($flowCollection, 'dialog_id'));
        if (!empty($idFlowCollection)) {
            return $idFlowCollection;
        } else {
            return [''];
        }
    }

}