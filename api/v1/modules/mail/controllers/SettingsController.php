<?php

namespace api\v1\modules\mail\controllers;

use domain\mail\v1\entities\SettingsEntity;
use yii2lab\rest\domain\rest\Controller;
use yii2rails\extension\web\helpers\Behavior;

class SettingsController extends Controller
{
	public $service = 'mail.dialog';

    public function behaviors()
    {
        return [
            Behavior::cors(),
            Behavior::auth(),
        ];
    }

    public function actionView()
    {
        return \App::$domain->mail->settings->oneSelf();
    }

    public function actionUpdate()
    {
        $post = \Yii::$app->request->post();
        $settingsEntity = new SettingsEntity;
        $settingsEntity->load($post);
        return \App::$domain->mail->settings->updateSelf($settingsEntity);
    }

}