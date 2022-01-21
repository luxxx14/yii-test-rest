<?php

namespace domain\mail\v1\repositories\schema;

use yii2rails\domain\enums\RelationEnum;
use yii2rails\domain\repositories\relations\BaseSchema;

/**
 * Class FolderSchema
 *
 * @package domain\mail\v1\repositories\schema
 *
 */
class FolderSchema extends BaseSchema
{

    public function relations()
    {
        return [
            'person' => [
                'type' => RelationEnum::ONE,
                'field' => 'person_id',
                'foreign' => [
                    'id' => 'user.person',
                    'field' => 'id',
                ],
            ],
            'mails' => [
                'type' => RelationEnum::MANY,
                'field' => 'id',
                'foreign' => [
                    'id' => 'mail.flow',
                    'field' => 'folder_id',
                ],
            ],
        ];
    }

}
