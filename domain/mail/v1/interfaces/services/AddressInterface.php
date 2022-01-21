<?php

namespace domain\mail\v1\interfaces\services;

use domain\mail\v1\entities\AddressEntity;

/**
 * Interface AddressInterface
 *
 * @package domain\mail\v1\interfaces\services
 *
 * @property-read \domain\mail\v1\Domain $domain
 * @property-read \domain\mail\v1\interfaces\repositories\AddressInterface $repository
 */
interface AddressInterface
{

    public function myAddress(): AddressEntity;

    public function oneByEmail(string $email): AddressEntity;

    public function parseEmail(string $email): AddressEntity;

    public function isInternal(string $email): bool;

    public function isInternalList(array $emails): bool;

    public function isExternalList(array $emails): array;

    public function personIdByEmail(string $email);

    public function personIdsByEmails(string $emails);

}
