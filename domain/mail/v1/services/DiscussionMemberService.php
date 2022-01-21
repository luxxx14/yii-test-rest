<?php

namespace domain\mail\v1\services;

use domain\mail\v1\entities\DiscussionMemberEntity;
use domain\mail\v1\enums\MemberEnum;
use domain\mail\v1\interfaces\services\DiscussionMemberInterface;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UnprocessableEntityHttpException;
use yii2rails\domain\data\Query;
use yii2rails\domain\helpers\ErrorCollection;
use yii2rails\domain\services\base\BaseActiveService;

/**
 * Class DiscussionMemberService
 *
 * @package domain\mail\v1\services
 *
 * @property-read \domain\mail\v1\Domain $domain
 * @property-read \domain\mail\v1\interfaces\repositories\DiscussionMemberInterface $repository
 */
class DiscussionMemberService extends BaseActiveService implements DiscussionMemberInterface
{

    public function allBySelf(Query $query = null)
    {
        $addressEntity = \App::$domain->mail->address->myAddress();
        $query = Query::forge($query);
        $query->andWhere(['email' => $addressEntity->email]);
        return $this->all($query);
    }

    public function create($data)
    {
        $discussionId = ArrayHelper::getValue($data, 'discussion_id');
        $this->isAdmin($discussionId, 'can_not_add_members');

        $emailCollection = explode(',', ArrayHelper::getValue($data, 'email', null));

        foreach ($emailCollection as $email) {
            $discussionMemberEntity = new DiscussionMemberEntity();
            $discussionMemberEntity->discussion_id = $discussionId;
            $discussionMemberEntity->email = $email;
            $discussionMemberEntity->validate();
        }

        $query = new Query;
        $query->andWhere([
            'discussion_id' => $discussionId,
            'email' => $emailCollection
        ]);
        $discussionMember = $this->all($query);
        if (count($discussionMember) > 0) {
            throw new UnprocessableEntityHttpException(Yii::t('mail/discussion', 'can_not_add'));
        }

        foreach ($emailCollection as $email) {
            $discussionMemberEntity = new DiscussionMemberEntity();
            $discussionMemberEntity->discussion_id = $discussionId;
            $discussionMemberEntity->email = $email;
            $discussionMemberEntity = parent::create($discussionMemberEntity->toArray());
        }
        return $discussionMemberEntity;
    }

    public function updateById($id, $data)
    {
        $discussionMemberEntity = \App::$domain->mail->discussionMember->oneById($id);
        $isInternal = \App::$domain->mail->address->isInternal($discussionMemberEntity->email);
        if (!$isInternal) {
            throw new UnprocessableEntityHttpException(Yii::t('mail/discussion', 'can_not_update_not_internal_member'));
        }
        /** @var DiscussionMemberEntity $memberEntity */
        $memberEntity = $this->oneById($id);
        $this->isCreatorOrAdmin($memberEntity->discussion_id, 'can_not_update_members');
        if ($data['role'] == MemberEnum::CREATOR) {
            throw new UnprocessableEntityHttpException(Yii::t('mail/discussion', 'can_not_set_role'));
        }
        foreach ($data as $property => $value) {
            if (!in_array($property, $memberEntity->fillable)) {
                unset($data[$property]);
            }
        }
        parent::updateById($id, $data);
    }

    public function deleteById($id)
    {
        /** @var DiscussionMemberEntity $memberEntity */
        $memberEntity = $this->oneById($id);

        $addressEntity = \App::$domain->mail->address->myAddress();
        $query = new Query();
        $query->andWhere([
            'discussion_id' => $memberEntity->discussion_id,
            'email' => $addressEntity->email
        ]);
        /** @var DiscussionMemberEntity $discussionMemberSelf */
        $discussionMemberSelf = $this->one($query);

        if ($discussionMemberSelf->role != MemberEnum::ADMIN && $discussionMemberSelf->role != MemberEnum::CREATOR) {
            throw new ForbiddenHttpException(Yii::t('mail/discussion', 'can_not_delete_this_member'));
        }
        /** @var DiscussionMemberEntity $memberEntity */
        $query = new Query();
        $query->andWhere(['discussion_id' => $memberEntity->discussion_id]);
        $countMembers = parent::count($query);
        if ($countMembers <= 3) {
            throw new BadRequestHttpException(Yii::t('mail/discussion', 'so_few_members'));
        }
        $this->isAdmin($memberEntity->discussion_id, 'can_not_delete_members');
        /** @var DiscussionMemberEntity $memberEntity */
        $memberEntity = $this->oneById($id);
        if ($memberEntity->role == MemberEnum::MEMBER) {
            parent::deleteById($id);
        } else {
            throw new ForbiddenHttpException(Yii::t('mail/discussion', 'can_not_delete_this_member'));
        }
    }

    private function isAdmin($discussionId, $errorMessage)
    {
        $addressEntity = \App::$domain->mail->address->myAddress();
        $query = new Query;
        $query->andWhere(
            [
                'discussion_id' => $discussionId,
                'email' => $addressEntity->email,
                'role' => [MemberEnum::CREATOR, MemberEnum::ADMIN]
            ]);
        try {
            \App::$domain->mail->discussionMember->one($query);
        } catch (NotFoundHttpException $e) {
            throw new ForbiddenHttpException(Yii::t('mail/discussion', $errorMessage));
        }
    }

    private function isCreator($discussionId, $errorMessage)
    {
        $addressEntity = \App::$domain->mail->address->myAddress();
        $query = new Query;
        $query->andWhere(
            [
                'discussion_id' => $discussionId,
                'email' => $addressEntity->email,
                'role' => [MemberEnum::CREATOR]
            ]);
        try {
            \App::$domain->mail->discussionMember->one($query);
        } catch (NotFoundHttpException $e) {
            throw new ForbiddenHttpException(Yii::t('mail/discussion', $errorMessage));
        }
    }

    private function isCreatorOrAdmin($discussionId, $errorMessage) {
        $addressEntity = \App::$domain->mail->address->myAddress();
        $query = new Query;
        $query->andWhere(
            [
                'discussion_id' => $discussionId,
                'email' => $addressEntity->email,
                'role' => [MemberEnum::CREATOR, MemberEnum::ADMIN],
            ]);
        try {
            \App::$domain->mail->discussionMember->one($query);
        } catch (NotFoundHttpException $e) {
            throw new ForbiddenHttpException(Yii::t('mail/discussion', $errorMessage));
        }
    }

}
