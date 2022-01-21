<?php

namespace domain\mail\v1\behaviors;

use App;
use domain\mail\v1\enums\MailTypeEnum;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii2rails\domain\behaviors\query\BaseQueryFilter;
use yii2rails\domain\data\Query;

class MailSearchBehavior extends BaseQueryFilter
{
    public function prepareQuery(Query $query)
    {
        $search = $query->getWhere('search');
        if (!empty($search)) {
            $query->removeWhere('search');

            $flowCollection = App::$domain->mail->flow->repository->all($query);
            $selfMailIdList = ArrayHelper::getColumn($flowCollection, 'mail_id');

            if (!empty($search['text'])) {
                $search['to'] = $search['text'];
                $search['from'] = $search['text'];
                $search['subject'] = $search['text'];
                $search['content'] = $search['text'];
            }

            $mailIdList = [];
            if (!empty($search['from'])) {
                $mailIdList = array_merge($mailIdList, $this->searchByFrom($selfMailIdList, $search['from']));
            }

            if (!empty($search['to'])) {
                $mailIdList = array_merge($mailIdList, $this->searchByTo($selfMailIdList, $search['to']));
            }

            if (!empty($search['subject'])) {
                $mailIdList = array_merge($mailIdList, $this->searchBySubject($selfMailIdList, $search['subject']));
            }

            if (!empty($search['content'])) {
                $mailIdList = array_merge($mailIdList, $this->searchByContent($selfMailIdList, $search['content']));
            }

            if (!empty($mailIdList)) {
                $query->andWhere(['mail_id' => $mailIdList]);
            } else {
                $query->andWhere(['mail_id' => null]);
            }
        }
        return $query;
    }

    private function searchByFrom($selfMailIdList, $from) {
        $mailQuery = new Query();
        $mailQuery->andWhere([
            'id' => $selfMailIdList,
            'discussion_id' => null,
            'type' => MailTypeEnum::MAIL,
            'search' => [
                'from' => $from,
            ]
        ]);
        $mailCollection = App::$domain->mail->mail->repository->all($mailQuery);
        return ArrayHelper::getColumn($mailCollection, 'id');

    }

    private function searchByTo($selfMailIdList, $to) {
        $mailQuery = new Query();
        $mailQuery->andWhere([
            'id' => $selfMailIdList,
            'discussion_id' => null,
            'type' => MailTypeEnum::MAIL,
            'search' => [
                'to' => $to,
            ]
        ]);
        $mailCollection = App::$domain->mail->mail->repository->all($mailQuery);
        return ArrayHelper::getColumn($mailCollection, 'id');
    }

    private function searchBySubject($selfMailIdList, $subject) {
        $mailQuery = new Query();
        $mailQuery->andWhere([
            'id' => $selfMailIdList,
            'discussion_id' => null,
            'type' => MailTypeEnum::MAIL,
            'search' => [
                'subject' => $subject,
            ]
        ]);
        $mailCollection = App::$domain->mail->mail->repository->all($mailQuery);
        return ArrayHelper::getColumn($mailCollection, 'id');
    }

    private function searchByContent($selfMailIdList, $content) {
        $mailQuery = new Query();
        $mailQuery->andWhere([
            'id' => $selfMailIdList,
            'discussion_id' => null,
            'type' => MailTypeEnum::MAIL,
            'search' => [
                'content' => $content,
            ]
        ]);
        $mailCollection = App::$domain->mail->mail->repository->all($mailQuery);
        return ArrayHelper::getColumn($mailCollection, 'id');
    }

}