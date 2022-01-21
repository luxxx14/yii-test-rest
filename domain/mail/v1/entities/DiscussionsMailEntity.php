<?php

namespace domain\mail\v1\entities;

use yii2rails\domain\BaseEntity;
use yii2rails\domain\behaviors\entity\TimeValueFilter;
use yii2rails\domain\values\TimeValue;
use yii2rails\extension\common\enums\StatusEnum;

/**
 * Class DiscussionsMailEntity
 *
 * @package domain\mail\v1\entities
 *
 * @property $id
 * @property $mail_id
 * @property $discussion_id
 * @property MailEntity $mail
 * @property $status
 * @property $created_at
 * @property $updated_at
 */
class DiscussionsMailEntity extends BaseEntity
{

    protected $id;
    protected $mail_id;
    protected $discussion_id;
    protected $mail;
    protected $status = StatusEnum::ENABLE;
    protected $created_at;
    protected $updated_at;

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

            'status' => 'integer',
            'created_at' => TimeValue::class,
            'updated_at' => TimeValue::class,
        ];
    }

    public function rules()
    {
        return [
            ['status', 'in', 'range' => StatusEnum::values()],
        ];
    }


}
