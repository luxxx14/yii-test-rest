<?php

namespace domain\mail\v1\entities;

use yii2rails\domain\BaseEntity;

/**
 * Class AddressEntity
 *
 * @package domain\mail\v1\entities
 *
 * @property $login
 * @property $domain
 * @property-read $email
 */
class AddressEntity extends BaseEntity
{

    protected $login;
    protected $domain;

    public function getEmail()
    {
        return $this->login . '@' . $this->domain;
    }

}
