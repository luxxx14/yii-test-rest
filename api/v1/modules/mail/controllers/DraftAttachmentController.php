<?php

namespace api\v1\modules\mail\controllers;

use domain\mail\v1\forms\UploadForm;
use Yii;
use yii2lab\rest\domain\rest\ActiveControllerWithQuery as Controller;
use yii2rails\domain\helpers\Helper;
use yii2rails\extension\web\enums\HttpHeaderEnum;
use yii2rails\extension\web\helpers\Behavior;

class DraftAttachmentController extends Controller
{

    public $service = 'mail.attachment';

    public function behaviors()
    {
        return [
            Behavior::cors(),
            Behavior::auth(),
        ];
    }

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['create']);
        return $actions;
    }

    public function actionCreate()
    {
        $model = new UploadForm;
        Helper::forgeForm($model);
        $attachmentEntity = \App::$domain->mail->attachment->upload($model);
        Yii::$app->response->setStatusCode(201);
        Yii::$app->response->headers->add(HttpHeaderEnum::X_ENTITY_ID, $attachmentEntity->id);
    }

}