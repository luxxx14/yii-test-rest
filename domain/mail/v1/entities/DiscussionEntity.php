<?php

namespace domain\mail\v1\entities;

use yii\web\NotFoundHttpException;
use yii2rails\domain\BaseEntity;
use yii2rails\domain\behaviors\entity\TimeValueFilter;
use yii2rails\domain\data\Query;
use yii2rails\domain\enums\JoinEnum;
use yii2rails\domain\values\TimeValue;
use yii2rails\extension\common\enums\StatusEnum;

/**
 * Class DiscussionEntity
 *
 * @package domain\mail\v1\entities
 *
 * @property $id
 * @property $corporate_client_id
 * @property $subject
 * @property $description
 * @property $members
 * @property $mails
 * @property $status
 * @property $created_at
 * @property $updated_at
 */
class DiscussionEntity extends BaseEntity
{

    protected $id;
    protected $corporate_client_id;
    protected $subject;
    protected $description;
    protected $members;
    protected $mails;
    protected $status = StatusEnum::ENABLE;
    protected $created_at;
    protected $updated_at;
    protected $last_message_content = 'Последнее сообщение (мок)';
    protected $new_message_count = 0;

    public function behaviors()
    {
        return [
            [
                'class' => TimeValueFilter::class,
            ],
        ];
    }

    public function fieldType()
    {
        return [
            'id' => 'integer',
            'members' => [
                'type' => DiscussionMemberEntity::class,
                'isCollection' => true,
            ],
            /*'mails' => [
                'type' => MailEntity::class,
                'isCollection' => true,
            ],*/
            'status' => 'integer',
            'created_at' => TimeValue::class,
            'updated_at' => TimeValue::class,
        ];
    }

    public function getLastMessage()
    {
        $addressEntity = \App::$domain->mail->address->myAddress();
        $query = new Query();
        $query->andWhere(['discussion_id' => $this->id, 'status' => StatusEnum::ENABLE]);
        $query->orderBy(['created_at' => SORT_DESC]);
        $query->limit(1);
        try {
            /** @var MailEntity $mailEntity */
            $mailEntity = \App::$domain->mail->mail->one($query);
            $query = new Query();
            $query->andWhere(['mail_id' => $mailEntity->id, 'mail_address' => $addressEntity->getEmail(), 'status' => StatusEnum::ENABLE]);
            $flowEntity = \App::$domain->mail->flow->repository->one($query);
            return $mailEntity->getShortContent();
        } catch (NotFoundHttpException $e) {
            return null;
        }
    }

    public function getNewMessageCount()
    {
        $addressEntity = \App::$domain->mail->address->myAddress();
        $query = new Query();
        $query->andWhere([
            'discussion_id' => $this->id,
            'email' => $addressEntity->getEmail()
        ]);
        try {
            /** @var DiscussionMemberEntity $discussionMemberEntity */
            $discussionMemberEntity = \App::$domain->mail->discussionMember->one($query);
            return $discussionMemberEntity->new_message_count;
        } catch (NotFoundHttpException $e) {
            return 0;
        }
    }

    public function rules()
    {
        return [
            ['status', 'in', 'range' => StatusEnum::values()],
            [['description', 'subject'], 'filter', 'filter' => '\yii\helpers\HtmlPurifier::process'],
        ];
    }

    public function fields()
    {
        $fields = parent::fields();
        $fields['last_message_content'] = 'last_message';
        $fields['new_message_count'] = 'new_message_count';
        return $fields;
    }

}
