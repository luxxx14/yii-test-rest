<?php

namespace domain\mail\v1\services;

use App;
use domain\mail\v1\behaviors\DraftFilter;
use domain\mail\v1\behaviors\MailByDataFilter;
use domain\mail\v1\entities\AttachmentEntity;
use domain\mail\v1\entities\MailEntity;
use domain\mail\v1\entities\SettingsEntity;
use domain\mail\v1\entities\TotalEntity;
use domain\mail\v1\enums\MailKindEnum;
use domain\mail\v1\enums\MailTypeEnum;
use domain\mail\v1\forms\UploadFileCollectionForm;
use domain\mail\v1\helpers\MailHelper;
use domain\mail\v1\helpers\MailContentHelper;
use domain\mail\v1\interfaces\services\MailInterface;
use domain\mail\v1\strategies\mail\ReceiveStrategy;
use domain\mail\v1\strategies\mail\SendStrategy;
use Yii;
use yii\web\NotFoundHttpException;
use yii\db\Expression;
use yii\web\ServerErrorHttpException;
use yii2lab\notify\domain\entities\EmailEntity;
use yii2rails\domain\data\Query;
use yii\web\UnprocessableEntityHttpException;
use yii2rails\domain\helpers\ErrorCollection;
use yii2rails\domain\services\base\BaseActiveService;
use yii2rails\extension\common\enums\StatusEnum;
use yii2rails\extension\yii\helpers\ArrayHelper;
use yii2rails\domain\helpers\Helper;
use yubundle\user\domain\v1\entities\PersonEntity;


/**
 * Class MailService
 *
 * @package domain\mail\v1\services
 *
 * @property-read \domain\mail\v1\Domain $domain
 * @property-read \domain\mail\v1\interfaces\repositories\MailInterface $repository
 */
class MailService extends BaseActiveService implements MailInterface
{

    public function behaviors()
    {
        return [
            DraftFilter::class,
            MailByDataFilter::class,
        ];
    }

    public function create($data)
    {
        // Отправитель
        $addressEntity = \App::$domain->mail->address->myAddress();
        $data['from'] = isset($data['from']) && !empty($data['from']) ? $data['from'] : $addressEntity->email;

        $data['ext_id'] = $data['ext_id'] ?? uniqid(hash('crc32b', $data['from']), true) . '.' . time();
        $query = new Query();
        $query->andWhere(['ext_id' => $data['ext_id']]);
        try {
            $mailEntity = $this->repository->one($query);
            $blindCopy = array_diff(explode(',', $data['blind_copy']), $mailEntity->blind_copy);
            //TODO: обдумать принятие скрытых копий от gmail
            if (!empty($blindCopy) &&  MailHelper::getServiceNameByEmail($data['from']) == 'gmail') {
                $data['to'] = [];
                $data['copy_to'] = [];
                $data['ext_id'] = uniqid(hash('crc32b', $data['from']), true) . '.' . time();
                $data['blind_copy'] = implode(",", $blindCopy);
            } else {
                return null;
            }
        } catch (NotFoundHttpException $e) {
        }

        $isInternal = true;
        $data['is_draft'] = true;

        // Получатели
        if (!empty($data['to']) && $data['to'] != '') {
            $data['to'] = MailHelper::validateEmailList(explode(',', $data['to']));
        } else {
            $data['to'] = [];
        }

        $emails = $data['to'];

        if (!empty($data['copy_to'])) {
            $data['copy_to'] = MailHelper::validateEmailList(explode(',', $data['copy_to']));
            $emails = array_merge($emails, $data['copy_to']);
        } else {
            $data['copy_to'] = [];
        }

        if (!empty($data['blind_copy'])) {
            $data['blind_copy'] = MailHelper::validateEmailList(explode(',', $data['blind_copy']));
            $emails = array_merge($emails, $data['blind_copy']);
        } else {
            $data['blind_copy'] = [];
        }

        $emails = array_merge($emails, [$data['from']]);
        if (!empty($emails)) {
            $isInternal = \App::$domain->mail->address->isExternalList($emails);
        }

        $data['kind'] = empty($isInternal) ? MailKindEnum::INNER : MailKindEnum::OUTER;

        // Проверка дискуссии или диалога
        $data = MailHelper::tieMailWithDiscussion($data);
        $data = MailHelper::tieMailWithDialog($data);

        $mailEntity = new MailEntity($data);

        $mailEntity = parent::create($mailEntity->toArray());

        $model = new UploadFileCollectionForm();
        $model->mail_id = $mailEntity->id;

        // todo: вынести это в контроллер, ибо когда вызываем из консоли, все крашится
        $data = \Yii::$app->request->post();
        $fileEncoding = ArrayHelper::getValue($data, 'file_content_encoding');
        Helper::forgeForm($model);
        if ($model->validate()) {
            \App::$domain->mail->attachment->uploadAll($model, $fileEncoding);
        }

        if (!\App::$domain->mail->address->isInternal($mailEntity->from)) {
            $receiveStrategy = ReceiveStrategy::getInstance();
            $serviceName = MailHelper::getServiceNameByEmail($mailEntity->from);
            $receiveStrategy->setStrategy($serviceName);
            $mailEntity = $receiveStrategy->receive($mailEntity);
            parent::update($mailEntity);
        }

        $mailEntity = $this->send($mailEntity);

        return $mailEntity;
    }

    public function createDraft($data)
    {

        $addressEntity = \App::$domain->mail->address->myAddress();
        $data['from'] = $addressEntity->email;

        if (!empty($data['to'])) {
            $data['to'] = MailHelper::validateEmailList(explode(',', $data['to']));
        } else {
            $data['to'] = [];
        }
        if (!empty($data['copy_to'])) {
            $data['copy_to'] = MailHelper::validateEmailList(explode(',', $data['copy_to']));
        }
        if (!empty($data['blind_copy'])) {
            $data['blind_copy'] = MailHelper::validateEmailList(explode(',', $data['blind_copy']));
        }

        $mailEntity = new MailEntity($data);
        $mailEntity = parent::create($mailEntity->toArray());
        return $mailEntity;
    }

    public function updateDraftById($id, $data)
    {
        $mailEntity = \App::$domain->mail->mail->oneById($id);
        if (!$mailEntity->is_draft) {
            throw new ServerErrorHttpException(Yii::t('mail/mail', 'email_messages_can_not_be_modify'));
        }
        if (!empty($data['to'])) {
            $data['to'] = MailHelper::validateEmailList(explode(',', $data['to']));
        }
        if (!empty($data['copy_to']) && $data['copy_to'] != '') {
            $data['copy_to'] = MailHelper::validateEmailList(explode(',', MailHelper::removeSpaces($data['copy_to'])));
        }
        if (!empty($data['blind_copy']) && $data['blind_copy'] != '') {
            $data['blind_copy'] = MailHelper::validateEmailList(explode(',', MailHelper::removeSpaces($data['blind_copy'])));
        }
        parent::updateById($id, $data);
    }

    public function send(MailEntity $mailEntity)
    {
        $this->beforeSend($mailEntity);
        $mailEntity->is_draft = false;
        parent::update($mailEntity);
        $this->afterSend($mailEntity);
        return $mailEntity;
    }

    public function sendDraft(MailEntity $mailEntity)
    {


        if (!$mailEntity->is_draft) {
            throw new ServerErrorHttpException(Yii::t('mail/mail', 'email_messages_can_not_be_modify'));
        }

        $mailEntity = $this->beforeSend($mailEntity);

        $mailEntity->is_draft = false;
        parent::update($mailEntity);

        $this->afterSend($mailEntity);

        return $mailEntity;
    }

    private function beforeSend(MailEntity $mailEntity)
    {

        $addressEntity = \App::$domain->mail->address->myAddress();
        MailHelper::checkRequiredField($mailEntity);
        MailHelper::checkWrongEmail($mailEntity);

        //MailHelper::checkSendMessageToSelf($mailEntity);

        $mailEntity->validate();
        $from = $mailEntity->from;
        $toList = $mailEntity->to;

        $mailEntity->content = MailContentHelper::loadInnerImagesAsAttachments($mailEntity->id, $mailEntity->content);

        $emails = ArrayHelper::merge([$from], $toList);
        if (!empty($mailEntity->copy_to)) {
            $emails = ArrayHelper::merge($emails, $mailEntity->copy_to);
        }
        if (!empty($mailEntity->blind_copy)) {
            $emails = ArrayHelper::merge($emails, $mailEntity->blind_copy);
        }

        $isInternal = \App::$domain->mail->address->isInternalList($emails);
        $mailEntity->kind = $isInternal ? MailKindEnum::INNER : MailKindEnum::OUTER;

        // Формируем подпись
        if ($from == $addressEntity->getEmail() && $mailEntity->type != MailTypeEnum::MESSAGE) {
            /** @var SettingsEntity $settings */
            $settings = App::$domain->mail->settings->oneSelf();
            $mailEntity->content .= $settings->isEnabledSign();
        }

        return $mailEntity;
    }

    private function afterSend(MailEntity $mailEntity)
    {

        $mailEntity = \App::$domain->mail->report->sendServerReport($mailEntity);

        $this->domain->flow->createFlowByMailEntity($mailEntity);

        if ($mailEntity->type == MailTypeEnum::MESSAGE) {
            $this->domain->dialog->updateDialog($mailEntity->from, $mailEntity->to[0]);
        }
        if (!empty($mailEntity->discussion_id)) {
            $this->domain->discussion->updateMessageCount($mailEntity->discussion_id);
            $mailEntity->reply_to = ArrayHelper::merge([$mailEntity->from], $mailEntity->to);
        }

        /**
         * Баг №2119
         */
        $addressEntity = \App::$domain->mail->address->myAddress();
        if ($mailEntity->kind == MailKindEnum::OUTER &&
            (in_array($mailEntity->from, [$addressEntity->getEmail(), 'mailsender@' . $addressEntity->domain]) || $mailEntity->subject == \Yii::t('mail/mail', 'auto_answer_mail'))) {
            $sendStrategy = SendStrategy::getInstance();
            $sendStrategy->send($mailEntity);
        }
    }

    public function updateById($id, $data)
    {
        $data = MailHelper::validateFillable($data);
        parent::updateById($id, $data);
    }

    public function update($mailEntity)
    {
        /** @var MailEntity $mailEntity */
        if ($mailEntity->is_draft) {
            parent::update($mailEntity);
        } else {
            throw new ServerErrorHttpException(Yii::t('mail/mail', 'email_messages_can_not_be_modify'));
        }

    }

    /**
     * @param MailEntity $mailEntity
     *
     * @deprecated
     */
    private function sendOuterLetter(MailEntity $mailEntity): void
    {
        $mailEntity = MailContentHelper::createBase64FromInnerImages($mailEntity);
        $emailEntity = new EmailEntity;
        $emailEntity->from = $mailEntity->from;
        $emailEntity->address = $mailEntity->to;
        if (isset($mailEntity->copy_to) && !empty($mailEntity->copy_to)) {
            $emailEntity->copyToAdress = $mailEntity->copy_to;
        }
        if (isset($emailEntity->blindCopyToAddress) && !empty($emailEntity->blindCopyToAddress)) {
            $emailEntity->blindCopyToAddress = $mailEntity->blind_copy;
        }
        $emailEntity->subject = $mailEntity->subject;
        $emailEntity->content = $mailEntity->content;
        $emailEntity->attachments = MailContentHelper::getAttachments($mailEntity->attachments);

        App::$domain->notify->email->directSendEntity($emailEntity);
    }

    public function deleteById($id)
    {
        /** @var MailEntity $mailEntity */
        $mailEntity = $this->oneById($id);
        if ($mailEntity->is_draft) {
            return parent::deleteById($id);
        } else {
            $error = new ErrorCollection;
            $error->add('name', 'mail/mail', 'email_messages_can_not_be_modify');
            throw new UnprocessableEntityHttpException($error);
        }
    }

    public function deleteDraftById($id)
    {
        $mailEntity = \App::$domain->mail->mail->oneById($id);
        if (!$mailEntity->is_draft) {
            throw new ServerErrorHttpException(Yii::t('mail/mail', 'email_messages_can_not_be_modify'));
        }
        \App::$domain->mail->attachment->deleteAllByMailId($id);
        $this->deleteById($id);
    }

    public function checkFreeSpaceInBox()
    {
        $personEntity = new PersonEntity();
        $mailsList = array();
        $attachSizes = array();

        $personId = \App::$domain->account->auth->identity->person_id;
        $personEntity->load(['id' => $personId]);
        $myBox = \App::$domain->mail->box->oneByPerson($personEntity);

        $query = Query::forge();
        $query->select('mail_id');
        $query->where(['mail_address' => $myBox->email]);
        $query->andWhere(['has_attachment' => true]);
        $mailsFlowList = \App::$domain->mail->flow->repository->all($query);
        if (!empty($mailsFlowList)) {
            foreach ($mailsFlowList as $key => $value) {
                $mailsAttachList = array();
                $mailsListData = array();
                $query = Query::forge();
                $query->where(['mail_id' => $value->mail_id]);
                $mailsAttachList = \App::$domain->mail->attachment->repository->all($query);

                $query = Query::forge();
                $query->where(['id' => $value->mail_id]);
                $mailsListData = \App::$domain->mail->mail->repository->all($query);
                $mailsListData[0]->__set('attachments', $mailsAttachList);
                $mailsList[] = $mailsListData[0];

                $mailsAttachData = $mailsListData[0]->__get('attachments');
                $attachSizes[] = $mailsAttachData[0]->size;
            }
            $totalAttachSpiceKB = round(floatval((array_sum($attachSizes) / 1024)), 2);
        } else {
            $totalAttachSpiceKB = 0;
        }

        //$totalAttachSpiceMB = round(floatval(($totalAttachSpiceKB / 1024)), 2);

        /***/
        $gruzKB = 8081;
        /***/

        $mailsSizes = \App::$domain->mail->mail->repository->getDataSize($myBox->email);
        $flowsSizes = \App::$domain->mail->flow->repository->getDataSize($myBox->email);

        $allMailsSizeKB = round(floatval($mailsSizes['KB'] + $flowsSizes['KB']), 2);
        //$allMailsSizeMB = round(floatval(($allMailsSizeKB / 1024)), 2);

        $myBoxSizeKB = round(floatval(($myBox->quote_size / 1024)), 2);
        //$myBoxSizeMB = round(floatval(($myBoxSizeKB / 1024)), 2);

        $myFreeSpaceKB = round(floatval($myBoxSizeKB - ($totalAttachSpiceKB + $allMailsSizeKB/* + $gruzKB*/)), 2);
        //$myFreeSpaceMB = round(floatval(($myFreeSpaceKB / 1024)), 2);

        $result = $myFreeSpaceKB;
        if ($myFreeSpaceKB < 1024 && $myFreeSpaceKB > 0) {
            $mailEntity = new MailEntity;
            $mailEntity->kind = MailKindEnum::OUTER;
            $mailEntity->from = Yii::t('mail/mail', 'notifity_from_address');
            $mailEntity->to = strval($myBox->email);
            $mailEntity->copy_to = "";
            $mailEntity->blind_copy = "";
            $mailEntity->subject = Yii::t('mail/mail', 'send_low_free_space_notifity_subject');
            $mailEntity->content = Yii::t('mail/mail', 'send_low_free_space_notifity_content');
            $mailEntity->content = strip_tags($mailEntity->content, '<p><a><strong><b><span><div><style><br><hr><img>');
            \App::$domain->mail->mail->create($mailEntity->toArray());
        } else if ($myFreeSpaceKB < 0) {
            $result = 0;
            //throw new Exception(\Yii::t('storage/storage' ,'free_space_is_over'), 201);
        }

        return $result;
    }

    public function sendRedirectMessages(MailEntity $mailEntity, $redirectEmailCollection)
    {
        foreach ($redirectEmailCollection as $redirectEmail) {
            $isInternal = \App::$domain->mail->address->isInternal($redirectEmail);
            if (!$isInternal) {
                $this->sendRedirectMessage($mailEntity, $redirectEmail);
            } else {
                \App::$domain->mail->flow->createForwardFlow($mailEntity, $redirectEmail);
            }
        }
    }

    public function sendRedirectMessage(MailEntity $mailEntity, $redirectEmail)
    {
        $replyToAddress = array_merge($mailEntity->to, $mailEntity->copy_to);
        $emailEntity = new EmailEntity;
        $emailEntity->content = $mailEntity->content;
        //$emailEntity->forwardAddress = $redirectEmail;
        $emailEntity->from = $mailEntity->from;
        $emailEntity->address = $redirectEmail;
        /*
        if (isset($mailEntity->copy_to) && !empty($mailEntity->copy_to)) {
            $emailEntity->copyToAdress = $mailEntity->copy_to;
        }
        if (isset($emailEntity->blindCopyToAddress) && !empty($emailEntity->blindCopyToAddress)) {
            $emailEntity->blindCopyToAddress = $mailEntity->blind_copy;
        }
        */
        $emailEntity->forwardAddress = $redirectEmail;
        $emailEntity->subject = $mailEntity->subject;
        $emailEntity->content = $mailEntity->content;
        $emailEntity->replyToAddress = $replyToAddress;
        App::$domain->notify->email->directSendEntity($emailEntity);
    }

    public function newMessageCountByTypes(string $types) {
        $types = explode(',', $types);
        $totalEntityCollection = [];
        foreach ($types as $type) {
            $totalEntityCollection[] = $this->newMessageCount($type);
        }
        return $totalEntityCollection;
    }

    private function newMessageCount($type) {
        $addressEntity = \App::$domain->mail->address->myAddress();
        $totalEntity = null;
        //TODO: тут должен быть какой-то сырой запрос raw или что-нибудь подобное, а не выборка через репозиторий
        if ($type == "dialog") {
            $dialogQuery = new Query();
            $dialogQuery->select(['SUM (new_message_count) AS new_message_count']);
            $dialogQuery->andWhere([
                'actor' => $addressEntity->getEmail(),
                'status' => StatusEnum::ENABLE,
            ]);
            $dialogEntity = \App::$domain->mail->dialog->repository->all($dialogQuery);
            $totalEntity = new TotalEntity(['type' => MailTypeEnum::MESSAGE, 'total' => $dialogEntity[0]->new_message_count]);
        } else if ($type == "discussion") {
            $discussionQuery = new Query();
            $discussionQuery->select(['SUM (new_message_count) AS new_message_count']);
            $discussionQuery->andWhere([
                'email' => $addressEntity->getEmail(),
                'status' => StatusEnum::ENABLE,
            ]);
            $discussionEntity = \App::$domain->mail->discussionMember->repository->all($discussionQuery);
            $totalEntity = new TotalEntity(['type' => MailTypeEnum::DISCUSSION, 'total' => $discussionEntity[0]->new_message_count]);
        }
        return $totalEntity;
    }

}
