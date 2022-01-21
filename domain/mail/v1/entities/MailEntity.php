<?php

namespace domain\mail\v1\entities;

use domain\mail\v1\enums\MailKindEnum;
use domain\mail\v1\enums\MailTypeEnum;
use yii\helpers\HtmlPurifier;
use yii\helpers\StringHelper;
use yii2rails\domain\BaseEntity;
use yii2rails\domain\behaviors\entity\TimeValueFilter;
use yii2rails\domain\values\TimeValue;
use yii2rails\extension\common\enums\StatusEnum;

/**
 * Class MailEntity
 *
 * @package domain\mail\v1\entities
 *
 * @property $id
 * @property $ext_id
 * @property $kind
 * @property $type
 * @property $discussion_id
 * @property $reply_from
 * @property $from
 * @property $to
 * @property $copy_to
 * @property $blind_copy
 * @property $reply_to
 * @property $subject
 * @property $content
 * @property $is_draft
 * @property $status
 * @property $created_at
 * @property $updated_at
 * @property $fillable
 * @property AttachmentEntity[] $attachments
 * @property BoxEntity $box
 */
class MailEntity extends BaseEntity
{

    protected $id;
    protected $ext_id;
    protected $kind = MailKindEnum::INNER;
    protected $type = MailTypeEnum::MAIL;
    protected $discussion_id;
    protected $reply_from;
    protected $from;
    protected $to;
    protected $copy_to;
    protected $blind_copy;
    protected $reply_to;
    protected $subject;
    protected $content;
    protected $is_draft = true;
    protected $status = StatusEnum::ENABLE;
    protected $created_at;
    protected $updated_at;
    protected $attachments;
    protected $box;
    protected $content_text;

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
            'kind' => 'string',
            'reply_from' => 'string',
            'from' => 'string',
            //'to' => 'array',
            //'copy_to' => 'array',
            'subject' => 'string',
            'content' => 'string',
            'status' => 'integer',
            'is_draft' => 'boolean',
            'created_at' => TimeValue::class,
            'updated_at' => TimeValue::class,
            'attachments' => [
                'type' => AttachmentEntity::class,
                'isCollection' => true,
            ],
            'box' => BoxEntity::class,
        ];
    }

    public function rules()
    {
        return [
            [['kind', 'reply_from', 'from', 'subject', 'content',], 'trim'],
            ['subject', 'string', 'max' => 254],
            [['kind',], 'required'],
            [['reply_from', 'from'], 'email'],
            ['kind', 'in', 'range' => MailKindEnum::values()],
            ['status', 'in', 'range' => StatusEnum::values()],
            [['content', 'subject',], 'filter', 'filter'=> function($html) {
                return HtmlPurifier::process( $html, [
                    'URI.AllowedSchemes' => [ 'data' => true, 'src' => true,'http' => true, 'https' => true,],
                    'URI.DisableExternalResources' => 'false'
                ]);
            }],
        ];
    }

    public function getShortContent()
    {
        $content = $this->content;
        $matches = [];
        $signPattern = '#^(.*)(<br.{0,1}\/{0,1}>){2}(.*)$#';
        $checkSingn = preg_match($signPattern, $content, $matches);
        if ($checkSingn) {
            $content = $matches[1];
        }
        $content = strip_tags($content);
        $content = StringHelper::truncate($content, 70);
        return $content;
    }

    public function getContentText()
    {
        return strip_tags($this->content);
    }

    public function fields()
    {
        $fields = parent::fields();
        /**
         * TODO: Костыль
         */
        $addressEntity = \App::$domain->mail->address->myAddress();
        if ($this->from != $addressEntity->getEmail()
        && $addressEntity->getEmail() != 'mailsender@t-cloud.kz') {
            unset($fields['blind_copy']);
        }
        return $fields;
    }

}
