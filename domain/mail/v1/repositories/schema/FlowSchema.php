<?php

namespace domain\mail\v1\repositories\schema;

use yii2rails\domain\enums\RelationEnum;
use yii2rails\domain\repositories\relations\BaseSchema;

/**
 * Class FlowSchema
 *
 * @package domain\mail\v1\repositories\schema
 *
 */
class FlowSchema extends BaseSchema
{

    public function relations()
    {
        return [
            'mail' => [
                'type' => RelationEnum::ONE,
                'field' => 'mail_id',
                'foreign' => [
                    'id' => 'mail.mail',
                    'field' => 'id',
                ],
            ],
        ];
    }

}
