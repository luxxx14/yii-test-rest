<?php


namespace domain\mail\v1\strategies\mail\send;


use domain\mail\v1\entities\MailEntity;

interface SendOuterMailStrategy
{
    public function send(MailEntity $mailEntity);

}