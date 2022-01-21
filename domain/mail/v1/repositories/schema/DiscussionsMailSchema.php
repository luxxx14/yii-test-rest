<?php

namespace domain\mail\v1\repositories\schema;

use yii2rails\domain\enums\RelationEnum;
use yii2rails\domain\repositories\relations\BaseSchema;

/**
 * Class DiscussionsMailSchema
 *
 * @package domain\mail\v1\repositories\schema
 *
 */
class DiscussionsMailSchema extends BaseSchema
{

    public function relations()
    {
        return [
            'discussion' => [
                'type' => RelationEnum::ONE,
                'field' => 'discussion_id',
                'foreign' => [
                    'id' => 'mail.discussion',
                    'field' => 'id',
                ],
            ],
            'mail' => [
                'type' => RelationEnum::MANY,
                'field' => 'mail_id',
                'foreign' => [
                    'id' => 'mail.mail',
                    'field' => 'id',
                ],
            ],
        ];
    }


}
