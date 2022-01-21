<?php

namespace domain\mail\v1\services;

use App;
use domain\mail\v1\entities\DiscussionEntity;
use domain\mail\v1\entities\DiscussionMemberEntity;
use domain\mail\v1\enums\MemberEnum;
use domain\mail\v1\interfaces\services\DiscussionInterface;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UnprocessableEntityHttpException;
use yii2rails\domain\behaviors\query\QueryFilter;
use yii2rails\domain\data\Query;
use yii2rails\domain\services\base\BaseActiveService;
use yii2rails\domain\values\TimeValue;
use yii2rails\extension\common\enums\StatusEnum;

/**
 * Class DiscussionService
 *
 * @package domain\mail\v1\services
 *
 * @property-read \domain\mail\v1\Domain $domain
 * @property-read \domain\mail\v1\interfaces\repositories\DiscussionInterface $repository
 */
class DiscussionService extends BaseActiveService implements DiscussionInterface
{

    protected function prepareQuery(Query $query = null)
    {
        $selfMemberCollection = App::$domain->mail->discussionMember->allBySelf();
        $discussionIdList = ArrayHelper::getColumn($selfMemberCollection, 'discussion_id');
        $query = Query::forge($query);
        if (!empty($discussionIdList)) {
            $query->andWhere(['id' => $discussionIdList]);
            $query->andWhere(['status' => StatusEnum::ENABLE]);
        } else {
            $query->andWhere(['id' => null]);
        }
        $query->orderBy(['updated_at' => SORT_DESC]);
        return $query;
    }

    public function create($data)
    {
        $addressEntity = \App::$domain->mail->address->myAddress();

        $data['member_emails'] = explode(',', $data['member_emails']);
        $data['member_emails'] = array_diff($data['member_emails'], [$addressEntity->email]);

        try {
            \App::$domain->staff->worker->oneSelf();
        } catch (NotFoundHttpException $e) {
            throw new ForbiddenHttpException(\Yii::t('mail/discussion', 'can_not_create_discussion_permission_denied'));
        }
        if (!isset($data['member_emails'])) {
            throw new UnprocessableEntityHttpException(Yii::t('mail/discussion', 'can_not_create_discussion_without_members'));
        }
        if (count($data['member_emails']) < 2) {
            throw new UnprocessableEntityHttpException(Yii::t('mail/discussion', 'so_few_members'));
        }
        $data['subject'] = preg_replace('/\s+/', ' ', $data['subject']);
        $discussionEntity = $this->isDiscussionExist($data['subject'], $data['member_emails']);
        if (!empty($discussionEntity)) {
            return $discussionEntity;
        }

        $discussionEntity = new DiscussionEntity($data);
        $discussionEntity->validate();
        $this->repository->insert($discussionEntity);
        $selfMemberEntity = new DiscussionMemberEntity([
            'discussion_id' => $discussionEntity->id,
            'email' => $addressEntity->email,
            'role' => MemberEnum::CREATOR
        ]);
        App::$domain->mail->discussionMember->repository->insert($selfMemberEntity);

        foreach ($data['member_emails'] as $memberMail) {
            App::$domain->mail->discussionMember->create([
                'discussion_id' => $discussionEntity->id,
                'email' => $memberMail
            ]);
        }

        return $discussionEntity;
    }

    public function isDiscussionExist($subject, $emails)
    {
        try {
            $addressEntity = \App::$domain->mail->address->myAddress();
            $emails[] = $addressEntity->getEmail();
            $query = new Query();
            $query->andWhere(['subject' => str_replace('Re: ', '', $subject), 'status' => StatusEnum::ENABLE]);
            $query->with('members');
            $discussionEntity = $this->repository->one($query);
            $discussionMembers = ArrayHelper::getColumn($discussionEntity->members, 'email');
            if (empty(array_diff($emails, $discussionMembers))) {
                return $discussionEntity;
            } else {
                throw new UnprocessableEntityHttpException(Yii::t('mail/discussion', 'not_unique_subject'));
            }
        } catch (NotFoundHttpException $e) {
            return null;
        }
    }

    public function getBySubjectAndEmails(string $subject, array $emails)
    {
        $query = new Query();
        $pattern = '#^([R|r][e|E]\s{0,}:\s{0,})(.*)#';
        preg_match($pattern, $subject, $matches);
        if (key_exists(2, $matches)) {
            $subject = $matches[2];
        }
        $query->andWhere(['status' => StatusEnum::ENABLE, 'subject' => $subject]);
        $query->with('members');
        $discussions = $this->repository->all($query);
        $discussionId = null;
        /** @var DiscussionEntity $discussion */
        foreach ($discussions as $discussion) {
            $discussionMembers = ArrayHelper::getColumn($discussion->members, 'email');
            if (empty(array_diff($discussionMembers, $emails))) {
                $discussionId = $discussion->id;
            }
        }
        return $discussionId;
    }

    public function deleteById($id)
    {
        $addressEntity = \App::$domain->mail->address->myAddress();
        $query = new Query;
        $query->andWhere(
            [
                'discussion_id' => $id,
                'email' => $addressEntity->email,
                'role' => [MemberEnum::CREATOR, MemberEnum::ADMIN]
            ]);
        try {
            $memberEntity = App::$domain->mail->discussionMember->one($query);
            parent::updateById($id, ['status' => StatusEnum::REJECTED]);
            $query = new Query;
            $query->andWhere(['discussion_id' => $id]);
            $mailEntities = App::$domain->mail->mail->all($query);
            $mailIdList = ArrayHelper::getColumn($mailEntities, 'id');
            $query = new Query;
            $query->andWhere(['mail_id' => $mailIdList]);
            $flowEntities = App::$domain->mail->flow->repository->all($query);
            foreach ($flowEntities as $flowEntity) {
                $flowEntity->status = StatusEnum::REJECTED;
                App::$domain->mail->flow->update($flowEntity);
            }
        } catch (NotFoundHttpException $e) {
            throw new ForbiddenHttpException(Yii::t('mail/discussion', 'can_not_delete_discussion'));
        }
    }

    public function deleteMessageById($id)
    {
        $addressEntity = \App::$domain->mail->address->myAddress();
        $query = new Query;
        $query->andWhere(['discussion_id' => $id]);
        $mailEntities = App::$domain->mail->mail->all($query);
        $mailIdList = ArrayHelper::getColumn($mailEntities, 'id');
        $query = new Query;
        $query->andWhere(['mail_id' => $mailIdList, 'mail_address' => $addressEntity->email]);
        $flowEntities = App::$domain->mail->flow->repository->all($query);
        foreach ($flowEntities as $flowEntity) {
            $flowEntity->status = StatusEnum::REJECTED;
            App::$domain->mail->flow->update($flowEntity);
        }
    }

    public function updateMessageCount($discussionId)
    {
        $addressEntity = \App::$domain->mail->address->myAddress();
        $query = new Query;
        $query->andWhere([
            'and',
            ['discussion_id' => $discussionId],
            ['!=', 'email', $addressEntity->getEmail()]
        ]);
        $discussionMemberEntityList = \App::$domain->mail->discussionMember->all($query);
        /** @var DiscussionMemberEntity $discussionMemberEntity */
        foreach ($discussionMemberEntityList as $discussionMemberEntity) {
            $discussionMemberEntity->new_message_count += 1;
            \App::$domain->mail->discussionMember->update($discussionMemberEntity);
        }

        /** @var DiscussionEntity $discussionEntity */
        $discussionEntity = $this->oneById($discussionId);
        // TODO: Костыль
        $discussionEntity->updated_at  = new TimeValue(time());
        parent::update($discussionEntity);
    }

    public function oneById($id, Query $query = null)
    {
        $addressEntity = \App::$domain->mail->address->myAddress();
        $memberQuery = new Query();
        $memberQuery->andWhere([
            'discussion_id' => $id,
            'email' => $addressEntity->getEmail()
        ]);
        /** @var DiscussionMemberEntity $discussionMemberEntity */
        $discussionMemberEntity = \App::$domain->mail->discussionMember->one($memberQuery);
        $discussionMemberEntity->new_message_count = 0;
        App::$domain->mail->discussionMember->update($discussionMemberEntity);
        return parent::oneById($id, $query);
    }

    public function touch($id) {
        $addressEntity = \App::$domain->mail->address->myAddress();
        $query = new Query();
        $query->andWhere(['discussion_id' => $id]);
        $mailCollection = \App::$domain->mail->mail->repository->all($query);
        $mailIdCollection = ArrayHelper::getColumn($mailCollection, 'id');
        $query = new Query();
        $query->andWhere([
            'seen' => false,
            'mail_address' => $addressEntity->getEmail(),
            'mail_id' => $mailIdCollection,
        ]);
        $flowCollection = \App::$domain->mail->flow->repository->all($query);
        $flowIdCollection = ArrayHelper::getColumn($flowCollection, 'id');
        \App::$domain->mail->flow->touch($flowIdCollection, true);
    }

}
