<?php

namespace domain\mail\v1\interfaces\services;

use domain\mail\v1\entities\MailEntity;
use yii2rails\domain\exceptions\UnprocessableEntityHttpException;
use yii2rails\domain\interfaces\services\CrudInterface;

/**
 * Interface MailInterface
 *
 * @package domain\mail\v1\interfaces\services
 *
 * @property-read \domain\mail\v1\Domain $domain
 * @property-read \domain\mail\v1\interfaces\repositories\MailInterface $repository
 */
interface MailInterface extends CrudInterface
{

    /**
     * @param MailEntity $mailEntity
     * @return MailEntity
     * @throws UnprocessableEntityHttpException
     */
    public function send(MailEntity $mailEntity);

    /**
     * @param MailEntity $mailEntity
     * @return MailEntity
     * @throws UnprocessableEntityHttpException
     */
    public function sendDraft(MailEntity $mailEntity);

    /**
     * @return float
     * @throws UnprocessableEntityHttpException
     */
    public function checkFreeSpaceInBox();

}
