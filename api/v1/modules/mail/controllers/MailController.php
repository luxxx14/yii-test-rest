<?php

namespace api\v1\modules\mail\controllers;

use common\enums\rbac\PermissionEnum;
use domain\mail\v1\entities\MailEntity;
use domain\mail\v1\entities\AttachmentEntity;
use domain\mail\v1\enums\MailKindEnum;
use domain\mail\v1\helpers\MailHelper;
use yii\helpers\ArrayHelper;
use yii2rails\domain\exceptions\UnprocessableEntityHttpException;
use yii2rails\domain\helpers\ErrorCollection;
use yii2rails\extension\yii\helpers\FileHelper;
use yii2lab\rest\domain\rest\ActiveControllerWithQuery as Controller;
use yii2rails\extension\web\helpers\Behavior;

use yubundle\user\domain\v1\entities\PersonEntity;
use yii2rails\domain\data\Query;

class MailController extends Controller
{

    public $service = 'mail.flow';

    public function behaviors()
    {
        return [
            Behavior::cors(),
            Behavior::auth(),
            Behavior::access([PermissionEnum::SEND_CUSTOM_EMAIL_MESSAGE], ['receiver', 'receiverForm']),
        ];
    }

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['create']);
        return $actions;
    }

    /**
     * @deprecated use actionReceiverForm()
     */
    public function actionReceiver()
    {
        $data = \Yii::$app->request->post();
        $mailEntity = new MailEntity;
        $mailEntity->kind = MailKindEnum::OUTER;
        $mailEntity->from = ArrayHelper::getValue($data, 'from');
        $mailEntity->to = MailHelper::removeSpaces(ArrayHelper::getValue($data, 'to'));
        $mailEntity->copy_to = MailHelper::removeSpaces(ArrayHelper::getValue($data, 'copy_to', null));
        $mailEntity->blind_copy = MailHelper::removeSpaces(ArrayHelper::getValue($data, 'blind_copy', null));
        $mailEntity->subject = ArrayHelper::getValue($data, 'subject');
        $mailEntity->content = $this->decodeMailContent(ArrayHelper::getValue($data, 'content'), $data['encoding']);
        $mailEntity->content = strip_tags($mailEntity->content, '<p><a><strong><b><span><div><style><br><hr><img>');
        \App::$domain->mail->mail->create($mailEntity->toArray());
        \Yii::$app->response->setStatusCode(201);
    }

    public function actionReceiverForm()
    {
        $data = \Yii::$app->request->post();
        $mailEntity = new MailEntity;
        $mailEntity->ext_id = ArrayHelper::getValue($data, 'ext_id');
        $mailEntity->kind = MailKindEnum::OUTER;
        $mailEntity->from = ArrayHelper::getValue($data, 'from');
        $mailEntity->to = MailHelper::removeSpaces(ArrayHelper::getValue($data, 'to', []));
        $mailEntity->copy_to = MailHelper::removeSpaces(ArrayHelper::getValue($data, 'copy_to', []));
        $mailEntity->blind_copy = MailHelper::removeSpaces(ArrayHelper::getValue($data, 'blind_copy', []));
        $mailEntity->subject = ArrayHelper::getValue($data, 'subject');
        $mailEntity->content = ArrayHelper::getValue($data, 'content');
        $this->validateAddress($mailEntity);
        $mailEntity->content = strip_tags($mailEntity->content, '<p><a><strong><b><span><div><style><br><hr><img>');
        \App::$domain->mail->mail->create($mailEntity->toArray());
        \Yii::$app->response->setStatusCode(201);
    }

    public function actionCreate()
    {
        $data = \Yii::$app->request->post();
        $mailEntity = new MailEntity;
        $mailEntity->kind = MailKindEnum::INNER;
        $mailEntity->to = MailHelper::removeSpaces(ArrayHelper::getValue($data, 'to', []));
        $mailEntity->copy_to = MailHelper::removeSpaces(ArrayHelper::getValue($data, 'copy_to', []));
        $mailEntity->blind_copy = MailHelper::removeSpaces(ArrayHelper::getValue($data, 'blind_copy', []));
        $mailEntity->subject = ArrayHelper::getValue($data, 'subject', null);
        $mailEntity->content = ArrayHelper::getValue($data, 'content', null);
        $mailEntity->discussion_id = ArrayHelper::getValue($data, 'discussion_id', null);
        \App::$domain->mail->mail->create($mailEntity->toArray());
        \Yii::$app->response->setStatusCode(201);
    }

    public function actionMove($idCollection)
    {
        $folder = \Yii::$app->request->post('folder');
        $idCollection = explode(',', $idCollection);
        \App::$domain->mail->flow->move($idCollection, $folder);
    }

    public function actionTouch($idCollection) {
        $status = \Yii::$app->request->post('status');
        if (!isset($status) | $status == '') {
            $error = new ErrorCollection;
            $error->add('status', 'mail/mail', 'empty_status');
            throw new UnprocessableEntityHttpException($error);
        }
        $idCollection = explode(',', $idCollection);
        \App::$domain->mail->flow->touch($idCollection, $status);
    }

    public function actionNewMessage() {
        $type = \Yii::$app->request->get('type');
        $totalEntity = \App::$domain->mail->mail->newMessageCountByTypes($type);
        return $totalEntity;
    }

    private function decodeMailContent(string $mailContent, string $encoding = null): string
    {
        switch ($encoding) {
            case 'base64':
                return base64_decode($mailContent);

                break;
            case 'quoted-printable':
                return quoted_printable_decode($mailContent);

                break;
            default:
                return $mailContent;
        }
    }

    public function actionBoxSizeQuote()
    {
        return \App::$domain->mail->mail->checkFreeSpaceInBox();
    }

    private function validateAddress($mailEntity)
    {
        $from = $mailEntity->from;
        $to = $mailEntity->to;
        $copyTo = $mailEntity->copy_to;
        $blindCopy = $mailEntity->blind_copy;
        if ((!isset($from) || empty($from)) && (!isset($to) && empty($to))) {
            $error = new ErrorCollection;
            $error->add('to', 'mail/mail', 'empty_to');
            $error->add('from', 'mail/mail', 'empty_from');
            throw new UnprocessableEntityHttpException($error);
        } elseif (!isset($from) || empty($from)) {
            $error = new ErrorCollection;
            $error->add('from', 'mail/mail', 'empty_from');
            throw new UnprocessableEntityHttpException($error);
        } elseif ((!isset($to) || empty($to)) && (!isset($copyTo) || empty($copyTo)) && (!isset($blindCopy) || empty($blindCopy))) {
            $error = new ErrorCollection;
            $error->add('to', 'mail/mail', 'empty_to_and_copy_to_and_blind_copy');
            $error->add('copy_to', 'mail/mail', 'empty_to_and_copy_to_and_blind_copy');
            $error->add('blind_copy', 'mail/mail', 'empty_to_and_copy_to_and_blind_copy');
            throw new UnprocessableEntityHttpException($error);
        }
    }

}