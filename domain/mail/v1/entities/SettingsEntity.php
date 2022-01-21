<?php

namespace domain\mail\v1\entities;

use yii2rails\domain\BaseEntity;

/**
 * Class SettingsEntity
 *
 * @package domain\mail\v1\entities
 *
 * @property $person_id
 * @property $is_enable_message_notify
 * @property $is_enable_mail_notify
 * @property $sign_text
 * @property $redirect_emails
 * @property $auto_answer_text
 * @property $is_enable_sign
 * @property $is_enable_redirect
 * @property $is_enable_auto_answer
 * @property $language
 */
class SettingsEntity extends BaseEntity
{

    protected $person_id;
    protected $is_enable_message_notify = 1;
    protected $is_enable_mail_notify = 1;
    protected $sign_text;
    protected $redirect_emails;
    protected $auto_answer_text;
    protected $is_enable_sign = 0;
    protected $is_enable_redirect = 0;
    protected $is_enable_auto_answer = 0;
    protected $language = 'ru';

    public function fieldType()
    {
        return [
            'person_id' => 'integer',
            'is_enable_message_notify' => 'boolean',
            'is_enable_mail_notify' => 'boolean',
            'is_enable_sign' => 'boolean',
            'is_enable_auto_answer' => 'boolean',
            'is_enable_redirect' => 'boolean',
        ];
    }

    public function rules()
    {
        return [
            [['person_id', 'is_enable_message_notify', 'is_enable_mail_notify', 'is_enable_sign'], 'required'],
            [['person_id'], 'integer'],
            [['is_enable_message_notify', 'is_enable_mail_notify', 'is_enable_sign'], 'boolean'],
            [['sign_text'], 'string', 'length' => [0, 1024]],
            [['sign_text', 'language',], 'filter','filter'=>'\yii\helpers\HtmlPurifier::process'],
        ];
    }

    public function isEnabledSign() {
        return ($this->is_enable_sign) ? '<br><br>' . $this->sign_text : null;
    }
}
