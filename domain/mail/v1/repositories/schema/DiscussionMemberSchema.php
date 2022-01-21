<?php

namespace domain\mail\v1\repositories\schema;

use yii2rails\domain\enums\RelationEnum;
use yii2rails\domain\repositories\relations\BaseSchema;

/**
 * Class DiscussionMemberSchema
 *
 * @package domain\mail\v1\repositories\schema
 *
 */
class DiscussionMemberSchema extends BaseSchema
{

    public function relations()
    {
        return [
            'discussion' => [
                'type' => RelationEnum::MANY,
                'field' => 'discussion_id',
                'foreign' => [
                    'id' => 'mail.discussion',
                    'field' => 'id',
                ],
            ]
        ];
    }

}
