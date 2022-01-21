<?php

namespace domain\mail\v1\entities;

use yubundle\staff\domain\v1\entities\CompanyEntity;
use yii2rails\domain\BaseEntity;
use yii2rails\domain\behaviors\entity\TimeValueFilter;
use yii2rails\domain\values\TimeValue;
use yii2rails\extension\common\enums\StatusEnum;

/**
 * Class DomainEntity
 *
 * @package domain\mail\v1\entities
 *
 * @property $id
 * @property $company_id
 * @property $domain
 * @property $host
 * @property $port
 * @property $status
 * @property $created_at
 * @property $updated_at
 * @property CompanyEntity $company
 */
class DomainEntity extends BaseEntity
{

    protected $id;
    protected $company_id;
    protected $domain;
    protected $host;
    protected $port;
    protected $status = StatusEnum::ENABLE;
    protected $created_at;
    protected $updated_at;
    protected $company;

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
            'company_id' => 'integer',
            'port' => 'integer',

            'status' => 'integer',
            'created_at' => TimeValue::class,
            'updated_at' => TimeValue::class,
        ];
    }

    public function rules()
    {
        return [
            ['status', 'in', 'range' => StatusEnum::values()],
            [['domain', 'host'], 'filter','filter'=>'\yii\helpers\HtmlPurifier::process'],
        ];
    }
}
