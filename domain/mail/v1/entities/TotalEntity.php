<?php

namespace domain\mail\v1\entities;

use yii2rails\domain\BaseEntity;

/**
 * Class TotalCountEntity
 *
 * @package domain\mail\v1\entities
 *
 * @property $person_id
 */
class TotalEntity extends BaseEntity
{

    protected $type;
    protected $total;

    public function fieldType()
    {
        return [
            'type' => 'string',
            'total' => 'integer',
        ];
    }
}
