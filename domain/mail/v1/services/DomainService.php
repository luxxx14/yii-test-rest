<?php

namespace domain\mail\v1\services;

use App;
use domain\mail\v1\interfaces\services\DomainInterface;
use yii2rails\domain\data\Query;
use yii2rails\domain\services\base\BaseActiveService;

/**
 * Class DomainService
 *
 * @package domain\mail\v1\services
 *
 * @property-read \domain\mail\v1\Domain $domain
 * @property-read \domain\mail\v1\interfaces\repositories\DomainInterface $repository
 */
class DomainService extends BaseActiveService implements DomainInterface
{

    public function oneByCompanyId(int $companyId, Query $query = null)
    {
        $query = Query::forge($query);
        $query->andWhere(['company_id' => $companyId]);
        return $this->one($query);
    }

    public function allByCompanyId(int $companyId, Query $query = null)
    {
        $query = Query::forge($query);
        $query->andWhere(['company_id' => $companyId]);
        return $this->all($query);
    }

    public function oneByDomainName(string $domain, Query $query = null)
    {
        $query = Query::forge($query);
        $query->andWhere(['domain' => $domain]);
        return $this->one($query);
    }

    public function create($data)
    {
        $personEntity = App::$domain->user->person->oneSelf();
        $data['company_id'] = $personEntity->user->company_id;
        return parent::create($data);
    }

}
