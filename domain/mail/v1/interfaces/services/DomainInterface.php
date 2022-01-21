<?php

namespace domain\mail\v1\interfaces\services;

use yii2rails\domain\data\Query;
use yii2rails\domain\interfaces\services\CrudInterface;

/**
 * Interface DomainInterface
 *
 * @package domain\mail\v1\interfaces\services
 *
 * @property-read \domain\mail\v1\Domain $domain
 * @property-read \domain\mail\v1\interfaces\repositories\DomainInterface $repository
 */
interface DomainInterface extends CrudInterface
{

    public function oneByCompanyId(int $companyId, Query $query = null);

    public function allByCompanyId(int $companyId, Query $query = null);

    public function oneByDomainName(string $domain, Query $query = null);

}
