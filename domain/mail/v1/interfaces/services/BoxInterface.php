<?php

namespace domain\mail\v1\interfaces\services;

use yii2rails\domain\data\Query;
use yii2rails\domain\interfaces\services\CrudInterface;

/**
 * Interface BoxInterface
 *
 * @package domain\mail\v1\interfaces\services
 *
 * @property-read \domain\mail\v1\Domain $domain
 * @property-read \domain\mail\v1\interfaces\repositories\BoxInterface $repository
 */
interface BoxInterface extends CrudInterface
{

    public function oneByEmail(string $email, Query $query = null);
    public function oneByPersonId($personId, Query $query = null);

}
