<?php

namespace domain\mail\v1\repositories\schema;

use yii2rails\domain\enums\RelationEnum;
use yii2rails\domain\repositories\relations\BaseSchema;

/**
 * Class DialogSchema
 *
 * @package domain\mail\v1\repositories\schema
 *
 */
class DialogSchema extends BaseSchema
{

    public function relations()
    {
        return [
            'box' => [
                'type' => RelationEnum::ONE,
                'field' => 'contractor',
                'foreign' => [
                    'id' => 'mail.box',
                    'field' => 'email',
                ],
            ],
        ];
    }

}
