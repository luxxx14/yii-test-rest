<?php

namespace domain\mail\v1\entities;

use domain\mail\v1\enums\FlowKindEnum;
use domain\mail\v1\enums\FolderEnum;
use domain\mail\v1\enums\MailTypeEnum;
use yii\helpers\StringHelper;
use yii2rails\domain\BaseEntity;
use yii2rails\domain\behaviors\entity\TimeValueFilter;
use yii2rails\domain\values\TimeValue;
use yii2rails\extension\common\enums\StatusEnum;

/**
 * Class FlowEntity
 *
 * @package domain\mail\v1\entities
 *
 * @property $id
 * @property $direct
 * @property $type
 * @property $mail_address
 * @property $mail_id
 * @property $read_at
 * @property $created_at
 * @property $updated_at
 * @property $status
 * @property $seen
 * @property $flagged
 * @property $folder
 *
 * @property $subject
 * @property $content
 * @property MailEntity $mail
 * @property AttachmentEntity[] $attachments
 * @property boolean $has_attachment
 * @property $full_name
 * @property $dialog_id
 * @property $discussion_id
 */
class FlowEntity extends BaseEntity
{

    protected $id;
    protected $direct;
    protected $type;
    protected $mail_address;
    protected $mail_id;
    protected $read_at;
    protected $created_at;
    protected $updated_at;
    protected $status = StatusEnum::ENABLE;
    protected $seen = false;
    protected $flagged;
    protected $folder;

    protected $from;
    protected $to;
    protected $subject;
    protected $short_content;
    protected $content;
    protected $mail;
    protected $attachments;
    protected $has_attachment = false;
    protected $full_name = null;
    protected $content_text;
    protected $dialog_id;
    protected $discussion_id;
    protected $copy_to;
    protected $blind_copy;

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
            'direct' => 'string',
            'mail_address' => 'string',
            'mail_id' => 'integer',
            'flags' => 'array',
            'read_at' => TimeValue::class,
            'created_at' => TimeValue::class,
            'updated_at' => TimeValue::class,
            'status' => 'integer',
            'seen' => 'boolean',
            'flagged' => 'boolean',
            'folder' => 'string',

            'subject' => 'string',
            'content' => 'string',
            'mail' => MailEntity::class,
            'attachments' => [
                'type' => AttachmentEntity::class,
                'isCollection' => true,
            ],
            'full_name' => 'string',
        ];
    }

    public function rules()
    {
        return [
            [['mail_address', 'direct'], 'trim'],
            ['direct', 'in', 'range' => FlowKindEnum::values()],
            [['mail_address'], 'email'],
            ['status', 'in', 'range' => StatusEnum::values()],
            ['folder', 'in', 'range' => FolderEnum::values()],
        ];
    }

    public function getCopyTo()
    {
        return $this->getMirrorAttribute('copy_to');
    }

    public function getDiscussionId()
    {
        return $this->getMirrorAttribute('discussion_id');
    }

    public function getType()
    {
        return $this->getMirrorAttribute('type');
    }

    public function getHasAttachments()
    {
        return $this->has_attachment;
    }

    public function getSubject()
    {
        return $this->getMirrorAttribute('subject');
    }

    public function getShortContent()
    {
        return $this->getMirrorAttribute('short_content');
    }

    public function getContent()
    {
        return $this->getMirrorAttribute('content');
    }

    public function getAttachments()
    {
        return $this->getMirrorAttribute('attachments');
    }

    public function getFrom()
    {
        return $this->getMirrorAttribute('from');
    }

    public function getTo()
    {
        return $this->getMirrorAttribute('to');
    }

    public function getContentText()
    {
        return $this->getMirrorAttribute('content_text');
    }

    public function getBlindCopy()
    {
        return $this->getMirrorAttribute('blind_copy');
    }

    public function getFullName()
    {
        if (isset($this->mail->box)){
            $boxEntity = $this->mail->box;
            $personEntity = $boxEntity->person;
            return $personEntity->full_name;
        } else {
            return $this->getFrom();
        }
    }


    private function getMirrorAttribute($name)
    {
        if (!empty($this->{$name})) {
            return $this->{$name};
        }
        if (!empty($this->mail)) {
            return $this->mail->{$name};
        }
        return null;
    }

    public function fields()
    {
        $fields = parent::fields();
        $fields['has_attachments'] = 'has_attachments';
        unset($fields['mail']);
        //unset($fields['mail_id']);
        return $fields;
    }

}
