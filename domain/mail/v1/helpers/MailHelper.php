<?php

namespace domain\mail\v1\helpers;

use App;
use domain\mail\v1\entities\DialogEntity;
use domain\mail\v1\entities\DiscussionEntity;
use domain\mail\v1\entities\MailEntity;
use domain\mail\v1\enums\MailTypeEnum;
use Yii;
use yii\db\Expression;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use yii2rails\domain\data\Query;
use yii2rails\domain\exceptions\UnprocessableEntityHttpException;
use yii2rails\domain\helpers\ErrorCollection;
use yii2rails\extension\common\enums\StatusEnum;
use yii2rails\extension\yii\helpers\ArrayHelper;

class MailHelper
{

    /**
     * @param array $emailList
     * @return array
     */
    public static function validateEmailList(array $emailList): array
    {
        foreach ($emailList as $key => $email) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                unset($emailList[$key]);
            }
        }
        return $emailList;
    }

    /**
     * @param $data
     * @return mixed
     * @throws \yii\web\NotFoundHttpException
     */
    public static function tieMailWithDiscussion($data)
    {
        $postData = \Yii::$app->request->post();
        if (!empty($data['discussion_id'])) {
            $addressEntity = App::$domain->mail->address->myAddress();
            $query = new Query();
            $query->andWhere(['status' => StatusEnum::ENABLE]);
            $query->with('members');
            /** @var DiscussionEntity $discussion */
            $discussion = App::$domain->mail->discussion->oneById($data['discussion_id'], $query);
            $discussionMembers = ArrayHelper::getColumn($discussion->members, 'email');
            ArrayHelper::removeByValue($addressEntity->email, $discussionMembers);
            $discussionMembers = array_values($discussionMembers);
            $data['discussion_id'] = $discussion->id;
            $data['to'] = $discussionMembers;
            $data['subject'] = $discussion->subject;
        } elseif (isset($data['from']) && isset($data['to']) && empty($postData['dialog_id'])) {
            $allMails = is_array($data['to']) ? $data['to'] : [$data['to']];
            if (isset($data['copy_to'])) {
                $allMails = ArrayHelper::merge($allMails, $data['copy_to']);
            }
            $allMails[] = $data['from'];
            $data['discussion_id'] = App::$domain->mail->discussion->getBySubjectAndEmails($data['subject'], $allMails);
        }
        return $data;
    }

    /**
     * @param $data
     * @return mixed
     * @throws UnprocessableEntityHttpException
     */
    public static function tieMailWithDialog($data)
    {
        $postData = \Yii::$app->request->post();
        if (!empty($postData['dialog_id'])) {
            try {
                /** @var DialogEntity $dialogEntity */
                $dialogEntity = App::$domain->mail->dialog->oneById($postData['dialog_id']);
                $data['subject'] = null;
                $data['to'] = ArrayHelper::toArray($dialogEntity->contractor);
                $data['type'] = MailTypeEnum::MESSAGE;
                $data['dialog_id'] = $dialogEntity->id;
            } catch (NotFoundHttpException $e) {
                $error = new ErrorCollection();
                $error->add('dialog_id', 'mail/mail', 'not_found');
                throw new UnprocessableEntityHttpException($error);
            }
            self::isOppositeDialogExist($data);
        }
        return $data;
    }

    /**
     * @param MailEntity $mailEntity
     * @throws UnprocessableEntityHttpException
     * @throws \yii\base\UnknownPropertyException
     */
    public static function checkWrongEmail(MailEntity $mailEntity)
    {

        $dataBeforeCheck = [
            'to' => ArrayHelper::toArray($mailEntity->getAttribute('to')),
            'copyTo' => ArrayHelper::toArray($mailEntity->getAttribute('copy_to'))
        ];

        $mailEntity->to = MailHelper::validateEmailList(ArrayHelper::toArray($mailEntity->getAttribute('to')));
        $mailEntity->copy_to = MailHelper::validateEmailList(ArrayHelper::toArray($mailEntity->getAttribute('copy_to')));

        $wrongEmailsCount = count($dataBeforeCheck['to']) - count($mailEntity->to);
        /*
        if (isset($mailEntity->copy_to[0])) {
            if ($mailEntity->copy_to[0] == '') {
                $wrongEmailsCount += count($dataBeforeCheck['copyTo']) - count($mailEntity->copy_to);
            } else {
                $mailEntity->copy_to = null;
            }
        }
        */
        if ($wrongEmailsCount > 0) {
            $error = new ErrorCollection();
            $error->add('to', 'mail/mail', 'wrong_emails');
            throw new UnprocessableEntityHttpException($error);
        }
    }

    public static function checkRequiredField(MailEntity $mailEntity)
    {
        if (empty($mailEntity->getAttribute('from'))) {
            throw new ServerErrorHttpException();
        }
        if (empty($mailEntity->getAttribute('to')) && empty($mailEntity->getAttribute('copy_to')) && empty($mailEntity->getAttribute('blind_copy'))) {
            $error = new ErrorCollection();
            $error->add('to', 'mail/mail', 'empty_fields {fields}', ['fields' => ' Кому, Копия или Скрытая копия'] );
            throw new UnprocessableEntityHttpException($error);
        }
        $query = new Query();
        $query->andWhere([
            'mail_id' => $mailEntity->getAttribute('id')
        ]);
        /*
        $attachmentCount = App::$domain->mail->attachment->count($query);
        if (empty($mailEntity->getAttribute('content')) and $attachmentCount == 0) {
            $error = new ErrorCollection();
            $error->add('content', 'mail/mail', 'empty_letter');
            throw new UnprocessableEntityHttpException($error);
        }
        */
    }

    public static function checkSendMessageToSelf(MailEntity $mailEntity)
    {
        foreach ($mailEntity->to as $email) {
            $copyToAddressEntity = \App::$domain->mail->address->oneByEmail($email);
            if ($copyToAddressEntity->email == $mailEntity->from) {
                $error = new ErrorCollection();
                $error->add('to', 'mail/mail', 'send_message_to_self_error');
                throw new UnprocessableEntityHttpException($error);
            }
        }

        if (isset($mailEntity->copy_to) && !empty($mailEntity->copy_to)) {
            foreach ($mailEntity->copy_to as $email) {
                $copyToAddressEntity = \App::$domain->mail->address->oneByEmail($email);
                if ($copyToAddressEntity->email == $mailEntity->from) {
                    $error = new ErrorCollection();
                    $error->add('copy_to', 'mail/mail', 'send_message_to_self_error');
                    throw new UnprocessableEntityHttpException($error);
                }
            }
        }
    }

    public static function validateFillable(array $data): array
    {
        $fillable = [
            'type'
        ];
        foreach ($data as $property => $value) {
            if (!in_array($property, $fillable)) {
                unset($data[$property]);
            }
        }
        return $data;
    }

    public static function getServiceNameByEmail(string $email) {
        $email = explode('@', $email);
        $email = explode('.', $email[1]);
        return $email[0];
    }

    public static function isOppositeDialogExist($data) {
        try {
            $query = new Query;
            $query->andWhere(['actor' => $data['to'], 'contractor' => $data['from']]);
            return \App::$domain->mail->dialog->repository->one($query);
        } catch (NotFoundHttpException $e) {
            $error = new ErrorCollection();
            $error->add('dialog_id', 'mail/mail', 'opposite_dialog_not_found');
            throw new UnprocessableEntityHttpException($error);
        }
    }

    public static function makeAutoAnswer($from, $to): bool
    {
        $mailQuery = new Query;
        $mailQuery->andWhere(['from' => $from]);
        $mailQuery->andWhere(['to' => json_encode([$to])]);
        $mailQuery->andWhere(['subject' => Yii::t('mail/mail', 'auto_answer_mail')]);
        $mailQuery->andWhere(new Expression("updated_at between now() - interval '20 minutes' and now()"));
        try {
            App::$domain->mail->mail->one($mailQuery);
            return false;
        } catch (NotFoundHttpException $e) {
            return true;
        }
    }

    public static function removeSpaces($string) {
        $string = str_replace(" ", "", $string);
        if ($string == '') {
            return null;
        }
        return $string;
    }

}