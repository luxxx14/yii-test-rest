<?php

namespace domain\mail\v1\interfaces\services;

use domain\mail\v1\entities\FlowEntity;
use domain\mail\v1\entities\MailEntity;
use yii2rails\domain\interfaces\services\CrudInterface;

/**
 * Interface FlowInterface
 *
 * @package domain\mail\v1\interfaces\services
 *
 * @property-read \domain\mail\v1\Domain $domain
 * @property-read \domain\mail\v1\interfaces\repositories\FlowInterface $repository
 */
interface FlowInterface extends CrudInterface
{

    public function send(FlowEntity $flowEntity);

    //public function createFlowList(array $emails, int $mailId);

    public function createFlowByMailEntity(MailEntity $mailEntity);

}
