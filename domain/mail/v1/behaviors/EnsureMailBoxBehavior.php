<?php

namespace domain\mail\v1\behaviors;

use yii\base\Behavior;
use yii\web\NotFoundHttpException;
use yubundle\account\domain\v2\enums\AccountEventEnum;
use yubundle\account\domain\v2\events\AccountAuthenticationEvent;
use yubundle\account\domain\v2\helpers\LoginTypeHelper;

class EnsureMailBoxBehavior extends Behavior
{

    public function events()
    {
        return [
            AccountEventEnum::AUTHENTICATION => 'ensureBox',
        ];
    }

    public function ensureBox(AccountAuthenticationEvent $event)
    {
        //$identity = $event->identity;
        /*$isEmail = LoginTypeHelper::isEmail($event->login);
        if($isEmail) {
            try {
                //\App::$domain->mail->box->oneByEmail($event->login);
                d('ok');
            } catch (NotFoundHttpException $e) {
                $emailEntity = \App::$domain->mail->address->oneByEmail($event->login);
                d($emailEntity);
            }
        }*/
    }

}