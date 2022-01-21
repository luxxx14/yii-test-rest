<?php


namespace domain\mail\v1\strategies\mail\receive;

use domain\mail\v1\entities\MailEntity;

interface ReceiveMailStrategy
{
    public function receive($mailEntity) : MailEntity;

}