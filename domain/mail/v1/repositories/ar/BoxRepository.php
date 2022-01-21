<?php

namespace domain\mail\v1\repositories\ar;

use domain\mail\v1\interfaces\repositories\BoxInterface;
use yii2lab\db\domain\helpers\TableHelper;
use yii2rails\extension\activeRecord\repositories\base\BaseActiveArRepository;

/**
 * Class BoxRepository
 *
 * @package domain\mail\v1\repositories\ar
 *
 * @property-read \domain\mail\v1\Domain $domain
 */
class BoxRepository extends BaseActiveArRepository implements BoxInterface
{

    protected $schemaClass = true;

    public function tableName()
    {
        return 'mail_box';
    }

}
