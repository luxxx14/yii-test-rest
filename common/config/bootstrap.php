<?php

use domain\mail\v1\behaviors\EnsureMailBoxBehavior;
use yii\base\Event;
use yubundle\account\domain\v2\enums\AccountEventEnum;
use yubundle\account\domain\v2\events\AccountAuthenticationEvent;

include(__DIR__ . '/../../vendor/yubundle/yii2-common/src/project/common/config/bootstrap.php');

/*Event::on(\yubundle\account\domain\v2\services\AuthService::class, AccountEventEnum::AUTHENTICATION, function (AccountAuthenticationEvent $event) {
    // @var EnsureMailBoxBehavior $handler
    $handler = Yii::createObject(EnsureMailBoxBehavior::class);
    $handler->ensureBox($event);
});*/
