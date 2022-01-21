<?php

namespace domain\mail\v1\repositories\schema;

use yii2rails\domain\enums\RelationEnum;
use yii2rails\domain\repositories\relations\BaseSchema;

/**
 * Class BoxSchema
 *
 * @package domain\mail\v1\repositories\schema
 *
 */
class BoxSchema extends BaseSchema
{

    public function relations()
    {
        return [
            'domain' => [
                'type' => RelationEnum::ONE,
                'field' => 'domain_id',
                'foreign' => [
                    'id' => 'mail.companyDomain',
                    'field' => 'id',
                ],
            ],
            'person' => [
                'type' => RelationEnum::ONE,
                'field' => 'person_id',
                'foreign' => [
                    'id' => 'user.person',
                    'field' => 'id',
                ],
            ],
            'dialog' => [
                'type' => RelationEnum::MANY,
                'field' => 'email',
                'foreign' => [
                    'id' => 'mail.dialog',
                    'field' => 'contractor',
                ],
            ],
        ];
    }

}
