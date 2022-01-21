<?php

namespace api\v1\modules\mail\controllers;

use yii2lab\rest\domain\rest\ActiveControllerWithQuery as Controller;
use yii2rails\extension\web\helpers\Behavior;

class DiscussionController extends Controller
{
	public $service = 'mail.discussion';

    public $formClass = null;
    public $titleName = null;

    public function behaviors()
    {
        return [
            Behavior::cors(),
            Behavior::auth(),
        ];
    }

    public function actionMessageDelete($id) {
        \App::$domain->mail->discussion->deleteMessageById($id);
        \Yii::$app->response->setStatusCode(204);
    }

    public function actionTouch($id) {
        \App::$domain->mail->discussion->touch($id);
        \Yii::$app->response->setStatusCode(204);
    }

}