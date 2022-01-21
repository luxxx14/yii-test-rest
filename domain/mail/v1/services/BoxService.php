<?php

namespace domain\mail\v1\services;

use domain\mail\v1\entities\BoxEntity;
use domain\mail\v1\interfaces\services\BoxInterface;
use yii\web\NotFoundHttpException;
use yii2rails\app\domain\helpers\EnvService;
use yii2rails\domain\data\Query;
use yii2rails\domain\services\base\BaseActiveService;
use yubundle\user\domain\v1\entities\PersonEntity;

/**
 * Class BoxService
 *
 * @package domain\mail\v1\services
 *
 * @property-read \domain\mail\v1\Domain $domain
 * @property-read \domain\mail\v1\interfaces\repositories\BoxInterface $repository
 */
class BoxService extends BaseActiveService implements BoxInterface
{

    public function oneByPersonId($personId, Query $query = null) /*: BoxEntity*/
    {
        $query = Query::forge($query);
        $query->where(['person_id' => $personId]);
        try {
            return $this->one($query);
        } catch (NotFoundHttpException $e) {
            if($personId == null) {
                throw new NotFoundHttpException;
            }
            $this->forgeBox($personId);
            return $this->one($query);
        }
    }

    public function oneByPerson(PersonEntity $personEntity, Query $query = null) /*: BoxEntity*/
    {
        $query = Query::forge($query);
        $query->where(['person_id' => $personEntity->id]);
        try {
            return $this->one($query);
        } catch (NotFoundHttpException $e) {
            if ($personEntity->id == null) {
                throw new NotFoundHttpException;
            }
            $this->forgeBox($personEntity->id);
            return $this->one($query);
        }
    }

    public function forgeBox(int $personId)
    {
        $loginEntity = \App::$domain->account->login->oneByPersonId($personId);
        $domainEntity = \App::$domain->mail->companyDomain->oneByCompanyId($loginEntity->company_id);
        $boxEntity = new BoxEntity;
        $boxEntity->domain_id = $domainEntity->id;
        $boxEntity->person_id = $personId;
        $defaultSize = EnvService::get('mail.box.defaultSize', 104857600);
        $boxEntity->quote_size = $defaultSize;
        $boxEntity->email = $loginEntity->login . '@' . $domainEntity->domain;
        $this->repository->insert($boxEntity);
    }

    public function oneByEmail(string $email, Query $query = null)
    {
        try {
            $query = Query::forge($query);
            $query->andWhere(['email' => $email]);
            $boxEntity = $this->one($query);
        } catch (NotFoundHttpException $e) {
            $boxEntity = $this->oneBoxByEmailAlt($email);
        }
        return $boxEntity;
    }

    protected function oneBoxByEmailAlt(string $email): BoxEntity
    {
        $emailEntity = \App::$domain->mail->address->oneByEmail($email);
        $domainEntity = \App::$domain->mail->companyDomain->oneByDomainName($emailEntity->domain);
        $identityEntity = \App::$domain->account->login->oneByLogin($emailEntity->login);

        $boxQuery = new Query;
        $boxQuery->andWhere([
            'domain_id' => $domainEntity->id,
            'person_id' => $identityEntity->person_id,
        ]);

        /** @var BoxEntity $boxEntity */
        $boxEntity = \App::$domain->mail->box->one($boxQuery);
        return $boxEntity;
    }

}
