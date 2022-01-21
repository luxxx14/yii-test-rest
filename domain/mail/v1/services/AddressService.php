<?php

namespace domain\mail\v1\services;

use App;
use domain\mail\v1\entities\AddressEntity;
use domain\mail\v1\entities\DomainEntity;
use domain\mail\v1\helpers\MailHelper;
use domain\mail\v1\interfaces\services\AddressInterface;
use yii\web\NotFoundHttpException;
use yii2rails\domain\exceptions\UnprocessableEntityHttpException;
use yii2mod\helpers\ArrayHelper;
use yii2rails\domain\data\Query;
use yii2rails\domain\helpers\ErrorCollection;
use yii2rails\domain\services\base\BaseService;

/**
 * Class AddressService
 *
 * @package domain\mail\v1\services
 *
 * @property-read \domain\mail\v1\Domain $domain
 * @property-read \domain\mail\v1\interfaces\repositories\AddressInterface $repository
 */
class AddressService extends BaseService implements AddressInterface
{

    public function myAddress(): AddressEntity
    {
        $identity = \App::$domain->account->auth->identity;

        $domainQuery = new Query;
        $domainQuery->andWhere(['company_id' => $identity->company_id]);
        /** @var DomainEntity $domainEntity */
        $domainEntity = \App::$domain->mail->companyDomain->one($domainQuery);

        $addressEntity = new AddressEntity;
        $addressEntity->domain = $domainEntity->domain;
        $addressEntity->login = $identity->login;

        return $addressEntity;
    }

    public function oneByEmail(string $email): AddressEntity
    {
        list($login, $domain) = explode('@', $email);
        $addressEntity = new AddressEntity;
        $addressEntity->domain = $domain;
        $addressEntity->login = $login;
        return $addressEntity;
    }

    public function parseEmail(string $email): AddressEntity
    {
        list($login, $domain) = explode('@', $email);
        $addressEntity = new AddressEntity;
        $addressEntity->domain = $domain;
        $addressEntity->login = $login;
        return $addressEntity;
    }

    public function isInternal(string $email): bool
    {
        try {
            return !is_null(App::$domain->mail->box->oneByEmail($email));
        } catch (NotFoundHttpException $e) {
            $login = explode('@', $email);
            $domain = $login[1];
            $login = $login[0];
            try {
                \App::$domain->mail->companyDomain->oneByDomainName($domain);
            } catch (NotFoundHttpException $e) {
                return false;
            }
            try {
                $loginEntity = \App::$domain->account->login->oneByLogin($login);
                \App::$domain->mail->box->forgeBox($loginEntity->person_id);
                return true;
            } catch (NotFoundHttpException $e) {
                return false;
            }
        }
    }

    public function isExternal(string $email): bool
    {
        try {
            $query = new Query();
            $query->andWhere(['email' => $email]);
            App::$domain->mail->box->one($query);
            return false;
        } catch (NotFoundHttpException $e) {
            return true;
        }
    }

    public function isInternalList(array $emails): bool
    {
        foreach ($emails as $email) {
            if (!$this->isInternal($email)) {
                return false;
            }
        }
        return true;
    }

    public function isExternalList(array $emails): array
    {
        $addressEntity = \App::$domain->mail->address->myAddress();
        $ourDomain = $addressEntity->domain;
        foreach ($emails as $email) {
            list($login, $domain) = explode('@', $email);
            if ($domain != $ourDomain || !$this->isExternal($email)) {
                $emails = array_diff($emails, [$email]);
            }
        }
        return array_unique($emails);
    }

    public function personIdByEmail(string $email)
    {
        $personIdList = $this->personIdsByEmails($email);
        $personId = ArrayHelper::first($personIdList);
        return $personId;
    }

    public function personIdsByEmails(string $emails)
    {
        $emails = ArrayHelper::toArray($emails);
        $personIdList = $this->getPersonIdListByContractorEmailList($emails);
        return $personIdList;
    }

    private function getPersonIdListByContractorEmailList($contractorEmailList)
    {
        if (empty($contractorEmailList)) {
            return [];
        }
        $loginList = [];
        foreach ($contractorEmailList as $contractorEmail) {
            $boxEntity = App::$domain->mail->box->oneByEmail($contractorEmail);
            $loginList[] = $boxEntity->person_id;
        }
        return $loginList;
    }

}
