<?php

namespace domain\contact\v1\repositories\ar;

use domain\contact\v1\interfaces\repositories\PersonalInterface;
use yii2rails\domain\behaviors\query\SearchFilter;
use yii2rails\extension\activeRecord\repositories\base\BaseActiveArRepository;

/**
 * Class PersonalRepository
 * 
 * @package domain\contact\v1\repositories\ar
 * 
 * @property-read \domain\contact\v1\Domain $domain
 */
class PersonalRepository extends BaseActiveArRepository implements PersonalInterface {

	protected $schemaClass = true;

    public function tableName()
    {
        return 'contact_personal';
    }

    public function behaviors()
    {
        return [
            [
                'class' => SearchFilter::class,
                'fields' => [
                    'name',
                    'first_name',
                    'last_name',
                    'middle_name',
                    'phone',
                    'email',
                    'text',
                ],
                'virtualFields' => [
                    'name' => [
                        'first_name',
                        'last_name',
                        'middle_name',
                    ],
                    'text' => [
                        'first_name',
                        'last_name',
                        'middle_name',
                        'phone',
                        'email',
                    ]
                ],
            ],
        ];
    }

}
