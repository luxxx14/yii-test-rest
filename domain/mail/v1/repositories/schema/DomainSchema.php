<?php

namespace domain\mail\v1\repositories\schema;

use yii2rails\domain\enums\RelationEnum;
use yii2rails\domain\repositories\relations\BaseSchema;

/**
 * Class DomainSchema
 *
 * @package domain\mail\v1\repositories\schema
 *
 */
class DomainSchema extends BaseSchema
{

    public function relations()
    {
        return [
            'company' => [
                'type' => RelationEnum::ONE,
                'field' => 'company_id',
                'foreign' => [
                    'id' => 'staff.company',
                    'field' => 'id',
                ],
            ],
        ];
    }

}
