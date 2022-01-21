<?php

namespace api\v1\modules\mail\controllers;

use common\enums\rbac\PermissionEnum;
use domain\mail\v1\entities\MailEntity;
use domain\mail\v1\enums\MailKindEnum;
use domain\mail\v1\helpers\MailHelper;
use Yii;
use yii\helpers\ArrayHelper;
use yii2lab\rest\domain\rest\ActiveControllerWithQuery as Controller;
use yii2rails\domain\data\Query;
use yii2rails\extension\web\helpers\Behavior;

class DraftController extends Controller
{

    public $service = 'mail.mail';

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
        $actions['create']['serviceMethod'] = 'createDraft';
        $actions['update']['serviceMethod'] = 'updateDraftById';
        $actions['delete']['serviceMethod'] = 'deleteDraftById';
        $actions['index']['query'] = Query::forge()->andWhere(['is_draft' => 1]);
        $actions['view']['query'] = Query::forge()->andWhere(['is_draft' => 1]);
        return $actions;
    }

    public function actionSend($id)
    {
        $data = \Yii::$app->request->post();
        /** @var MailEntity $mailEntity */
        $mailEntity = \App::$domain->mail->mail->oneById($id);
        $to = MailHelper::removeSpaces(ArrayHelper::getValue($data, 'to', []));
        if (!empty($to)) {
            $mailEntity->to = explode(',', $to);
        } else {
            $mailEntity->to = [];
        }
        $copyTo = MailHelper::removeSpaces(ArrayHelper::getValue($data, 'copy_to', []));
        if (!empty($copyTo)) {
            $mailEntity->copy_to = explode(',', $copyTo);
        } else {
            $mailEntity->copy_to = [];
        }
        $blindCopy = MailHelper::removeSpaces(ArrayHelper::getValue($data, 'blind_copy', []));
        if (!empty($blindCopy)) {
            $mailEntity->blind_copy = explode(',', $blindCopy);
        } else {
            $mailEntity->blind_copy = [];
        }
        $mailEntity->subject = ArrayHelper::getValue($data, 'subject');
        $mailEntity->content = ArrayHelper::getValue($data, 'content');
        \App::$domain->mail->mail->sendDraft($mailEntity);
        \Yii::$app->response->setStatusCode(201);
    }


}