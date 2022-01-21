<?php

namespace domain\mail\v1\repositories\ar;

use domain\mail\v1\interfaces\repositories\DiscussionInterface;
use yii2rails\domain\behaviors\query\SearchFilter;
use yii2rails\extension\activeRecord\repositories\base\BaseActiveArRepository;

/**
 * Class DiscussionRepository
 *
 * @package domain\mail\v1\repositories\ar
 *
 * @property-read \domain\mail\v1\Domain $domain
 */
class DiscussionRepository extends BaseActiveArRepository implements DiscussionInterface
{

    protected $schemaClass = true;

    public function tableName()
    {
        return 'mail_discussion';
    }

    public function behaviors()
    {
        return [
            [
                'class' => SearchFilter::class,
                'fields' => [
                    'subject',
                ],
            ],
        ];
    }

}
