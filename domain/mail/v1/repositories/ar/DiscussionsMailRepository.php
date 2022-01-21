<?php

namespace domain\mail\v1\repositories\ar;

use domain\mail\v1\interfaces\repositories\DiscussionsMailInterface;
use yii2lab\db\domain\helpers\TableHelper;
use yii2rails\extension\activeRecord\repositories\base\BaseActiveArRepository;

/**
 * Class DiscussionsMailRepository
 *
 * @package domain\mail\v1\repositories\ar
 *
 * @property-read \domain\mail\v1\Domain $domain
 */
class DiscussionsMailRepository extends BaseActiveArRepository implements DiscussionsMailInterface
{

    protected $schemaClass = true;

    public function tableName()
    {
        return 'mail_discussion_mail';
    }

}
