<?php

namespace domain\mail\v1\behaviors;

use yii\base\ActionEvent;
use yii\base\Behavior;
use yii\web\ForbiddenHttpException;
use yii2rails\domain\services\base\BaseActiveService;

class ModifyMessageFilter extends Behavior
{

    public $actions = [];

    public function events()
    {
        return [
            BaseActiveService::EVENT_DELETE => 'prepareQueryEvent',
            BaseActiveService::EVENT_CREATE => 'prepareQueryEvent',
        ];
    }

    public function prepareQueryEvent(ActionEvent $event)
    {
        if (in_array($event->action, $this->actions)) {
            throw new ForbiddenHttpException(\Yii::t('mail/mail', 'email_messages_can_not_be_modify'));
        }
    }

}
