<?php

namespace domain\mail\v1\entities;

use yii2rails\domain\BaseEntity;
use yii2rails\domain\behaviors\entity\TimeValueFilter;
use yii2rails\domain\values\TimeValue;
use yii2rails\extension\common\enums\StatusEnum;
use yubundle\user\domain\v1\entities\PersonEntity;

/**
 * Class FolderEntity
 *
 * @package domain\mail\v1\entities
 *
 * @property $id
 * @property $person_id
 * @property $name
 * @property $sort
 * @property $created_at
 * @property $updated_at
 * @property $status
 */
class FolderEntity extends BaseEntity
{

    protected $id;
    protected $person_id;
    protected $name;
    protected $sort;
    protected $created_at;
    protected $updated_at;
    protected $status = StatusEnum::ENABLE;
    protected $mails;

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
            'name' => 'string',
            'sort' => 'integer',
            'created_at' => TimeValue::class,
            'updated_at' => TimeValue::class,
            'status' => 'integer',
            'person' => PersonEntity::class,
        ];
    }

    public function rules()
    {
        return [
            [['name'], 'trim'],
            ['status', 'in', 'range' => StatusEnum::values()],
        ];
    }

}
