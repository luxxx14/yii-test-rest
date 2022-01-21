<?php

namespace domain\mail\v1\entities;

use yii2rails\domain\BaseEntity;
use yii2rails\domain\behaviors\entity\TimeValueFilter;
use yii2rails\domain\values\TimeValue;
use yii2rails\extension\common\enums\StatusEnum;
use yubundle\user\domain\v1\entities\PersonEntity;

/**
 * Class BoxEntity
 *
 * @package domain\mail\v1\entities
 *
 * @property $id
 * @property $domain_id
 * @property $person_id
 * @property $email
 * @property $quote_size
 * @property $status
 * @property $created_at
 * @property $updated_at
 * @property DomainEntity $domain
 * @property PersonEntity $person
 */
class BoxEntity extends BaseEntity
{

    protected $id;
    protected $domain_id;
    protected $person_id;
    protected $email;
    protected $quote_size = 104857600;
    protected $status = StatusEnum::ENABLE;
    protected $created_at;
    protected $updated_at;
    protected $domain;
    protected $person;

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
