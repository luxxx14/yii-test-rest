<?php

namespace domain\mail\v1\repositories\ar;

use domain\mail\v1\interfaces\repositories\DomainInterface;
use yii2lab\db\domain\helpers\TableHelper;
use yii2rails\extension\activeRecord\repositories\base\BaseActiveArRepository;

/**
 * Class DomainRepository
 *
 * @package domain\mail\v1\repositories\ar
 *
 * @property-read \domain\mail\v1\Domain $domain
 */
class DomainRepository extends BaseActiveArRepository implements DomainInterface
{

    protected $schemaClass = true;

    public function tableName()
    {
        return 'mail_domain';
    }

}
