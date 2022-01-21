<?php

namespace domain\mail\v1\repositories\schema;

use yii2rails\domain\enums\RelationEnum;
use yii2rails\domain\repositories\relations\BaseSchema;

/**
 * Class MailSchema
 *
 * @package domain\mail\v1\repositories\schema
 *
 */
class MailSchema extends BaseSchema
{

    public function relations()
    {
        return [
            'attachments' => [
                'type' => RelationEnum::MANY,
                'field' => 'id',
                'foreign' => [
                    'id' => 'mail.attachment',
                    'field' => 'mail_id',
                ],
            ],
            'box' => [
                'type' => RelationEnum::ONE,
                'field' => 'from',
                'foreign' => [
                    'id' => 'mail.box',
                    'field' => 'email',
                ],
            ],
        ];
    }

}
