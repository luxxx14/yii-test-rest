<?php

namespace domain\mail\v1\repositories\schema;

use yii2rails\domain\enums\RelationEnum;
use yii2rails\domain\repositories\relations\BaseSchema;

/**
 * Class DiscussionSchema
 *
 * @package domain\mail\v1\repositories\schema
 *
 */
class DiscussionSchema extends BaseSchema
{

    public function relations()
    {
        return [
            'members' => [
                'type' => RelationEnum::MANY,
                'field' => 'id',
                'foreign' => [
                    'id' => 'mail.discussionMember',
                    'field' => 'discussion_id',
                ],
            ],
        ];
    }


}
